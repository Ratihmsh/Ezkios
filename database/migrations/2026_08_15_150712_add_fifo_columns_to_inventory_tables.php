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
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->integer('remaining_quantity')->default(0)->after('quantity')->comment('Sisa stok dari kloter ini untuk FIFO');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('total_cogs', 15, 2)->default(0)->after('subtotal')->comment('Total Harga Pokok Penjualan dari kloter yang terpotong');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropColumn('remaining_quantity');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn('total_cogs');
        });
    }
};
