<?php

namespace App\Http\Controllers;

use App\Models\CashFlow;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends Controller
{
    public function profitLoss(Request $request)
    {
        // Default to current month if no dates provided
        $startDate = $request->start_date ?? date('Y-m-01');
        $endDate = $request->end_date ?? date('Y-m-t');

        // 1. LABA BERSIH KESELURUHAN (ALL TIME - REAL TIME)
        $allTimeRevenue = Sale::where('is_settled', true)->sum('grand_total');
        
        $allTimeCogs = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.is_settled', true)
            ->sum('sale_items.total_cogs');

        $allTimeExpense = CashFlow::where('type', 'expense')
            ->where('category', '!=', 'Pembelian')
            ->where('fund_source', '!=', 'laba')
            ->sum('amount');
        
        $netProfit = $allTimeRevenue - $allTimeCogs - $allTimeExpense;

        // 2. KEKAYAAN (VALUASI ASET) & SALDO UANG REAL (ALL TIME)
        $physicalInventoryValue = Product::selectRaw('SUM(stock * purchase_price) as total')->value('total') ?? 0;
        
        // Tambahkan nilai barang yang sudah terjual tapi belum di-settle (Tutup Buku)
        $unsettledCogs = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.is_settled', false)
            ->sum('sale_items.total_cogs') ?? 0;

        $inventoryValue = $physicalInventoryValue + $unsettledCogs;
        
        $totalCashIn = CashFlow::where('type', 'income')->sum('amount');
        $totalCashOut = CashFlow::where('type', 'expense')->sum('amount');
        $realCashBalance = $totalCashIn - $totalCashOut;

        // Breakdown Porsi Uang di Tangan
        $labaDiambil = CashFlow::where('type', 'expense')->where('fund_source', 'laba')->sum('amount');
        $labaDitambah = CashFlow::where('type', 'income')->where('fund_source', 'laba')->sum('amount');
        
        $porsiLaba = $netProfit - $labaDiambil + $labaDitambah;
        
        if ($porsiLaba > $realCashBalance) {
            $porsiLaba = $realCashBalance;
        } elseif ($porsiLaba < 0) {
            $porsiLaba = 0;
        }
        $porsiModal = $realCashBalance - $porsiLaba;

        // 3. HUTANG & PIUTANG
        $totalPiutang = Sale::where('payment_status', '!=', 'paid')
            ->where('is_settled', true)
            ->selectRaw('SUM(grand_total - paid_amount) as total')->value('total') ?? 0;
            
        $totalHutang = Purchase::where('payment_status', '!=', 'paid')
            ->selectRaw('SUM(grand_total - paid_amount) as total')->value('total') ?? 0;

        // 4. GRAFIK LABA PER HARI
        // Get daily revenue
        $dailyRevenues = Sale::whereDate('sale_date', '>=', $startDate)
            ->whereDate('sale_date', '<=', $endDate)
            ->where('is_settled', true)
            ->selectRaw('DATE(sale_date) as date, SUM(grand_total) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        // Get daily COGS
        $dailyCogs = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereDate('sales.sale_date', '>=', $startDate)
            ->whereDate('sales.sale_date', '<=', $endDate)
            ->where('sales.is_settled', true)
            ->selectRaw('DATE(sales.sale_date) as date, SUM(sale_items.total_cogs) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        // Get daily Expenses
        $dailyExpenses = CashFlow::where('type', 'expense')
            ->where('category', '!=', 'Pembelian')
            ->whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate)
            ->selectRaw('DATE(transaction_date) as date, SUM(amount) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        // Build array of dates and calculate net profit per day
        $chartDates = [];
        $chartProfits = [];
        
        $current = strtotime($startDate);
        $end = strtotime($endDate);
        
        while ($current <= $end) {
            $dateStr = date('Y-m-d', $current);
            $chartDates[] = date('d M', $current);
            
            $rev = $dailyRevenues[$dateStr] ?? 0;
            $cogs = $dailyCogs[$dateStr] ?? 0;
            $exp = $dailyExpenses[$dateStr] ?? 0;
            
            $chartProfits[] = $rev - $cogs - $exp;
            
            $current = strtotime('+1 day', $current);
        }

        // 4. BARANG TERLARIS (Top Selling Items)
        $topProducts = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereDate('sales.sale_date', '>=', $startDate)
            ->whereDate('sales.sale_date', '<=', $endDate)
            ->where('sales.is_settled', true)
            ->selectRaw('products.name, SUM(sale_items.quantity) as total_qty')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_qty')
            ->limit(7)
            ->get();
            
        $topProductNames = $topProducts->pluck('name')->toArray();
        $topProductQtys = $topProducts->pluck('total_qty')->toArray();

        // 5. DAFTAR ARUS KAS (Sesuai Filter Tanggal)
        $cashInList = CashFlow::where('type', 'income')
            ->whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate)
            ->orderBy('transaction_date', 'desc')
            ->get();
            
        $cashOutList = CashFlow::where('type', 'expense')
            ->whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate)
            ->orderBy('transaction_date', 'desc')
            ->get();

        return view('reports.profit-loss', compact(
            'startDate',
            'endDate',
            'netProfit',
            'inventoryValue',
            'realCashBalance',
            'porsiLaba',
            'porsiModal',
            'totalPiutang',
            'totalHutang',
            'chartDates',
            'chartProfits',
            'topProductNames',
            'topProductQtys',
            'cashInList',
            'cashOutList'
        ));
    }
    public function exportExcel(Request $request)
    {
        $startDate = $request->start_date ?? date('Y-m-01');
        $endDate = $request->end_date ?? date('Y-m-t');

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        // 1. Produk
        $sheetProduk = $spreadsheet->createSheet();
        $sheetProduk->setTitle('Produk');
        $sheetProduk->setCellValue('A1', 'Kode');
        $sheetProduk->setCellValue('B1', 'Nama Produk');
        $sheetProduk->setCellValue('C1', 'Kategori');
        $sheetProduk->setCellValue('D1', 'Stok');
        $sheetProduk->setCellValue('E1', 'Harga Beli');
        $sheetProduk->setCellValue('F1', 'Harga Jual');
        
        $products = Product::whereDate('created_at', '<=', $endDate)->get();
        $row = 2;
        foreach($products as $p) {
            $sheetProduk->setCellValue('A'.$row, $p->code);
            $sheetProduk->setCellValue('B'.$row, $p->name);
            $sheetProduk->setCellValue('C'.$row, $p->category);
            $sheetProduk->setCellValue('D'.$row, $p->stock);
            $sheetProduk->setCellValue('E'.$row, $p->purchase_price);
            $sheetProduk->setCellValue('F'.$row, $p->selling_price);
            $row++;
        }

        // 2. Supplier
        $sheetSupplier = $spreadsheet->createSheet();
        $sheetSupplier->setTitle('Supplier');
        $sheetSupplier->setCellValue('A1', 'Nama');
        $sheetSupplier->setCellValue('B1', 'Telepon');
        $sheetSupplier->setCellValue('C1', 'Alamat');
        
        $suppliers = Supplier::whereDate('created_at', '<=', $endDate)->get();
        $row = 2;
        foreach($suppliers as $s) {
            $sheetSupplier->setCellValue('A'.$row, $s->name);
            $sheetSupplier->setCellValue('B'.$row, $s->phone);
            $sheetSupplier->setCellValue('C'.$row, $s->address);
            $row++;
        }

        // 3. Pembelian
        $sheetPembelian = $spreadsheet->createSheet();
        $sheetPembelian->setTitle('Pembelian');
        $sheetPembelian->setCellValue('A1', 'Tanggal');
        $sheetPembelian->setCellValue('B1', 'No. Invoice');
        $sheetPembelian->setCellValue('C1', 'Supplier');
        $sheetPembelian->setCellValue('D1', 'Total');
        $sheetPembelian->setCellValue('E1', 'Status');
        
        $purchases = Purchase::with('supplier')->whereDate('purchase_date', '>=', $startDate)
                        ->whereDate('purchase_date', '<=', $endDate)->get();
        $row = 2;
        foreach($purchases as $p) {
            $sheetPembelian->setCellValue('A'.$row, $p->purchase_date->format('Y-m-d'));
            $sheetPembelian->setCellValue('B'.$row, $p->invoice_number);
            $sheetPembelian->setCellValue('C'.$row, $p->supplier ? $p->supplier->name : '-');
            $sheetPembelian->setCellValue('D'.$row, $p->grand_total);
            $sheetPembelian->setCellValue('E'.$row, $p->payment_status);
            $row++;
        }

        // 4. Penjualan
        $sheetPenjualan = $spreadsheet->createSheet();
        $sheetPenjualan->setTitle('Penjualan');
        $sheetPenjualan->setCellValue('A1', 'Tanggal');
        $sheetPenjualan->setCellValue('B1', 'No. Invoice');
        $sheetPenjualan->setCellValue('C1', 'Pelanggan');
        $sheetPenjualan->setCellValue('D1', 'Total');
        $sheetPenjualan->setCellValue('E1', 'Status');
        
        $sales = Sale::whereDate('sale_date', '>=', $startDate)
                    ->whereDate('sale_date', '<=', $endDate)->get();
        $row = 2;
        foreach($sales as $s) {
            $sheetPenjualan->setCellValue('A'.$row, $s->sale_date->format('Y-m-d H:i'));
            $sheetPenjualan->setCellValue('B'.$row, $s->invoice_number);
            $sheetPenjualan->setCellValue('C'.$row, $s->customer_name);
            $sheetPenjualan->setCellValue('D'.$row, $s->grand_total);
            $sheetPenjualan->setCellValue('E'.$row, $s->payment_status);
            $row++;
        }

        // 5. Saldo Uang Real (Arus Kas)
        $sheetKas = $spreadsheet->createSheet();
        $sheetKas->setTitle('Saldo Uang Real');
        $sheetKas->setCellValue('A1', 'Tanggal');
        $sheetKas->setCellValue('B1', 'Tipe');
        $sheetKas->setCellValue('C1', 'Kategori');
        $sheetKas->setCellValue('D1', 'Nominal');
        $sheetKas->setCellValue('E1', 'Keterangan');
        
        $cashflows = CashFlow::whereDate('transaction_date', '>=', $startDate)
                            ->whereDate('transaction_date', '<=', $endDate)->orderBy('transaction_date')->get();
        $row = 2;
        foreach($cashflows as $c) {
            $sheetKas->setCellValue('A'.$row, $c->transaction_date->format('Y-m-d'));
            $sheetKas->setCellValue('B'.$row, $c->type == 'income' ? 'Pemasukan' : 'Pengeluaran');
            $sheetKas->setCellValue('C'.$row, $c->category);
            $sheetKas->setCellValue('D'.$row, $c->amount);
            $sheetKas->setCellValue('E'.$row, $c->description);
            $row++;
        }

        // 6. Modal Barang
        $sheetModal = $spreadsheet->createSheet();
        $sheetModal->setTitle('Modal Barang');
        $sheetModal->setCellValue('A1', 'Keterangan');
        $sheetModal->setCellValue('B1', 'Nilai');

        $physicalInventoryValue = Product::whereDate('created_at', '<=', $endDate)->selectRaw('SUM(stock * purchase_price) as total')->value('total') ?? 0;
        $unsettledCogs = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereDate('sales.sale_date', '<=', $endDate)
            ->where('sales.is_settled', false)
            ->sum(DB::raw('sale_items.quantity * products.purchase_price')) ?? 0;
            
        $sheetModal->setCellValue('A2', 'Nilai Fisik Barang (Stok Akhir)');
        $sheetModal->setCellValue('B2', $physicalInventoryValue);
        $sheetModal->setCellValue('A3', 'Nilai Barang Belum Tutup Buku');
        $sheetModal->setCellValue('B3', $unsettledCogs);
        $sheetModal->setCellValue('A4', 'TOTAL MODAL BARANG');
        $sheetModal->setCellValue('B4', $physicalInventoryValue + $unsettledCogs);

        // 7. Hutang Piutang
        $sheetHutang = $spreadsheet->createSheet();
        $sheetHutang->setTitle('Hutang Piutang');
        $sheetHutang->setCellValue('A1', 'Jenis');
        $sheetHutang->setCellValue('B1', 'No. Invoice');
        $sheetHutang->setCellValue('C1', 'Pihak');
        $sheetHutang->setCellValue('D1', 'Sisa Tagihan');
        
        $row = 2;
        $piutang = Sale::where('payment_status', '!=', 'paid')->where('is_settled', true)
                    ->whereDate('sale_date', '<=', $endDate)->get();
        foreach($piutang as $p) {
            $sheetHutang->setCellValue('A'.$row, 'Piutang (Pelanggan berhutang)');
            $sheetHutang->setCellValue('B'.$row, $p->invoice_number);
            $sheetHutang->setCellValue('C'.$row, $p->customer_name);
            $sheetHutang->setCellValue('D'.$row, $p->grand_total - $p->paid_amount);
            $row++;
        }
        $hutang = Purchase::where('payment_status', '!=', 'paid')
                    ->whereDate('purchase_date', '<=', $endDate)->get();
        foreach($hutang as $h) {
            $sheetHutang->setCellValue('A'.$row, 'Hutang (Toko berhutang)');
            $sheetHutang->setCellValue('B'.$row, $h->invoice_number);
            $sheetHutang->setCellValue('C'.$row, $h->supplier ? $h->supplier->name : '-');
            $sheetHutang->setCellValue('D'.$row, $h->grand_total - $h->paid_amount);
            $row++;
        }

        // 8. Laba Harian
        $sheetLaba = $spreadsheet->createSheet();
        $sheetLaba->setTitle('Laba Harian');
        $sheetLaba->setCellValue('A1', 'Tanggal');
        $sheetLaba->setCellValue('B1', 'Pendapatan');
        $sheetLaba->setCellValue('C1', 'HPP');
        $sheetLaba->setCellValue('D1', 'Pengeluaran Lain');
        $sheetLaba->setCellValue('E1', 'Laba Bersih');
        
        $dailyRevenues = Sale::whereDate('sale_date', '>=', $startDate)
            ->whereDate('sale_date', '<=', $endDate)
            ->where('is_settled', true)
            ->selectRaw('DATE(sale_date) as date, SUM(grand_total) as total')
            ->groupBy('date')->pluck('total', 'date');
        $dailyCogs = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereDate('sales.sale_date', '>=', $startDate)
            ->whereDate('sales.sale_date', '<=', $endDate)
            ->where('sales.is_settled', true)
            ->selectRaw('DATE(sales.sale_date) as date, SUM(sale_items.quantity * products.purchase_price) as total')
            ->groupBy('date')->pluck('total', 'date');
        $dailyExpenses = CashFlow::where('type', 'expense')
            ->where('category', '!=', 'Pembelian')
            ->whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate)
            ->selectRaw('DATE(transaction_date) as date, SUM(amount) as total')
            ->groupBy('date')->pluck('total', 'date');

        $row = 2;
        $current = strtotime($startDate);
        $end = strtotime($endDate);
        while ($current <= $end) {
            $dateStr = date('Y-m-d', $current);
            $rev = $dailyRevenues[$dateStr] ?? 0;
            $cogs = $dailyCogs[$dateStr] ?? 0;
            $exp = $dailyExpenses[$dateStr] ?? 0;
            $profit = $rev - $cogs - $exp;
            
            $sheetLaba->setCellValue('A'.$row, $dateStr);
            $sheetLaba->setCellValue('B'.$row, $rev);
            $sheetLaba->setCellValue('C'.$row, $cogs);
            $sheetLaba->setCellValue('D'.$row, $exp);
            $sheetLaba->setCellValue('E'.$row, $profit);
            
            $row++;
            $current = strtotime('+1 day', $current);
        }

        // 9. Barang Terlaris
        $sheetLaris = $spreadsheet->createSheet();
        $sheetLaris->setTitle('Barang Terlaris');
        $sheetLaris->setCellValue('A1', 'Nama Produk');
        $sheetLaris->setCellValue('B1', 'Total Terjual');
        
        $topProducts = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereDate('sales.sale_date', '>=', $startDate)
            ->whereDate('sales.sale_date', '<=', $endDate)
            ->where('sales.is_settled', true)
            ->selectRaw('products.name, SUM(sale_items.quantity) as total_qty')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_qty')
            ->get();
            
        $row = 2;
        foreach($topProducts as $tp) {
            $sheetLaris->setCellValue('A'.$row, $tp->name);
            $sheetLaris->setCellValue('B'.$row, $tp->total_qty);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Laporan_EZKIOS_' . $startDate . '_sd_' . $endDate . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. urlencode($fileName).'"');
        $writer->save('php://output');
        exit;
    }
}