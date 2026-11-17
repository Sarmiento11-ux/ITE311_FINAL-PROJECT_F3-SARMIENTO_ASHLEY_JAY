<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_zone')->nullable()->after('shipping_fee');
            // snapshot of seller's location at time of order (for zone display & audit)
            $table->string('seller_location')->nullable()->after('shipping_zone');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_zone', 'seller_location']);
        });
    }
};
