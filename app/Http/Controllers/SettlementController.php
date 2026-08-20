<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\CashFlow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SettlementController extends Controller
{
    public function index()
    {
        $unsettledSales = Sale::where('is_settled', false)
            ->orderBy('sale_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $totalSales = $unsettledSales->count();
        $totalRevenue = $unsettledSales->sum('grand_total');
        $totalPaid = 0;

        // Group by payment method for cash register balance check
        $paymentMethods = [];
        foreach ($unsettledSales as $sale) {
            if ($sale->paid_amount > 0) {
                $method = $sale->payment_method ?? 'Tunai';
                if (!isset($paymentMethods[$method])) {
                    $paymentMethods[$method] = 0;
                }
                // Uang riil yang diterima (setelah kembalian) adalah paid_amount - change_amount
                $actualPaid = clone $sale; // wait, no.
                $actualPaidAmount = $sale->paid_amount - $sale->change_amount;
                if ($actualPaidAmount < 0) $actualPaidAmount = 0;
                $paymentMethods[$method] += $actualPaidAmount;
                $totalPaid += $actualPaidAmount;
            }
        }
        $totalUnpaid = $totalRevenue - $totalPaid;

        return view('settlements.index', compact(
            'unsettledSales', 
            'totalSales', 
            'totalRevenue', 
            'totalPaid', 
            'totalUnpaid',
            'paymentMethods'
        ));
    }

    public function store(Request $request)
    {
        $unsettledSales = Sale::where('is_settled', false)->get();

        if ($unsettledSales->isEmpty()) {
            return back()->with('error', 'Tidak ada penjualan yang perlu disetor.');
        }

        DB::beginTransaction();
        try {
            foreach ($unsettledSales as $sale) {
                if ($sale->paid_amount > 0) {
                    $actualPaid = $sale->paid_amount - $sale->change_amount;
                    if ($actualPaid > 0) {
                        CashFlow::create([
                            'type' => 'income',
                            'category' => 'Penjualan',
                            'amount' => $actualPaid,
                            'description' => 'Penjualan barang - ' . $sale->invoice_number,
                            'transaction_date' => date('Y-m-d'), // Tanggal setor
                            'reference_type' => Sale::class,
                            'reference_id' => $sale->id,
                            'payment_method' => $sale->payment_method,
                            'fund_source' => 'modal',
                            'status' => 'confirmed',
                            'created_by' => Auth::id(),
                        ]);
                    }
                }
                $sale->update(['is_settled' => true]);
            }

            DB::commit();

            return redirect()->route('sales.index')
                ->with('success', 'Tutup Buku Kasir Berhasil! Semua transaksi hari ini telah masuk ke Laporan Keuangan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat tutup buku: ' . $e->getMessage());
        }
    }
}
