<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\CashFlow;
use App\Models\StockAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StockAdjustmentController extends Controller
{
    public function index()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $adjustments = StockAdjustment::with(['product', 'createdBy'])->latest()->limit(50)->get();
        return view('stock-adjustments.index', compact('products', 'adjustments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'actual_stocks' => 'array',
            'actual_stocks.*' => 'nullable|integer|min:0',
            'reasons' => 'array',
            'reasons.*' => 'nullable|string|max:255',
        ]);

        $actualStocks = $request->input('actual_stocks', []);
        $reasons = $request->input('reasons', []);

        $changesMade = 0;

        DB::beginTransaction();
        try {
            foreach ($actualStocks as $productId => $newStock) {
                // Ignore if empty
                if ($newStock === null || $newStock === '') {
                    continue;
                }

                $product = Product::find($productId);
                if (!$product) continue;

                $oldStock = $product->stock;
                $difference = (int)$newStock - $oldStock;

                if ($difference != 0) {
                    $reason = $reasons[$productId] ?? null;

                    if (empty($reason)) {
                        DB::rollBack();
                        return back()->with('error', 'Keterangan wajib diisi untuk barang ' . $product->name . ' karena ada selisih stok!');
                    }

                    // 1. Catat riwayat koreksi stok
                    StockAdjustment::create([
                        'product_id' => $product->id,
                        'old_stock' => $oldStock,
                        'new_stock' => $newStock,
                        'difference' => $difference,
                        'reason' => $reason,
                        'created_by' => Auth::id(),
                    ]);

                    // --- LOGIKA FIFO UNTUK KOREKSI STOK ---
                    if ($difference < 0) {
                        // Kurangi stok (Potong dari batch paling lama)
                        $qtyToDeduct = abs($difference);
                        $batches = \App\Models\PurchaseItem::where('product_id', $product->id)
                            ->where('remaining_quantity', '>', 0)
                            ->orderBy('id', 'asc')
                            ->get();

                        foreach ($batches as $batch) {
                            if ($qtyToDeduct <= 0) break;
                            if ($batch->remaining_quantity >= $qtyToDeduct) {
                                $batch->remaining_quantity -= $qtyToDeduct;
                                $batch->save();
                                $qtyToDeduct = 0;
                            } else {
                                $qtyToDeduct -= $batch->remaining_quantity;
                                $batch->remaining_quantity = 0;
                                $batch->save();
                            }
                        }
                    } else if ($difference > 0) {
                        // Tambah stok (Buat batch sistem agar FIFO tetap jalan)
                        $systemSupplier = \App\Models\Supplier::firstOrCreate(
                            ['phone' => '0000000000'],
                            ['name' => 'Sistem (Migrasi Awal)', 'address' => 'Sistem Internal']
                        );
                        $migrationPurchase = \App\Models\Purchase::firstOrCreate(
                            ['invoice_number' => 'MIG-FIFO-001'],
                            [
                                'supplier_id' => $systemSupplier->id,
                                'purchase_date' => now(),
                                'total_amount' => 0,
                                'grand_total' => 0,
                                'payment_status' => 'paid',
                                'notes' => 'Otomatis untuk penyesuaian stok',
                            ]
                        );
                        \App\Models\PurchaseItem::create([
                            'purchase_id' => $migrationPurchase->id,
                            'product_id' => $product->id,
                            'quantity' => $difference,
                            'remaining_quantity' => $difference,
                            'purchase_price' => $product->purchase_price,
                            'subtotal' => $difference * $product->purchase_price,
                            'notes' => 'Hasil koreksi stok (plus) - ' . $reason,
                        ]);
                    }
                    // ----------------------------------------

                    // 2. Update stok produk
                    $product->update(['stock' => $newStock]);
                    $changesMade++;
                }
            }

            DB::commit();

            if ($changesMade > 0) {
                return redirect()->route('stock-adjustments.index')
                    ->with('success', "Koreksi stok berhasil disimpan untuk $changesMade barang!");
            } else {
                return redirect()->route('stock-adjustments.index')
                    ->with('info', 'Tidak ada barang yang berubah stoknya.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
