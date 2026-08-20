<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ==========================================
        // TABEL 1: purchases (Pembelian / Barang Masuk)
        // ==========================================
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->onDelete('restrict');
            $table->date('purchase_date');
            $table->string('invoice_number')->unique()->comment('No. Faktur pembelian');
            $table->decimal('total_amount', 15, 2)->default(0)->comment('Total harga beli');
            $table->decimal('discount', 15, 2)->default(0)->comment('Diskon pembelian');
            $table->decimal('tax', 15, 2)->default(0)->comment('Pajak');
            $table->decimal('shipping_cost', 15, 2)->default(0)->comment('Ongkos kirim');
            $table->decimal('grand_total', 15, 2)->default(0)->comment('Total akhir setelah diskon, pajak, dll');
            $table->enum('payment_status', ['pending', 'partial', 'paid'])->default('pending')->comment('Status pembayaran');
            $table->enum('payment_method', ['cash', 'transfer', 'bank'])->nullable()->comment('Metode pembayaran');
            $table->date('due_date')->nullable()->comment('Tanggal jatuh tempo');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Index untuk mempercepat query
            $table->index('purchase_date');
            $table->index('payment_status');
        });

        // ==========================================
        // TABEL 2: purchase_items (Detail Barang Masuk)
        // ==========================================
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('restrict');
            $table->integer('quantity');
            $table->decimal('purchase_price', 15, 2)->comment('Harga beli per unit (saat transaksi)');
            $table->decimal('discount', 15, 2)->default(0)->comment('Diskon per item');
            $table->decimal('subtotal', 15, 2)->comment('Subtotal (quantity * purchase_price) - discount');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Index untuk mempercepat query
            $table->index('purchase_id');
            $table->index('product_id');

            // Unique constraint agar tidak duplikat product dalam 1 purchase
            $table->unique(['purchase_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
    }
};
