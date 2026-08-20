<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductUnit;
use App\Models\CashFlow;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the purchases.
     */
    public function index()
    {
        $purchases = Purchase::with(['supplier', 'createdBy'])->latest()->paginate(10);
        $paymentMethods = PaymentMethod::all()->pluck('name')->toArray();
        if(empty($paymentMethods)) {
            $paymentMethods = ['Tunai', 'Transfer Bank', 'QRIS'];
        }

        $totalPurchases = Purchase::count();
        $unpaidPurchases = Purchase::whereIn('payment_status', ['pending', 'partial'])->count();
        $totalSpend = Purchase::sum('grand_total');
        
        $unpaidQuery = Purchase::whereIn('payment_status', ['pending', 'partial']);
        $totalDebt = $unpaidQuery->sum('grand_total') - $unpaidQuery->sum('paid_amount');

        return view('purchases.index', compact(
            'purchases', 'paymentMethods', 'totalPurchases', 'unpaidPurchases', 'totalSpend', 'totalDebt'
        ));
    }

    /**
     * Show the form for creating a new purchase.
     */
    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();
        
        $paymentMethods = PaymentMethod::all()->pluck('name')->toArray();
        if(empty($paymentMethods)) {
            $paymentMethods = ['Tunai', 'Transfer Bank', 'QRIS'];
        }

        $categories = ProductCategory::pluck('name')->toArray();

        // Generate invoice number
        $lastPurchase = Purchase::orderBy('id', 'desc')->first();
        $sequence = 1;
        if ($lastPurchase && $lastPurchase->created_at->format('Ymd') === date('Ymd')) {
            $lastSeq = (int) substr($lastPurchase->invoice_number, -4);
            $sequence = $lastSeq + 1;
        }
        $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

        return view('purchases.create', compact('suppliers', 'products', 'paymentMethods', 'invoiceNumber', 'categories'));
    }

    /**
     * Store a newly created purchase in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'invoice_number' => 'required|string|unique:purchases,invoice_number|max:50',
            'tax' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'payment_status' => 'required|in:pending,partial,paid',
            'paid_amount' => 'nullable|string',
            'payment_method' => 'nullable|string|max:100',
            'payment_method_new' => 'nullable|string|max:100',
            'receipt_image' => 'nullable|image|max:5120', // max 5MB
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.purchase_price' => 'required',
        ]);

        // Validasi Anti-Rugi (Harga Beli >= Harga Jual)
        foreach ($request->items as $item) {
            $purchasePrice = (float) str_replace('.', '', $item['purchase_price']);
            $product = \App\Models\Product::find($item['product_id']);
            if ($product && $purchasePrice >= $product->selling_price) {
                return back()->withInput()->withErrors(['error' => "Harga beli untuk produk '{$product->name}' (Rp " . number_format($purchasePrice, 0, ',', '.') . ") lebih besar atau sama dengan harga jual saat ini (Rp " . number_format($product->selling_price, 0, ',', '.') . "). Silakan edit dan naikkan Harga Jual pada menu Master Produk terlebih dahulu!"]);
            }
        }

        DB::beginTransaction();

        try {
            // Process payment method
            $paymentMethod = $request->payment_method;
            if ($paymentMethod === 'Lainnya_Baru' && $request->filled('payment_method_new')) {
                $paymentMethod = $request->payment_method_new;
                PaymentMethod::firstOrCreate(['name' => $paymentMethod]);
            }

            // Process receipt image
            $receiptPath = null;
            if ($request->hasFile('receipt_image')) {
                $receiptPath = $request->file('receipt_image')->store('receipts', 'public');
            }

            // Calculate totals
            $totalAmount = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                // Parse formatted string
                $purchasePrice = (float) str_replace('.', '', $item['purchase_price']);
                $subtotal = ($item['quantity'] * $purchasePrice);
                $totalAmount += $subtotal;

                $itemsData[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'remaining_quantity' => $item['quantity'],
                    'purchase_price' => $purchasePrice,
                    'discount' => 0,
                    'subtotal' => $subtotal,
                    'notes' => $item['notes'] ?? null,
                ];
            }

            // Calculate grand total
            $tax = $request->filled('tax') ? (float) str_replace('.', '', $request->tax) : 0;
            $shipping = $request->filled('shipping_cost') ? (float) str_replace('.', '', $request->shipping_cost) : 0;
            $grandTotal = $totalAmount + $tax + $shipping;

            $paidAmount = 0;
            if ($request->payment_status === 'paid') {
                $paidAmount = $grandTotal;
            } elseif ($request->payment_status === 'partial') {
                $paidAmount = $request->filled('paid_amount') ? (float) str_replace('.', '', $request->paid_amount) : 0;
            }

            // Create purchase
            $purchase = Purchase::create([
                'supplier_id' => $request->supplier_id,
                'purchase_date' => $request->purchase_date,
                'invoice_number' => $request->invoice_number,
                'total_amount' => $totalAmount,
                'discount' => 0,
                'tax' => $tax,
                'shipping_cost' => $shipping,
                'grand_total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'payment_status' => $request->payment_status,
                'payment_method' => $paymentMethod,
                'receipt_image' => $receiptPath,
                'due_date' => $request->due_date,
                'notes' => $request->notes,
                'created_by' => Auth::id(),
            ]);

            // Create purchase items
            foreach ($itemsData as $item) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'remaining_quantity' => $item['quantity'],
                    'purchase_price' => $item['purchase_price'],
                    'discount' => $item['discount'],
                    'subtotal' => $item['subtotal'],
                    'notes' => $item['notes'],
                ]);

                // Update product stock & latest purchase price
                $product = Product::find($item['product_id']);
                $product->addStock($item['quantity']);
                $product->update(['purchase_price' => $item['purchase_price']]);
            }

            // Create cash flow if payment is paid or partial
            if ($request->payment_status === 'paid' || ($request->payment_status === 'partial' && $paidAmount > 0)) {
                CashFlow::create([
                    'type' => 'expense',
                    'category' => 'Pembelian',
                    'amount' => $paidAmount,
                    'description' => 'Pembelian barang - ' . $request->invoice_number,
                    'transaction_date' => $request->purchase_date,
                    'reference_type' => Purchase::class,
                    'reference_id' => $purchase->id,
                    'payment_method' => $request->payment_method,
                    'status' => 'confirmed',
                    'created_by' => Auth::id(),
                ]);
            }

            DB::commit();

            return redirect()->route('purchases.index')
                ->with('success', 'Pembelian berhasil ditambahkan!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified purchase.
     */
    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'items.product', 'createdBy', 'payments']);
        
        $paymentMethods = PaymentMethod::all()->pluck('name')->toArray();
        if(empty($paymentMethods)) {
            $paymentMethods = ['Tunai', 'Transfer Bank', 'QRIS'];
        }

        return view('purchases.show', compact('purchase', 'paymentMethods'));
    }

    /**
     * Show the form for editing the specified purchase.
     */
    public function edit(Purchase $purchase)
    {
        if ($purchase->payment_status === 'paid') {
            return redirect()->route('purchases.index')
                ->with('error', 'Pembelian yang sudah lunas tidak bisa diedit!');
        }

        $suppliers = Supplier::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();
        
        $paymentMethods = PaymentMethod::all()->pluck('name')->toArray();
        if(empty($paymentMethods)) {
            $paymentMethods = ['Tunai', 'Transfer Bank', 'QRIS'];
        }

        $categories = ProductCategory::pluck('name')->toArray();

        $purchase->load('items');
        return view('purchases.edit', compact('purchase', 'suppliers', 'products', 'paymentMethods', 'categories'));
    }

    /**
     * Update the specified purchase in storage.
     */
    public function update(Request $request, Purchase $purchase)
    {
        if ($purchase->payment_status === 'paid') {
            return redirect()->route('purchases.index')
                ->with('error', 'Pembelian yang sudah lunas tidak bisa diedit!');
        }

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'invoice_number' => 'required|string|unique:purchases,invoice_number,' . $purchase->id . '|max:50',
            'tax' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'payment_status' => 'required|in:pending,partial,paid',
            'paid_amount' => 'nullable|string',
            'payment_method' => 'nullable|string|max:100',
            'payment_method_new' => 'nullable|string|max:100',
            'receipt_image' => 'nullable|image|max:5120',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.purchase_price' => 'required',
        ]);

        DB::beginTransaction();

        try {
            // Process payment method
            $paymentMethod = $request->payment_method;
            if ($paymentMethod === 'Lainnya_Baru' && $request->filled('payment_method_new')) {
                $paymentMethod = $request->payment_method_new;
                PaymentMethod::firstOrCreate(['name' => $paymentMethod]);
            }

            // Process receipt image
            $receiptPath = $purchase->receipt_image;
            if ($request->hasFile('receipt_image')) {
                if ($receiptPath) {
                    Storage::disk('public')->delete($receiptPath);
                }
                $receiptPath = $request->file('receipt_image')->store('receipts', 'public');
            }

            // Restore old stock
            foreach ($purchase->items as $oldItem) {
                $product = Product::find($oldItem->product_id);
                $product->reduceStock($oldItem->quantity);
            }

            // Delete old items
            $purchase->items()->delete();

            // Calculate totals
            $totalAmount = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $purchasePrice = (float) str_replace('.', '', $item['purchase_price']);
                $subtotal = ($item['quantity'] * $purchasePrice);
                $totalAmount += $subtotal;

                $itemsData[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'purchase_price' => $purchasePrice,
                    'discount' => 0,
                    'subtotal' => $subtotal,
                    'notes' => $item['notes'] ?? null,
                ];
            }

            $tax = $request->filled('tax') ? (float) str_replace('.', '', $request->tax) : 0;
            $shipping = $request->filled('shipping_cost') ? (float) str_replace('.', '', $request->shipping_cost) : 0;
            $grandTotal = $totalAmount + $tax + $shipping;

            $paidAmount = 0;
            if ($request->payment_status === 'paid') {
                $paidAmount = $grandTotal;
            } elseif ($request->payment_status === 'partial') {
                $paidAmount = $request->filled('paid_amount') ? (float) str_replace('.', '', $request->paid_amount) : 0;
            }

            // Update purchase
            $purchase->update([
                'supplier_id' => $request->supplier_id,
                'purchase_date' => $request->purchase_date,
                'invoice_number' => $request->invoice_number,
                'total_amount' => $totalAmount,
                'discount' => 0,
                'tax' => $tax,
                'shipping_cost' => $shipping,
                'grand_total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'payment_status' => $request->payment_status,
                'payment_method' => $paymentMethod,
                'receipt_image' => $receiptPath,
                'due_date' => $request->due_date,
                'notes' => $request->notes,
                'updated_by' => Auth::id(),
            ]);

            // Create new items
            foreach ($itemsData as $item) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'remaining_quantity' => $item['quantity'],
                    'purchase_price' => $item['purchase_price'],
                    'discount' => $item['discount'],
                    'subtotal' => $item['subtotal'],
                    'notes' => $item['notes'],
                ]);

                // Add new stock
                $product = Product::find($item['product_id']);
                $product->addStock($item['quantity']);
            }

            // Update cash flow
            $cashFlow = CashFlow::where('reference_type', Purchase::class)
                ->where('reference_id', $purchase->id)
                ->first();

            if ($request->payment_status === 'paid' || ($request->payment_status === 'partial' && $paidAmount > 0)) {
                if ($cashFlow) {
                    $cashFlow->update([
                        'amount' => $paidAmount,
                        'transaction_date' => $request->purchase_date,
                        'payment_method' => $request->payment_method,
                    ]);
                } else {
                    CashFlow::create([
                        'type' => 'expense',
                        'category' => 'Pembelian',
                        'amount' => $paidAmount,
                        'description' => 'Pembelian barang - ' . $request->invoice_number,
                        'transaction_date' => $request->purchase_date,
                        'reference_type' => Purchase::class,
                        'reference_id' => $purchase->id,
                        'payment_method' => $request->payment_method,
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

            return redirect()->route('purchases.index')
                ->with('success', 'Pembelian berhasil diupdate!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function addPayment(Request $request, Purchase $purchase)
    {
        if ($purchase->payment_status === 'paid') {
            return back()->with('error', 'Pembelian ini sudah lunas!');
        }

        $request->validate([
            'payment_amount' => 'required|string',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'receipt_image' => 'required|image|max:5120',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $paymentAmount = (float) str_replace('.', '', $request->payment_amount);
            $remainingBalance = $purchase->grand_total - $purchase->paid_amount;

            if ($paymentAmount <= 0) {
                return back()->with('error', 'Jumlah pembayaran harus lebih dari 0.');
            }

            if ($paymentAmount > $remainingBalance) {
                return back()->with('error', 'Jumlah pembayaran melebihi sisa tagihan.');
            }

            $receiptPath = null;
            if ($request->hasFile('receipt_image')) {
                $receiptPath = $request->file('receipt_image')->store('receipts', 'public');
            }

            \App\Models\PurchasePayment::create([
                'purchase_id' => $purchase->id,
                'amount' => $paymentAmount,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'receipt_image' => $receiptPath,
                'notes' => $request->notes,
                'created_by' => Auth::id(),
            ]);

            $newPaidAmount = $purchase->paid_amount + $paymentAmount;
            $newStatus = $newPaidAmount >= $purchase->grand_total ? 'paid' : 'partial';

            $purchase->update([
                'paid_amount' => $newPaidAmount,
                'payment_status' => $newStatus,
                'due_date' => $request->due_date ?? $purchase->due_date,
            ]);

            CashFlow::create([
                'type' => 'expense',
                'category' => 'Pembayaran Tagihan',
                'amount' => $paymentAmount,
                'description' => 'Pembayaran tagihan pembelian - ' . $purchase->invoice_number,
                'transaction_date' => $request->payment_date,
                'reference_type' => Purchase::class,
                'reference_id' => $purchase->id,
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
     * Remove the specified purchase from storage.
     */
    public function destroy(Purchase $purchase)
    {
        if ($purchase->payment_status === 'paid') {
            return redirect()->route('purchases.index')
                ->with('error', 'Pembelian yang sudah lunas tidak bisa dihapus!');
        }

        DB::beginTransaction();

        try {
            // Restore stock
            foreach ($purchase->items as $item) {
                $product = Product::find($item->product_id);
                $product->reduceStock($item->quantity);
            }

            // Delete cash flow
            CashFlow::where('reference_type', Purchase::class)
                ->where('reference_id', $purchase->id)
                ->delete();

            // Delete items and purchase
            $purchase->items()->delete();
            $purchase->delete();

            DB::commit();

            return redirect()->route('purchases.index')
                ->with('success', 'Pembelian berhasil dihapus!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Print purchase invoice.
     */
    public function print(Purchase $purchase)
    {
        $purchase->load(['supplier', 'items.product', 'createdBy']);
        return view('purchases.print', compact('purchase'));
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
                'purchase_price' => $product->purchase_price,
                'selling_price' => $product->selling_price,
                'stock' => $product->stock,
            ]);
        }
        return response()->json(['success' => false]);
    }

    public function storeSupplierAjax(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $supplier = Supplier::create([
            'name' => $request->name,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'supplier' => $supplier
        ]);
    }

    public function storeProductAjax(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:products,code',
            'category' => 'required|string|max:100',
            'selling_price' => 'required|string',
        ]);

        $code = $request->code;
        if (empty($code)) {
            $code = 'PRD-' . strtoupper(Str::random(6));
            // Ensure unique
            while(Product::where('code', $code)->exists()) {
                $code = 'PRD-' . strtoupper(Str::random(6));
            }
        }

        ProductCategory::firstOrCreate(['name' => $request->category]);

        $sellingPrice = (float) str_replace('.', '', $request->selling_price);

        $product = Product::create([
            'name' => $request->name,
            'code' => $code,
            'category' => $request->category,
            'selling_price' => $sellingPrice,
            'purchase_price' => 0,
            'stock' => 0,
            'min_stock' => 5,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'product' => $product
        ]);
    }
}
