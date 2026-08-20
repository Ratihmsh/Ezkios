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
        Schema::table('purchases', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->comment('Metode pembayaran (dinamis)')->change();
            $table->string('receipt_image')->nullable()->comment('Path gambar nota transaksi')->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->enum('payment_method', ['cash', 'transfer', 'bank'])->nullable()->change();
            $table->dropColumn('receipt_image');
        });
    }
};
