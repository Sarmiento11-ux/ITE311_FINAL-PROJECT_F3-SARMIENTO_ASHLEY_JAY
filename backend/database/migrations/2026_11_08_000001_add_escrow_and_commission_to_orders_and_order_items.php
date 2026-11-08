<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add escrow ledger and commission snapshot fields.
     *
     * Escrow lifecycle: pending -> held -> released_to_seller
     *                   pending/held -> refunded | cancelled
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'escrow_status')) {
                $table->string('escrow_status')->default('pending');
            }
            if (!Schema::hasColumn('orders', 'platform_fee')) {
                $table->decimal('platform_fee', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('orders', 'seller_payout')) {
                $table->decimal('seller_payout', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('orders', 'escrow_released_at')) {
                $table->timestamp('escrow_released_at')->nullable();
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'commission_rate')) {
                $table->decimal('commission_rate', 5, 2)->default(8.00);
            }
            if (!Schema::hasColumn('order_items', 'platform_fee')) {
                $table->decimal('platform_fee', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('order_items', 'seller_payout')) {
                $table->decimal('seller_payout', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('order_items', 'escrow_status')) {
                $table->string('escrow_status')->default('pending');
            }
            if (!Schema::hasColumn('order_items', 'escrow_released_at')) {
                $table->timestamp('escrow_released_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'escrow_status',
                'platform_fee',
                'seller_payout',
                'escrow_released_at',
            ]);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn([
                'commission_rate',
                'platform_fee',
                'seller_payout',
                'escrow_status',
                'escrow_released_at',
            ]);
        });
    }
};
