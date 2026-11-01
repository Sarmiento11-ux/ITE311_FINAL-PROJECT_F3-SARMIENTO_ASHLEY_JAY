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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Payment gateway info
            $table->string('payment_method');             // e.g. 'gcash', 'card', 'paymaya', 'Cash on Delivery'
            $table->string('payment_gateway')->default('paymongo'); // 'paymongo' | 'cod'
            $table->string('payment_reference')->nullable()->unique(); // PayMongo payment_intent or checkout_session ID
            $table->string('checkout_session_id')->nullable();         // PayMongo checkout session ID
            $table->string('checkout_url')->nullable();                // Hosted checkout URL to redirect customer

            // Financials (stored for audit, always verified server-side)
            $table->decimal('amount', 10, 2);

            // Status: pending | paid | failed | cancelled | refunded
            $table->string('status')->default('pending');

            // Timestamps
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('order_id');
            $table->index('status');
            $table->index('payment_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
