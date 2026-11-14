<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ArtisanSubscription;
use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    // Tiered Pricing Constants
    const BASIC_MONTHLY_PRICE_PHP   = 149.00;
    const BASIC_ANNUAL_PRICE_PHP    = 1490.00; // ~17% discount (2 months free)
    const BASIC_MONTHLY_CENTAVOS    = 14900;
    const BASIC_ANNUAL_CENTAVOS     = 149000;

    const PREMIUM_MONTHLY_PRICE_PHP = 299.00;
    const PREMIUM_ANNUAL_PRICE_PHP  = 2990.00; // ~17% discount (2 months free)
    const PREMIUM_MONTHLY_CENTAVOS  = 29900;
    const PREMIUM_ANNUAL_CENTAVOS   = 299000;

    // Backwards-compatibility aliases
    const PRO_MONTHLY_PRICE_PHP     = 299.00;
    const PRO_MONTHLY_CENTAVOS      = 29900;

    /**
     * PayMongo API base URL.
     */
    private function baseUrl(): string
    {
        return config('services.paymongo.base_url', 'https://api.paymongo.com/v1');
    }

    /**
     * Base64-encoded secret key for PayMongo Basic Auth.
     */
    private function authHeader(): string
    {
        return 'Basic ' . base64_encode(config('services.paymongo.secret_key') . ':');
    }

    /**
     * Get available subscription plans (Free, Basic, Premium) with Monthly & Annual billing.
     */
    public function getPlans()
    {
        $plans = [
            [
                'id'              => 'free',
                'name'            => 'Free Artisan Starter',
                'price'           => 0,
                'price_formatted' => 'Free',
                'billing_period'  => 'Forever Free',
                'price_monthly'   => 0,
                'price_annual'    => 0,
                'commission_rate' => 8.0,
                'commission_text' => '8% marketplace commission',
                'listing_limit'   => 5,
                'listing_text'    => 'Up to 5 active craft listings',
                'badge'           => 'Starter Artisan',
                'features'        => [
                    'Standard search & catalog visibility',
                    'Direct customer review interactions',
                    'Escrow payouts within 24-48 hours',
                    'Up to 5 active craft listings',
                    'Standard seller dashboard analytics',
                    'Standard email support',
                ],
                'popular'         => false,
            ],
            [
                'id'              => 'basic',
                'name'            => 'Basic Artisan Maker',
                'price'           => (int) self::BASIC_MONTHLY_PRICE_PHP,
                'price_formatted' => '₱' . ((int) self::BASIC_MONTHLY_PRICE_PHP),
                'billing_period'  => 'per month (or ₱1,490/year)',
                'price_monthly'   => self::BASIC_MONTHLY_PRICE_PHP,
                'price_annual'    => self::BASIC_ANNUAL_PRICE_PHP,
                'annual_savings'  => 'Save ₱298/year (2 months free)',
                'commission_rate' => 5.0,
                'commission_text' => 'Reduced 5% commission (Save 3%!)',
                'listing_limit'   => 25,
                'listing_text'    => 'Up to 25 active craft listings',
                'badge'           => 'Verified Artisan Maker',
                'features'        => [
                    'Reduced 5% marketplace commission rate',
                    'Up to 25 active handmade craft listings',
                    'Verified Artisan Maker badge across store',
                    'Priority catalog search indexing',
                    'Flexible monthly & annual billing options',
                    'Standard artisan dashboard analytics',
                ],
                'popular'         => false,
            ],
            [
                'id'              => 'pro',
                'alias'           => 'premium',
                'name'            => 'Premium Artisan Pro',
                'price'           => (int) self::PREMIUM_MONTHLY_PRICE_PHP,
                'price_formatted' => '₱' . ((int) self::PREMIUM_MONTHLY_PRICE_PHP),
                'billing_period'  => 'per month (or ₱2,990/year)',
                'price_monthly'   => self::PREMIUM_MONTHLY_PRICE_PHP,
                'price_annual'    => self::PREMIUM_ANNUAL_PRICE_PHP,
                'annual_savings'  => 'Save ₱598/year (2 months free)',
                'commission_rate' => 3.0,
                'commission_text' => 'Lowest 3% commission (Save 5%!)',
                'listing_limit'   => null, // unlimited
                'listing_text'    => 'Unlimited craft listings',
                'badge'           => '⭐ Pro Artisan Maker',
                'features'        => [
                    'Lowest 3% commission rate on all sales',
                    'Unlimited handmade craft inventory',
                    '⭐ "Pro Artisan" verified badge across catalog',
                    'Homepage & category priority discovery',
                    'Advanced revenue & customer demographics',
                    'Featured maker spotlight eligibility',
                    'Expedited escrow clearing & priority support',
                ],
                'popular'         => true,
            ]
        ];

        return response()->json([
            'success' => true,
            'data'    => $plans
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

        // Check if seller has an active paid subscription record
        $latestActiveSub = ArtisanSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', Carbon::now());
            })
            ->latest()
            ->first();

        if ($latestActiveSub && ($user->subscription_status !== 'active')) {
            $subPlanKey = str_contains(strtolower($latestActiveSub->plan_name), 'basic') ? 'basic' : 'pro';
            $user->update([
                'subscription_plan'       => $subPlanKey,
                'subscription_status'     => 'active',
                'subscription_expires_at' => $latestActiveSub->expires_at,
                'commission_rate'         => $subPlanKey === 'basic' ? 5.00 : 3.00,
                'listing_limit'           => $subPlanKey === 'basic' ? 25 : null,
            ]);
            $user->refresh();
        }

        $rawPlan = $user->subscription_plan ?: 'free';
        $plan = ($rawPlan === 'premium') ? 'pro' : $rawPlan;
        $isPro = ($plan === 'pro');
        $isBasic = ($plan === 'basic' && (float)$user->commission_rate === 5.00);

        $limit = $user->listing_limit;
        if ($limit === null && !$isPro) {
            $limit = $isBasic ? 25 : 5;
        }

        $commissionRate = $user->commission_rate !== null
            ? (float) $user->commission_rate
            : ($isPro ? 3.0 : ($isBasic ? 5.0 : 8.0));

        // Check if paid subscription has expired
        $isExpired = false;
        if ($isPro && $user->subscription_expires_at && Carbon::now()->greaterThan($user->subscription_expires_at)) {
            $isExpired = true;
            // Auto revert to free
            $user->update([
                'subscription_plan'   => 'free',
                'subscription_status' => 'expired',
                'commission_rate'     => 8.00,
                'listing_limit'       => 5,
            ]);
            $plan = 'free';
            $limit = 5;
            $commissionRate = 8.0;
            $isPro = false;
        }

        // Recent billing record
        $latestRecord = ArtisanSubscription::where('user_id', $user->id)
            ->latest()
            ->first();

        return response()->json([
            'success' => true,
            'data'    => [
                'plan'            => $plan,
                'status'          => $user->subscription_status ?: 'active',
                'expires_at'      => $user->subscription_expires_at ? $user->subscription_expires_at->toIso8601String() : null,
                'commission_rate' => $commissionRate,
                'listing_limit'   => $limit,
                'active_listings' => $activeProductsCount,
                'can_add_product' => ($limit === null) || ($activeProductsCount < $limit),
                'is_pro'          => $isPro,
                'billing_cycle'   => $latestRecord?->billing_cycle ?? 'monthly',
                'latest_payment'  => $latestRecord ? [
                    'amount'         => $latestRecord->amount,
                    'paid_at'        => $latestRecord->created_at->toFormattedDateString(),
                    'payment_method' => $latestRecord->payment_method,
                    'status'         => $latestRecord->status,
                ] : null,
            ]
        ]);
    }

    /**
     * Upgrade artisan to Basic or Premium tier with Monthly or Annual billing.
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

        $planId = strtolower($request->input('plan_id', $request->input('plan', 'pro')));
        $billingCycle = strtolower($request->input('billing_cycle', 'monthly')) === 'annual' ? 'annual' : 'monthly';

        // Plan parameters resolution
        if ($planId === 'basic') {
            $planKey        = 'basic';
            $planName       = 'Basic Artisan Maker';
            $commissionRate = 5.00;
            $listingLimit   = 25;
            $pricePhp       = $billingCycle === 'annual' ? self::BASIC_ANNUAL_PRICE_PHP : self::BASIC_MONTHLY_PRICE_PHP;
            $centavos       = $billingCycle === 'annual' ? self::BASIC_ANNUAL_CENTAVOS : self::BASIC_MONTHLY_CENTAVOS;
            $description    = "CraftMarket Basic Artisan ({$billingCycle}) - 5% commission, 25 listings";
        } elseif ($planId === 'free') {
            $user->update([
                'subscription_plan'       => 'free',
                'subscription_status'     => 'active',
                'subscription_expires_at' => null,
                'commission_rate'         => 8.00,
                'listing_limit'           => 5,
            ]);

            return response()->json([
                'success' => true,
                'mode'    => 'free',
                'message' => 'Switched to Free Artisan Starter plan.',
                'data'    => [
                    'plan'            => 'free',
                    'commission_rate' => 8.00,
                    'listing_limit'   => 5,
                    'expires_at'      => null,
                ]
            ]);
        } else {
            // Default: 'pro' or 'premium'
            $planKey        = 'pro';
            $planName       = 'Premium Artisan Pro';
            $commissionRate = 3.00;
            $listingLimit   = null;
            $pricePhp       = $billingCycle === 'annual' ? self::PREMIUM_ANNUAL_PRICE_PHP : self::PREMIUM_MONTHLY_PRICE_PHP;
            $centavos       = $billingCycle === 'annual' ? self::PREMIUM_ANNUAL_CENTAVOS : self::PREMIUM_MONTHLY_CENTAVOS;
            $description    = "CraftMarket Premium Artisan Pro ({$billingCycle}) - 3% commission, unlimited listings";
        }

        $duration = $billingCycle === 'annual' ? Carbon::now()->addYear() : Carbon::now()->addMonth();

        $secretKey = config('services.paymongo.secret_key');
        $isPayMongoConfigured = !empty($secretKey) && !str_contains($secretKey, 'REPLACE_WITH_YOUR');

        if ($isPayMongoConfigured) {
            try {
                $successUrl = env('APP_FRONTEND_URL', 'http://localhost:5173') . '/dashboard/seller?tab=subscription&payment=success';
                $cancelUrl  = env('APP_FRONTEND_URL', 'http://localhost:5173') . '/dashboard/seller?tab=subscription&payment=cancelled';

                $response = Http::withHeaders([
                    'Authorization' => $this->authHeader(),
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ])->post($this->baseUrl() . '/checkout_sessions', [
                    'data' => [
                        'attributes' => [
                            'billing' => [
                                'name'  => $user->name,
                                'email' => $user->email,
                                'phone' => $user->phone ?? '09170000000',
                            ],
                            'send_email_receipt'   => false,
                            'show_description'     => true,
                            'show_line_items'      => true,
                            'line_items'           => [
                                [
                                    'currency'    => 'PHP',
                                    'amount'      => $centavos,
                                    'name'        => $planName . ' (' . ucfirst($billingCycle) . ')',
                                    'quantity'    => 1,
                                    'description' => $description,
                                ]
                            ],
                            'payment_method_types' => ['gcash', 'card', 'paymaya', 'billease', 'dob', 'grab_pay'],
                            'description'          => "{$planName} Subscription ({$billingCycle}) for {$user->name}",
                            'reference_number'     => strtoupper($planKey) . '-' . $user->id . '-' . time(),
                            'success_url'          => $successUrl,
                            'cancel_url'           => $cancelUrl,
                        ]
                    ]
                ]);

                if (!$response->successful()) {
                    $errorBody = $response->json();
                    $errorMsg  = $errorBody['errors'][0]['detail'] ?? 'PayMongo API error. Please try again.';
                    throw new \Exception($errorMsg);
                }

                $sessionData = $response->json('data');
                $sessionId   = $sessionData['id'];
                $checkoutUrl = $sessionData['attributes']['checkout_url'];

                ArtisanSubscription::create([
                    'user_id'             => $user->id,
                    'plan_name'           => $planName,
                    'amount'              => $pricePhp,
                    'billing_cycle'       => $billingCycle,
                    'payment_method'      => 'PayMongo Online',
                    'checkout_session_id' => $sessionId,
                    'status'              => 'pending',
                    'starts_at'           => Carbon::now(),
                    'expires_at'          => $duration,
                ]);

                return response()->json([
                    'success'      => true,
                    'mode'         => 'paymongo',
                    'checkout_url' => $checkoutUrl,
                    'session_id'   => $sessionId,
                    'message'      => 'PayMongo checkout session created. Redirecting to payment page...',
                ]);

            } catch (\Exception $e) {
                Log::error('[PayMongo Subscription] Checkout session creation failed: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'PayMongo session error: ' . $e->getMessage(),
                ], 422);
            }
        }

        // Sandbox / Local fallback: Allowed only in local environment or with explicit test simulation
        if (!app()->environment('local', 'testing') && !$request->boolean('is_test_simulated')) {
            return response()->json([
                'success' => false,
                'message' => 'Online payment verification via PayMongo is required to activate an Artisan Pro membership.',
            ], 422);
        }

        $paymentMethod = $request->input('payment_method', 'Sandbox Simulation (Local Test)');

        DB::beginTransaction();
        try {
            $user->update([
                'subscription_plan'       => $planKey,
                'subscription_status'     => 'active',
                'subscription_expires_at' => $duration,
                'commission_rate'         => $commissionRate,
                'listing_limit'           => $listingLimit,
            ]);

            $record = ArtisanSubscription::create([
                'user_id'        => $user->id,
                'plan_name'      => $planName,
                'amount'         => $pricePhp,
                'billing_cycle'  => $billingCycle,
                'payment_method' => $paymentMethod . ' (Sandbox)',
                'status'         => 'active',
                'starts_at'      => Carbon::now(),
                'expires_at'     => $duration,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'mode'    => 'sandbox',
                'message' => "Congratulations! You have successfully upgraded to {$planName} ({$billingCycle}) in Sandbox Mode.",
                'data'    => [
                    'plan'            => $planKey,
                    'commission_rate' => $commissionRate,
                    'listing_limit'   => $listingLimit,
                    'billing_cycle'   => $billingCycle,
                    'expires_at'      => $duration->toIso8601String(),
                    'billing_id'      => $record->id,
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
     * Verify PayMongo session and activate Artisan Pro upon payment completion.
     */
    public function verifyPaymongoSession(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'seller') {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $sessionId = $request->query('session_id');
        $pendingRecord = ArtisanSubscription::where('user_id', $user->id)
            ->when($sessionId, fn($q) => $q->where('checkout_session_id', $sessionId))
            ->where('status', 'pending')
            ->latest()
            ->first();

        $targetSessionId = $sessionId ?: $pendingRecord?->checkout_session_id;

        if (!$targetSessionId) {
            return response()->json([
                'success' => true,
                'data' => [
                    'is_pro' => $user->subscription_plan === 'pro',
                    'status' => $user->subscription_status,
                ]
            ]);
        }

        $secretKey = config('services.paymongo.secret_key');
        if (empty($secretKey) || str_contains($secretKey, 'REPLACE_WITH_YOUR')) {
            $expiresAt = Carbon::now()->addMonth();
            $user->update([
                'subscription_plan'       => 'pro',
                'subscription_status'     => 'active',
                'subscription_expires_at' => $expiresAt,
                'commission_rate'         => 3.00,
                'listing_limit'           => null,
            ]);
            if ($pendingRecord) {
                $pendingRecord->update(['status' => 'active']);
            }
            return response()->json([
                'success' => true,
                'message' => 'Subscription active.',
                'data'    => ['is_pro' => true]
            ]);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->authHeader(),
                'Accept'        => 'application/json',
            ])->get($this->baseUrl() . '/checkout_sessions/' . $targetSessionId);

            if (!$response->successful()) {
                return response()->json(['success' => false, 'message' => 'Could not verify PayMongo checkout session.'], 422);
            }

            $sessionData = $response->json('data.attributes');
            $pmStatus = $sessionData['payment_intent']['attributes']['status'] ?? $sessionData['status'] ?? null;

            if ($pmStatus === 'succeeded' || $pmStatus === 'paid') {
                $expiresAt = Carbon::now()->addMonth();
                $user->update([
                    'subscription_plan'       => 'pro',
                    'subscription_status'     => 'active',
                    'subscription_expires_at' => $expiresAt,
                    'commission_rate'         => 3.00,
                    'listing_limit'           => null,
                ]);

                ArtisanSubscription::where('checkout_session_id', $targetSessionId)->update([
                    'status'     => 'active',
                    'starts_at'  => Carbon::now(),
                    'expires_at' => $expiresAt,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment verified! Welcome to Artisan Pro.',
                    'data'    => ['is_pro' => true, 'plan' => 'pro']
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => "Payment session status is {$pmStatus}."
            ], 422);

        } catch (\Exception $e) {
            Log::warning('[PayMongo Subscription] Verification error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Cancel artisan subscription (revert to Free Starter).
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
            'subscription_plan'   => 'free',
            'subscription_status' => 'cancelled',
            'commission_rate'     => 8.00,
            'listing_limit'       => 5,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Your subscription has been switched back to the Free Artisan Starter tier. Standard 8% commission applies.',
            'data' => [
                'plan'            => 'free',
                'commission_rate' => 8.00,
                'listing_limit'   => 5,
            ]
        ]);
    }

    /**
     * Admin Endpoint: View subscription MRR, active subscribers & metrics across tiers.
     */
    public function getAdminSubscriptionMetrics(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        // Active Pro/Premium subscriptions: status is active and not expired
        $activeProUserIds = ArtisanSubscription::where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', Carbon::now());
            })
            ->pluck('user_id')
            ->unique()
            ->toArray();

        // Synchronize seller user records whose subscription is actively Pro
        if (!empty($activeProUserIds)) {
            User::whereIn('id', $activeProUserIds)
                ->where('role', 'seller')
                ->whereNotIn('subscription_plan', ['pro', 'premium'])
                ->update([
                    'subscription_plan'       => 'pro',
                    'subscription_status'     => 'active',
                    'commission_rate'         => 3.00,
                    'listing_limit'           => null,
                ]);
        }

        $totalSellers = User::where('role', 'seller')->count();
        $proSellersCount = User::where('role', 'seller')
            ->where(function ($q) use ($activeProUserIds) {
                $q->whereIn('subscription_plan', ['pro', 'premium'])
                  ->orWhereIn('id', $activeProUserIds);
            })
            ->where('subscription_status', 'active')
            ->count();

        $proSellersCount = max($proSellersCount, count($activeProUserIds));

        $basicSellersCount = User::where('role', 'seller')
            ->where('subscription_plan', 'basic')
            ->where('subscription_status', 'active')
            ->count();

        $freeSellersCount = max(0, $totalSellers - ($proSellersCount + $basicSellersCount));

        // Monthly Recurring Revenue = (Active Pro * ₱299) + (Active Basic * ₱149)
        $mrr = round(($proSellersCount * self::PREMIUM_MONTHLY_PRICE_PHP) + ($basicSellersCount * self::BASIC_MONTHLY_PRICE_PHP), 2);

        // Total subscription revenue collected all-time
        $allTimeSubscriptionRevenue = (float) ArtisanSubscription::sum('amount');

        // Recent subscribers list
        $recentSubscriptions = ArtisanSubscription::with('user:id,name,email')
            ->latest()
            ->take(15)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'mrr'                       => $mrr,
                'all_time_revenue'          => (float) $allTimeSubscriptionRevenue,
                'pro_subscribers_count'     => $proSellersCount,
                'premium_subscribers_count' => $proSellersCount,
                'basic_subscribers_count'   => $basicSellersCount,
                'basic_sellers_count'       => $basicSellersCount,
                'free_sellers_count'        => $freeSellersCount,
                'conversion_rate'           => $totalSellers > 0 ? round((($proSellersCount + $basicSellersCount) / $totalSellers) * 100, 1) : 0,
                'recent_records'            => $recentSubscriptions,
            ]
        ]);
    }
}
