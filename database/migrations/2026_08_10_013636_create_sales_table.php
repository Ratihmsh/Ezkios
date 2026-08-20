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
        // TABEL 1: sales (Penjualan / Barang Keluar)
        // ==========================================
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->date('sale_date');
            $table->string('invoice_number')->unique()->comment('No. Faktur penjualan');
            $table->string('customer_name')->nullable()->comment('Nama pembeli');
            $table->string('customer_phone')->nullable()->comment('No HP pembeli');
            $table->decimal('total_amount', 15, 2)->default(0)->comment('Total harga jual');
            $table->decimal('discount', 15, 2)->default(0)->comment('Diskon penjualan');
            $table->decimal('tax', 15, 2)->default(0)->comment('Pajak');
            $table->decimal('shipping_cost', 15, 2)->default(0)->comment('Ongkos kirim');
            $table->decimal('grand_total', 15, 2)->default(0)->comment('Total akhir setelah diskon, pajak, dll');
            $table->decimal('paid_amount', 15, 2)->default(0)->comment('Jumlah yang sudah dibayar');
            $table->decimal('change_amount', 15, 2)->default(0)->comment('Kembalian (jika bayar lebih)');
            $table->enum('payment_status', ['pending', 'partial', 'paid'])->default('pending')->comment('Status pembayaran');
            $table->enum('payment_method', ['cash', 'transfer', 'debit', 'credit'])->nullable()->comment('Metode pembayaran');
            $table->date('due_date')->nullable()->comment('Tanggal jatuh tempo');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Index untuk mempercepat query
            $table->index('sale_date');
            $table->index('payment_status');
            $table->index('customer_name');
        });

        // ==========================================
        // TABEL 2: sale_items (Detail Barang Keluar)
        // ==========================================
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('restrict');
            $table->integer('quantity');
            $table->decimal('selling_price', 15, 2)->comment('Harga jual per unit (saat transaksi)');
            $table->decimal('discount', 15, 2)->default(0)->comment('Diskon per item');
            $table->decimal('subtotal', 15, 2)->comment('Subtotal (quantity * selling_price) - discount');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Index untuk mempercepat query
            $table->index('sale_id');
            $table->index('product_id');

            // Unique constraint agar tidak duplikat product dalam 1 sale
            $table->unique(['sale_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
};
