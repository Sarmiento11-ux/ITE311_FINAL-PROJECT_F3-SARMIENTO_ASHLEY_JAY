<?php

namespace App\Services;

use App\Models\User;

/**
 * Applies artisan storefront verification decisions.
 *
 * Both the Laravel admin API and the headless WordPress CMS bridge funnel
 * through this service so the state transition, audit timestamps, and seller
 * email notification always stay in sync.
 */
class SellerVerificationService
{
    /** Verification statuses accepted by the marketplace. */
    public const STATUSES = ['approved', 'rejected', 'pending'];

    /**
     * Apply a verification decision and notify the artisan when it changes.
     *
     * @return array<string, mixed>
     */
    public static function update(User $seller, string $status, ?string $notes = null): array
    {
        $profile = $seller->sellerProfile;
        $previousStatus = $profile->verification_status;

        $profile->update([
            'verification_status'      => $status,
            'is_verified'              => $status === 'approved',
            'verification_notes'       => $notes !== null ? $notes : $profile->verification_notes,
            'verification_reviewed_at' => now(),
            'verified_at'              => $status === 'approved'
                ? ($profile->verified_at ?? now())
                : null,
        ]);

        $profile->refresh();

        if ($previousStatus !== $status) {
            NotificationService::sendSellerVerificationUpdate(
                $seller->fresh(['sellerProfile']),
                $status,
                $notes
            );
        }

        return [
            'id'                  => $seller->id,
            'shop_name'           => $profile->shop_name,
            'is_verified'         => (bool) $profile->is_verified,
            'verification_status' => $profile->verification_status,
            'verification_notes'  => $profile->verification_notes,
            'verified_at'         => $profile->verified_at?->toISOString(),
        ];
    }
}
