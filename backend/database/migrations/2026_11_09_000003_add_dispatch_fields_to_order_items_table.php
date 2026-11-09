<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * QR dispatch confirmation fields. A dispatch code is generated when an
     * artisan marks an item as shipped; scanning its QR payload confirms the
     * hand-off and timestamps the dispatch.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'dispatch_code')) {
                $table->string('dispatch_code')->nullable()->unique();
            }
            if (!Schema::hasColumn('order_items', 'dispatched_at')) {
                $table->timestamp('dispatched_at')->nullable();
            }
            if (!Schema::hasColumn('order_items', 'dispatch_confirmed_at')) {
                $table->timestamp('dispatch_confirmed_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropUnique(['dispatch_code']);
            $table->dropColumn([
                'dispatch_code',
                'dispatched_at',
                'dispatch_confirmed_at',
            ]);
        });
    }
};
