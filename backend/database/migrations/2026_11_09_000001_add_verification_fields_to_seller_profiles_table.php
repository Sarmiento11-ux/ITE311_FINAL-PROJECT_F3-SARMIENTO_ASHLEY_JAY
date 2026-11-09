<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds the artisan storefront approval workflow fields promised by the SRS:
     * administrators review new sellers and approve or reject their storefronts.
     */
    public function up(): void
    {
        Schema::table('seller_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('seller_profiles', 'verification_status')) {
                $table->string('verification_status')->default('pending');
            }
            if (!Schema::hasColumn('seller_profiles', 'verification_notes')) {
                $table->text('verification_notes')->nullable();
            }
            if (!Schema::hasColumn('seller_profiles', 'verified_at')) {
                $table->timestamp('verified_at')->nullable();
            }
            if (!Schema::hasColumn('seller_profiles', 'verification_reviewed_at')) {
                $table->timestamp('verification_reviewed_at')->nullable();
            }
        });

        // Backfill legacy verified artisans so the new workflow reflects existing data.
        DB::table('seller_profiles')
            ->where('is_verified', true)
            ->update([
                'verification_status'      => 'approved',
                'verified_at'              => DB::raw('COALESCE(verified_at, updated_at)'),
                'verification_reviewed_at' => DB::raw('COALESCE(verification_reviewed_at, updated_at)'),
            ]);
    }

    public function down(): void
    {
        Schema::table('seller_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'verification_status',
                'verification_notes',
                'verified_at',
                'verification_reviewed_at',
            ]);
        });
    }
};
