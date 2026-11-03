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
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();

            // Customer who saved the product
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Product that was wishlisted
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->cascadeOnDelete();

            $table->timestamps();

            // Enforce that a customer can only have one wishlist entry per product
            $table->unique(['user_id', 'product_id'], 'unique_user_product_wishlist');

            // Quick lookup indexes
            $table->index('user_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wishlists');
    }
};
