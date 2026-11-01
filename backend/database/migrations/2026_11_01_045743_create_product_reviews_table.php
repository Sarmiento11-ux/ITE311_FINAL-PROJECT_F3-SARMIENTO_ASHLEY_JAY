<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the product_reviews table for CraftMarket's review and rating system.
     */
    public function up(): void
    {
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();

            // Reviewer: must be a registered user (customer)
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Reviewed product
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->cascadeOnDelete();

            // The specific order item that qualifies this review (nullable for legacy)
            $table->foreignId('order_item_id')
                  ->nullable()
                  ->constrained('order_items')
                  ->nullOnDelete();

            // Star rating: 1–5
            $table->tinyInteger('rating')->unsigned()->default(5);

            // Written review (optional)
            $table->text('comment')->nullable();

            $table->timestamps();

            // A customer can only review each product once
            $table->unique(['user_id', 'product_id'], 'unique_user_product_review');

            // Indexes for fast lookups
            $table->index('product_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};
