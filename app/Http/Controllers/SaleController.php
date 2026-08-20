<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\CashFlow;
use App\Models\PaymentMethod;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SaleController extends Controller
{
    /**
     * Display a listing of the sales.
     */
    public function index()
    {
        $sales = Sale::with(['createdBy', 'items.product'])->latest()->paginate(10);
        
        $totalSales = Sale::sum('grand_total');
        $totalTransactions = Sale::count();
        $salesToday = Sale::whereDate('sale_date', date('Y-m-d'))->sum('grand_total');
        $totalUnpaid = Sale::where('payment_status', '!=', 'paid')
            ->selectRaw('SUM(grand_total - paid_amount) as total')->value('total') ?? 0;

        return view('sales.index', compact('sales', 'totalSales', 'totalTransactions', 'salesToday', 'totalUnpaid'));
    }

    /**
     * Show the form for creating a new sale.
     */
    public function create()
    {
        $products = Product::where('is_active', true)->where('stock', '>', 0)->get();
        $categories = ProductCategory::pluck('name')->toArray();
        
        $paymentMethods = PaymentMethod::all()->pluck('name')->toArray();
        if(empty($paymentMethods)) {
            $paymentMethods = ['Tunai', 'Transfer Bank', 'QRIS'];
        }

        // Generate invoice number
        $lastSale = Sale::orderBy('id', 'desc')->first();
        $sequence = 1;
        if ($lastSale && $lastSale->created_at->format('Ymd') === date('Ymd')) {
            $lastSeq = (int) substr($lastSale->invoice_number, -4);
            $sequence = $lastSeq + 1;
        }
        $invoiceNumber = 'SLS-' . date('Ymd') . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

        // Fetch active promotions
        $activePromotions = \App\Models\Promotion::with('rewardProduct')->where('is_active', true)->where(function($q) {
            $now = now();
            $q->where(function($q2) use ($now) {
                $q2->whereNull('start_date')->orWhere('start_date', '<=', $now);
            })->where(function($q3) use ($now) {
                $q3->whereNull('end_date')->orWhere('end_date', '>=', $now);
            });
        })->where(function($q) {
            $q->whereNull('usage_limit')->orWhereRaw('used_count < usage_limit');
        })->get();

        return view('sales.create', compact('products', 'categories', 'paymentMethods', 'invoiceNumber', 'activePromotions'));
    }

    /**
     * Store a newly created sale in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'sale_date' => 'required|date',
            'invoice_number' => 'required|string|unique:sales,invoice_number|max:50',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'payment_status' => 'required|in:pending,partial,paid',
            'payment_method' => 'nullable|string|max:255',
            'payment_method_new' => 'nullable|string|max:255|required_if:payment_method,Lainnya_Baru',
            'due_date' => 'nullable|date',
            'paid_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.selling_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric',
        ]);

        DB::beginTransaction();

        try {
            // Calculate totals
            $totalAmount = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $subtotal = ($item['quantity'] * $item['selling_price']) - ($item['discount'] ?? 0);
                $totalAmount += $subtotal;

                // Check stock
                $product = Product::find($item['product_id']);
                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Stok produk {$product->name} tidak mencukupi! Stok tersedia: {$product->stock}");
                }

                $itemsData[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'selling_price' => $item['selling_price'],
                    'discount' => $item['discount'] ?? 0,
                    'subtotal' => $subtotal,
                    'notes' => $item['notes'] ?? null,
                ];
            }

            // Calculate grand total
            $grandTotal = $totalAmount - ($request->discount ?? 0) + ($request->tax ?? 0) + ($request->shipping_cost ?? 0);

            // Calculate paid amount
            $paidAmount = $request->paid_amount ?? 0;
            $changeAmount = $paidAmount - $grandTotal;

            // Handle new payment method
            $finalPaymentMethod = $request->payment_method;
            if ($finalPaymentMethod === 'Lainnya_Baru' && $request->filled('payment_method_new')) {
                $finalPaymentMethod = $request->payment_method_new;
                PaymentMethod::firstOrCreate(['name' => $finalPaymentMethod]);
            }

            // Create sale
            $sale = Sale::create([
                'sale_date' => $request->sale_date,
                'invoice_number' => $request->invoice_number,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'total_amount' => $totalAmount,
                'discount' => $request->discount ?? 0,
                'tax' => $request->tax ?? 0,
                'shipping_cost' => $request->shipping_cost ?? 0,
                'grand_total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount > 0 ? $changeAmount : 0,
                'payment_status' => $request->payment_status,
                'payment_method' => $finalPaymentMethod,
                'due_date' => $request->due_date,
                'notes' => $request->notes,
                'created_by' => Auth::id(),
                'applied_promo_code' => $request->applied_promo_code,
            ]);

            // Increment usage_count and attach applied promotions
            if ($request->has('applied_promotions') && is_array($request->applied_promotions)) {
                $uniquePromoIds = array_unique($request->applied_promotions);
                $sale->promotions()->attach($uniquePromoIds);
                foreach ($uniquePromoIds as $promoId) {
                    \App\Models\Promotion::where('id', $promoId)->increment('used_count');
                }
            }

            // Create sale items
            foreach ($itemsData as $item) {
                
                // --- LOGIKA FIFO UNTUK MENGHITUNG COGS (HPP) ---
                $qtyToDeduct = $item['quantity'];
                $totalCogs = 0;

                // Ambil batch pembelian yang masih ada sisa stok, diurutkan dari paling lama
                $batches = \App\Models\PurchaseItem::where('product_id', $item['product_id'])
                    ->where('remaining_quantity', '>', 0)
                    ->orderBy('id', 'asc')
                    ->get();

                foreach ($batches as $batch) {
                    if ($qtyToDeduct <= 0) break;

                    if ($batch->remaining_quantity >= $qtyToDeduct) {
                        // Batch cukup
                        $totalCogs += ($qtyToDeduct * $batch->purchase_price);
                        $batch->remaining_quantity -= $qtyToDeduct;
                        $batch->save();
                        $qtyToDeduct = 0;
                    } else {
                        // Batch tidak cukup, ambil semuanya lalu lanjut ke batch berikutnya
                        $totalCogs += ($batch->remaining_quantity * $batch->purchase_price);
                        $qtyToDeduct -= $batch->remaining_quantity;
                        $batch->remaining_quantity = 0;
                        $batch->save();
                    }
                }

                // Jika stok fisik lebih banyak dari stok tercatat di batch (selisih),
                // fallback gunakan harga beli master
                if ($qtyToDeduct > 0) {
                    $product = Product::find($item['product_id']);
                    $totalCogs += ($qtyToDeduct * $product->purchase_price);
                }
                // ------------------------------------------------

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'selling_price' => $item['selling_price'],
                    'discount' => $item['discount'],
                    'subtotal' => $item['subtotal'],
                    'total_cogs' => $totalCogs,
                    'notes' => $item['notes'],
                ]);

                // Reduce product stock
                $product = Product::find($item['product_id']);
                $product->reduceStock($item['quantity']);
            }

            // Catatan: Cash flow tidak lagi dibuat otomatis di sini.
            // Uang akan disetor ke Laporan (CashFlow) saat kasir melakukan "Tutup Buku" (Settlement).

            if ($request->filled('applied_promo_code')) {
                $promo = \App\Models\Promotion::where('promo_code', $request->applied_promo_code)->first();
                if ($promo) {
                    $promo->increment('used_count');
                }
            }

            DB::commit();

            return redirect()->route('sales.create')
                ->with('success', 'Transaksi berhasil! Siap untuk penjualan berikutnya.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified sale.
     */
    public function show(Sale $sale)
    {
        $sale->load(['items.product', 'createdBy', 'payments']);
        
        $paymentMethods = PaymentMethod::all()->pluck('name')->toArray();
        if(empty($paymentMethods)) {
            $paymentMethods = ['Tunai', 'Transfer Bank', 'QRIS'];
        }

        return view('sales.show', compact('sale', 'paymentMethods'));
    }

    /**
     * Show the form for editing the specified sale.
     */
    public function edit(Sale $sale)
    {
        if ($sale->payment_status === 'paid') {
            return redirect()->route('sales.index')
                ->with('error', 'Penjualan yang sudah lunas tidak bisa diedit!');
        }

        $products = Product::where('is_active', true)->get();
        $categories = ProductCategory::pluck('name')->toArray();
        $paymentMethods = PaymentMethod::all()->pluck('name')->toArray();
        if(empty($paymentMethods)) {
            $paymentMethods = ['Tunai', 'Transfer Bank', 'QRIS'];
        }

        $sale->load('items');
        return view('sales.edit', compact('sale', 'products', 'categories', 'paymentMethods'));
    }

    /**
     * Update the specified sale in storage.
     */
    public function update(Request $request, Sale $sale)
    {
        if ($sale->payment_status === 'paid') {
            return redirect()->route('sales.index')
                ->with('error', 'Penjualan yang sudah lunas tidak bisa diedit!');
        }

        $request->validate([
            'sale_date' => 'required|date',
            'invoice_number' => 'required|string|unique:sales,invoice_number,' . $sale->id . '|max:50',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'payment_status' => 'required|in:pending,partial,paid',
            'payment_method' => 'nullable|string|max:255',
            'payment_method_new' => 'nullable|string|max:255|required_if:payment_method,Lainnya_Baru',
            'due_date' => 'nullable|date',
            'paid_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.selling_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Restore old stock
            foreach ($sale->items as $oldItem) {
                $product = Product::find($oldItem->product_id);
                $product->addStock($oldItem->quantity);
            }

            // Delete old items
            $sale->items()->delete();

            // Calculate totals
            $totalAmount = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $subtotal = ($item['quantity'] * $item['selling_price']) - ($item['discount'] ?? 0);
                $totalAmount += $subtotal;

                // Check stock
                $product = Product::find($item['product_id']);
                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Stok produk {$product->name} tidak mencukupi! Stok tersedia: {$product->stock}");
                }

                $itemsData[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'selling_price' => $item['selling_price'],
                    'discount' => $item['discount'] ?? 0,
                    'subtotal' => $subtotal,
                    'notes' => $item['notes'] ?? null,
                ];
            }

            $grandTotal = $totalAmount - ($request->discount ?? 0) + ($request->tax ?? 0) + ($request->shipping_cost ?? 0);
            $paidAmount = $request->paid_amount ?? 0;
            $changeAmount = $paidAmount - $grandTotal;

            // Handle new payment method
            $finalPaymentMethod = $request->payment_method;
            if ($finalPaymentMethod === 'Lainnya_Baru' && $request->filled('payment_method_new')) {
                $finalPaymentMethod = $request->payment_method_new;
                PaymentMethod::firstOrCreate(['name' => $finalPaymentMethod]);
            }

            // Update sale
            $sale->update([
                'sale_date' => $request->sale_date,
                'invoice_number' => $request->invoice_number,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'total_amount' => $totalAmount,
                'discount' => $request->discount ?? 0,
                'tax' => $request->tax ?? 0,
                'shipping_cost' => $request->shipping_cost ?? 0,
                'grand_total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount > 0 ? $changeAmount : 0,
                'payment_status' => $request->payment_status,
                'payment_method' => $finalPaymentMethod,
                'due_date' => $request->due_date,
                'notes' => $request->notes,
                'updated_by' => Auth::id(),
            ]);

            // Create new items
            foreach ($itemsData as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'selling_price' => $item['selling_price'],
                    'discount' => $item['discount'],
                    'subtotal' => $item['subtotal'],
                    'notes' => $item['notes'],
                ]);

                // Reduce stock
                $product = Product::find($item['product_id']);
                $product->reduceStock($item['quantity']);
            }

            // Update cash flow
            $cashFlow = CashFlow::where('reference_type', Sale::class)
                ->where('reference_id', $sale->id)
                ->first();

            if ($request->payment_status === 'paid' || $request->payment_status === 'partial') {
                if ($cashFlow) {
                    $cashFlow->update([
                        'amount' => $paidAmount > 0 ? $paidAmount : $grandTotal,
                        'transaction_date' => $request->sale_date,
                        'payment_method' => $finalPaymentMethod,
                    ]);
                } else {
                    CashFlow::create([
                        'type' => 'income',
                        'category' => 'Penjualan',
                        'amount' => $paidAmount > 0 ? $paidAmount : $grandTotal,
                        'description' => 'Penjualan barang - ' . $request->invoice_number,
                        'transaction_date' => $request->sale_date,
                        'reference_type' => Sale::class,
                        'reference_id' => $sale->id,
                        'payment_method' => $finalPaymentMethod,
                        'status' => 'confirmed',
                        'created_by' => Auth::id(),
                    ]);
                }
            } else {
                if ($cashFlow) {
                    $cashFlow->delete();
                }
            }

            DB::commit();

            return redirect()->route('sales.index')
                ->with('success', 'Penjualan berhasil diupdate!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Add payment (pelunasan) for a sale.
     */
    public function addPayment(Request $request, Sale $sale)
    {
        if ($sale->payment_status === 'paid') {
            return back()->with('error', 'Penjualan ini sudah lunas!');
        }

        $request->validate([
            'payment_amount' => 'required|string',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'receipt_image' => 'nullable|image|max:5120',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $paymentAmount = (float) str_replace('.', '', $request->payment_amount);
            $remainingBalance = $sale->grand_total - $sale->paid_amount;

            if ($paymentAmount <= 0) {
                return back()->with('error', 'Jumlah pembayaran harus lebih dari 0.');
            }

            if ($paymentAmount > $remainingBalance) {
                return back()->with('error', 'Jumlah pembayaran melebihi sisa tagihan.');
            }

            $receiptPath = null;
            if ($request->hasFile('receipt_image')) {
                $receiptPath = $request->file('receipt_image')->store('sale_receipts', 'public');
            }

            \App\Models\SalePayment::create([
                'sale_id' => $sale->id,
                'amount' => $paymentAmount,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'receipt_image' => $receiptPath,
                'notes' => $request->notes,
                'created_by' => Auth::id(),
            ]);

            $newPaidAmount = $sale->paid_amount + $paymentAmount;
            $newStatus = $newPaidAmount >= $sale->grand_total ? 'paid' : 'partial';

            $sale->update([
                'paid_amount' => $newPaidAmount,
                'payment_status' => $newStatus,
                'due_date' => $request->due_date ?? $sale->due_date,
            ]);

            CashFlow::create([
                'type' => 'income',
                'category' => 'Pelunasan Tagihan',
                'amount' => $paymentAmount,
                'description' => 'Pelunasan tagihan penjualan - ' . $sale->invoice_number,
                'transaction_date' => $request->payment_date,
                'reference_type' => Sale::class,
                'reference_id' => $sale->id,
                'payment_method' => $request->payment_method,
                'status' => 'confirmed',
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return back()->with('success', 'Pembayaran berhasil ditambahkan!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified sale from storage.
     */
    public function destroy(Sale $sale)
    {
        if ($sale->payment_status === 'paid') {
            return redirect()->route('sales.index')
                ->with('error', 'Penjualan yang sudah lunas tidak bisa dihapus!');
        }

        DB::beginTransaction();

        try {
            // Restore stock
            foreach ($sale->items as $item) {
                $product = Product::find($item->product_id);
                $product->addStock($item->quantity);
            }

            // Delete cash flow
            CashFlow::where('reference_type', Sale::class)
                ->where('reference_id', $sale->id)
                ->delete();

            // Delete items and sale
            $sale->items()->delete();
            $sale->delete();

            DB::commit();

            return redirect()->route('sales.index')
                ->with('success', 'Penjualan berhasil dihapus!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Print sale invoice.
     */
    public function print(Sale $sale)
    {
        $sale->load(['items.product', 'createdBy']);
        return view('sales.print', compact('sale'));
    }

    /**
     * Get product price by ID (for AJAX).
     */
    public function getProductPrice(Request $request)
    {
        $product = Product::find($request->product_id);
        if ($product) {
            return response()->json([
                'success' => true,
                'selling_price' => $product->selling_price,
                'stock' => $product->stock,
            ]);
        }
        return response()->json(['success' => false]);
    }
}
