<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class MigrateFifoInitialStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fifo:migrate-initial-stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membuat kloter awal di purchase_items untuk produk yang sudah memiliki stok namun belum ada riwayat pembelian';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai migrasi stok awal untuk FIFO...');
        DB::beginTransaction();
        try {
            // Kita butuh satu supplier dummy untuk 'Sistem' jika belum ada
            $systemSupplier = Supplier::firstOrCreate(
                ['phone' => '0000000000'],
                [
                    'name' => 'Sistem (Migrasi Awal)',
                    'address' => 'Sistem Internal',
                ]
            );

            // Buat satu purchase dummy untuk migrasi
            $migrationPurchase = Purchase::firstOrCreate(
                ['invoice_number' => 'MIG-FIFO-001'],
                [
                    'supplier_id' => $systemSupplier->id,
                    'purchase_date' => now()->subYears(10), // Buat tanggal di masa lalu agar jadi kloter pertama
                    'total_amount' => 0,
                    'grand_total' => 0,
                    'payment_status' => 'paid',
                    'notes' => 'Migrasi otomatis untuk sisa stok sebelum sistem FIFO diterapkan',
                ]
            );

            $products = Product::where('stock', '>', 0)->get();
            $count = 0;

            foreach ($products as $product) {
                // Cari berapa stok yang sudah ada di purchase_items
                $recordedQuantity = PurchaseItem::where('product_id', $product->id)->sum('remaining_quantity');
                
                $missingQuantity = $product->stock - $recordedQuantity;

                if ($missingQuantity > 0) {
                    // Buat purchase_items untuk stok yang kurang
                    PurchaseItem::create([
                        'purchase_id' => $migrationPurchase->id,
                        'product_id' => $product->id,
                        'quantity' => $missingQuantity,
                        'remaining_quantity' => $missingQuantity, // Penting untuk FIFO
                        'purchase_price' => $product->purchase_price, // Gunakan harga beli di master
                        'subtotal' => $missingQuantity * $product->purchase_price,
                        'notes' => 'Stok awal hasil migrasi',
                    ]);
                    $count++;
                }
            }
            
            DB::commit();
            $this->info("Berhasil membuat kloter awal untuk {$count} produk.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Gagal migrasi stok awal: ' . $e->getMessage());
        }
    }
}
