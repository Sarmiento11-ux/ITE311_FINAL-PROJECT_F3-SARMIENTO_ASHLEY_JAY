<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ArtisanSubscription;
use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    /**
     * Get available subscription plans.
     */
    public function getPlans()
    {
        $plans = [
            [
                'id' => 'basic',
                'name' => 'Basic Artisan',
                'price' => 0,
                'price_formatted' => 'Free',
                'billing_period' => 'Forever Free',
                'commission_rate' => 8.0,
                'commission_text' => '8% marketplace commission',
                'listing_limit' => 10,
                'listing_text' => 'Up to 10 active craft listings',
                'badge' => 'Standard Artisan',
                'features' => [
                    'Standard search & catalog visibility',
                    'Direct customer review interactions',
                    'Escrow payouts within 24-48 hours',
                    'Standard seller dashboard analytics',
                    'Standard customer support'
                ],
                'popular' => false,
            ],
            [
                'id' => 'pro',
                'name' => 'Artisan Pro',
                'price' => 299,
                'price_formatted' => '₱299',
                'billing_period' => 'per month',
                'commission_rate' => 3.0,
                'commission_text' => 'Only 3% commission (Save 5%!)',
                'listing_limit' => null, // unlimited
                'listing_text' => 'Unlimited craft listings',
                'badge' => '⭐ Pro Artisan Maker',
                'features' => [
                    'Reduced 3% commission rate on all sales',
                    'Unlimited handmade craft inventory',
                    '⭐ "Pro Artisan" verified badge across catalog',
                    'Homepage & category priority discovery',
                    'Advanced revenue & customer demographics',
                    'Featured maker spotlight eligibility',
                    'Priority artisan assistance & escrow clearing'
                ],
                'popular' => true,
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $plans
        ]);
    }

    /**
     * Get the authenticated seller's current subscription status and usage.
     */
    public function getCurrentSubscription(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'seller') {
            return response()->json([
                'success' => false,
                'message' => 'Only artisan sellers have access to subscription plans.'
            ], 403);
        }

        $activeProductsCount = Product::where('seller_id', $user->id)->count();

        $plan = $user->subscription_plan ?: 'basic';
        $limit = $user->listing_limit !== null ? (int)$user->listing_limit : ($plan === 'basic' ? 10 : null);
        $commissionRate = $user->commission_rate !== null ? (float)$user->commission_rate : ($plan === 'pro' ? 3.0 : 8.0);

        // Check if pro subscription has expired
        $isExpired = false;
        if ($plan === 'pro' && $user->subscription_expires_at && Carbon::now()->greaterThan($user->subscription_expires_at)) {
            $isExpired = true;
            // Auto revert to basic
            $user->update([
                'subscription_plan' => 'basic',
                'subscription_status' => 'expired',
                'commission_rate' => 8.00,
                'listing_limit' => 10,
            ]);
            $plan = 'basic';
            $limit = 10;
            $commissionRate = 8.0;
        }

        // Recent billing record
        $latestRecord = ArtisanSubscription::where('user_id', $user->id)
            ->latest()
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'plan' => $plan,
                'status' => $user->subscription_status ?: 'active',
                'expires_at' => $user->subscription_expires_at ? $user->subscription_expires_at->toIso8601String() : null,
                'commission_rate' => $commissionRate,
                'listing_limit' => $limit,
                'active_listings' => $activeProductsCount,
                'can_add_product' => ($limit === null) || ($activeProductsCount < $limit),
                'is_pro' => ($plan === 'pro'),
                'latest_payment' => $latestRecord ? [
                    'amount' => $latestRecord->amount,
                    'paid_at' => $latestRecord->created_at->toFormattedDateString(),
                    'payment_method' => $latestRecord->payment_method,
                    'status' => $latestRecord->status,
                ] : null,
            ]
        ]);
    }

    /**
     * Upgrade artisan to Artisan Pro.
     */
    public function upgradeToPro(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'seller') {
            return response()->json([
                'success' => false,
                'message' => 'Only artisans can upgrade subscription plans.'
            ], 403);
        }

        $paymentMethod = $request->input('payment_method', 'GCash / Online Wallet');
        $expiresAt = Carbon::now()->addMonth();

        DB::beginTransaction();
        try {
            // Update user to Pro
            $user->update([
                'subscription_plan' => 'pro',
                'subscription_status' => 'active',
                'subscription_expires_at' => $expiresAt,
                'commission_rate' => 3.00,
                'listing_limit' => null, // unlimited
            ]);

            // Create billing history record
            $record = ArtisanSubscription::create([
                'user_id' => $user->id,
                'plan_name' => 'Artisan Pro',
                'amount' => 299.00,
                'billing_cycle' => 'monthly',
                'payment_method' => $paymentMethod,
                'status' => 'active',
                'starts_at' => Carbon::now(),
                'expires_at' => $expiresAt,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Congratulations! You have successfully upgraded to Artisan Pro.',
                'data' => [
                    'plan' => 'pro',
                    'commission_rate' => 3.00,
                    'listing_limit' => null,
                    'expires_at' => $expiresAt->toIso8601String(),
                    'billing_id' => $record->id,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Could not complete subscription upgrade: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel artisan subscription (revert to Basic).
     */
    public function cancelSubscription(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'seller') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        $user->update([
            'subscription_plan' => 'basic',
            'subscription_status' => 'cancelled',
            'commission_rate' => 8.00,
            'listing_limit' => 10,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Your subscription has been switched back to the Basic Free tier. Standard 8% commission applies.',
            'data' => [
                'plan' => 'basic',
                'commission_rate' => 8.00,
                'listing_limit' => 10,
            ]
        ]);
    }

    /**
     * Admin Endpoint: View subscription MRR, active subscribers & metrics.
     */
    public function getAdminSubscriptionMetrics(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $totalSellers = User::where('role', 'seller')->count();
        $proSellersCount = User::where('role', 'seller')->where('subscription_plan', 'pro')->count();
        $basicSellersCount = $totalSellers - $proSellersCount;

        // Monthly Recurring Revenue = Active Pro Sellers * ₱299
        $mrr = $proSellersCount * 299.00;

        // Total subscription revenue collected all-time
        $allTimeSubscriptionRevenue = ArtisanSubscription::sum('amount');

        // Recent subscribers list
        $recentSubscriptions = ArtisanSubscription::with('user:id,name,email')
            ->latest()
            ->take(15)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'mrr' => $mrr,
                'all_time_revenue' => (float)$allTimeSubscriptionRevenue,
                'pro_subscribers_count' => $proSellersCount,
                'basic_sellers_count' => $basicSellersCount,
                'conversion_rate' => $totalSellers > 0 ? round(($proSellersCount / $totalSellers) * 100, 1) : 0,
                'recent_records' => $recentSubscriptions,
            ]
        ]);
    }
}
