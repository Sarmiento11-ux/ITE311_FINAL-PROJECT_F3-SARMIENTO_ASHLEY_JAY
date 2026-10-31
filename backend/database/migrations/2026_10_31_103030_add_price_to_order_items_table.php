<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'price')) {
                $table->decimal('price', 10, 2)->nullable()->after('quantity');
            }
        });

        // Copy unit_price to price for existing rows
        if (Schema::hasColumn('order_items', 'unit_price') && Schema::hasColumn('order_items', 'price')) {
            DB::statement('UPDATE order_items SET price = unit_price WHERE price IS NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'price')) {
                $table->dropColumn('price');
            }
        });
    }
};
