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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique()->nullable()->comment('Kode produk (SKU/Barcode)');
            $table->string('category')->nullable()->comment('Kategori produk');
            $table->string('brand')->nullable()->comment('Merek produk');
            $table->decimal('purchase_price', 15, 2)->default(0)->comment('Harga beli');
            $table->decimal('selling_price', 15, 2)->default(0)->comment('Harga jual');
            $table->decimal('discount', 15, 2)->default(0)->comment('Diskon (opsional)');
            $table->integer('stock')->default(0)->comment('Stok saat ini');
            $table->integer('min_stock')->default(5)->comment('Stok minimal (peringatan)');
            $table->string('unit')->nullable()->comment('Satuan (pcs, kg, box, dll)');
            $table->text('description')->nullable();
            $table->string('image')->nullable()->comment('Path gambar produk');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
