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
        // 1. Add subscription fields to users table (sellers)
        Schema::table('users', function (Blueprint $table) {
            $table->string('subscription_plan', 50)->default('basic')->after('role'); // 'basic' or 'pro'
            $table->string('subscription_status', 50)->default('active')->after('subscription_plan'); // 'active', 'cancelled', 'expired'
            $table->timestamp('subscription_expires_at')->nullable()->after('subscription_status');
            $table->decimal('commission_rate', 5, 2)->default(8.00)->after('subscription_expires_at'); // 8.00% for basic, 3.00% for pro
            $table->integer('listing_limit')->nullable()->default(10)->after('commission_rate'); // 10 for basic, null (unlimited) for pro
        });

        // 2. Create subscriptions history/billing log table
        Schema::create('artisan_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('plan_name', 50); // 'Artisan Pro'
            $table->decimal('amount', 10, 2); // 299.00
            $table->string('billing_cycle', 20)->default('monthly'); // 'monthly', 'annual'
            $table->string('payment_method', 50)->default('GCash / Card');
            $table->string('status', 50)->default('active'); // 'active', 'cancelled', 'expired'
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artisan_subscriptions');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'subscription_plan',
                'subscription_status',
                'subscription_expires_at',
                'commission_rate',
                'listing_limit'
            ]);
        });
    }
};
