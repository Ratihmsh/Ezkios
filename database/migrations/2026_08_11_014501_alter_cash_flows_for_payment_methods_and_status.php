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
        Schema::table('cash_flows', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->change();
            
            if (Schema::hasColumn('cash_flows', 'status')) {
                $table->dropIndex(['status']);
                $table->dropColumn('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_flows', function (Blueprint $table) {
            $table->enum('payment_method', ['cash', 'transfer', 'debit', 'credit', 'bank'])->nullable()->change();
            if (!Schema::hasColumn('cash_flows', 'status')) {
                $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('confirmed');
                $table->index('status');
            }
        });
    }
};
