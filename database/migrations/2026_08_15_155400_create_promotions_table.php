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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['product_discount', 'product_markup', 'transaction_discount']);
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->integer('min_qty')->default(1);
            $table->decimal('min_spend', 15, 2)->default(0);
            $table->enum('value_type', ['percentage', 'fixed_amount']);
            $table->decimal('value', 15, 2);
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
