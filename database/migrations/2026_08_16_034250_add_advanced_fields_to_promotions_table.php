<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Alter ENUM column to support new types
        DB::statement("ALTER TABLE promotions MODIFY COLUMN type ENUM('product_discount', 'product_markup', 'transaction_discount', 'buy_x_get_y', 'category_discount') NOT NULL");

        Schema::table('promotions', function (Blueprint $table) {
            $table->foreignId('reward_product_id')->nullable()->after('product_id')->constrained('products')->nullOnDelete();
            $table->integer('reward_qty')->nullable()->after('reward_product_id');
            $table->string('category_name')->nullable()->after('reward_qty');
            $table->string('promo_code')->nullable()->unique()->after('category_name');
            $table->integer('usage_limit')->nullable()->after('promo_code');
            $table->integer('used_count')->default(0)->after('usage_limit');
            $table->string('payment_method')->nullable()->after('used_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->dropForeign(['reward_product_id']);
            $table->dropColumn([
                'reward_product_id', 
                'reward_qty', 
                'category_name', 
                'promo_code', 
                'usage_limit', 
                'used_count',
                'payment_method'
            ]);
        });
        
        // Cannot easily revert enum to smaller set if data exists, so we leave the enum extended.
    }
};
