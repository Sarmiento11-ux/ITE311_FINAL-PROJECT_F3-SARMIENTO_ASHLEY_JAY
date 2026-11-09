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
    const PRO_MONTHLY_PRICE_PHP = 299.00;
    const PRO_MONTHLY_CENTAVOS  = 29900;

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
                'price' => (int) self::PRO_MONTHLY_PRICE_PHP,
                'price_formatted' => '₱' . ((int) self::PRO_MONTHLY_PRICE_PHP),
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

        // Check if seller has an active Artisan Pro subscription record
        $latestActivePro = ArtisanSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', Carbon::now());
            })
            ->latest()
            ->first();

        if ($latestActivePro && ($user->subscription_plan !== 'pro' || $user->subscription_status !== 'active')) {
            $user->update([
                'subscription_plan'       => 'pro',
                'subscription_status'     => 'active',
                'subscription_expires_at' => $latestActivePro->expires_at,
                'commission_rate'         => 3.00,
                'listing_limit'           => null,
            ]);
            $user->refresh();
        }

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
     * Upgrade artisan to Artisan Pro via PayMongo checkout or dev sandbox.
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
                                    'amount'      => self::PRO_MONTHLY_CENTAVOS, // ₱299.00 in centavos
                                    'name'        => 'CraftMarket Artisan Pro Monthly Membership',
                                    'quantity'    => 1,
                                    'description' => 'Unlimited craft listings, 3% commission, and Pro Artisan badge',
                                ]
                            ],
                            'payment_method_types' => ['gcash', 'card', 'paymaya', 'billease', 'dob', 'grab_pay'],
                            'description'          => 'Artisan Pro Subscription for ' . $user->name,
                            'reference_number'     => 'PRO-' . $user->id . '-' . time(),
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
                    'plan_name'           => 'Artisan Pro',
                    'amount'              => self::PRO_MONTHLY_PRICE_PHP,
                    'billing_cycle'       => 'monthly',
                    'payment_method'      => 'PayMongo Online',
                    'checkout_session_id' => $sessionId,
                    'status'              => 'pending',
                    'starts_at'           => Carbon::now(),
                    'expires_at'          => Carbon::now()->addMonth(),
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
        $expiresAt = Carbon::now()->addMonth();

        DB::beginTransaction();
        try {
            $user->update([
                'subscription_plan'       => 'pro',
                'subscription_status'     => 'active',
                'subscription_expires_at' => $expiresAt,
                'commission_rate'         => 3.00,
                'listing_limit'           => null,
            ]);

            $record = ArtisanSubscription::create([
                'user_id'        => $user->id,
                'plan_name'      => 'Artisan Pro',
                'amount'         => self::PRO_MONTHLY_PRICE_PHP,
                'billing_cycle'  => 'monthly',
                'payment_method' => $paymentMethod . ' (Sandbox)',
                'status'         => 'active',
                'starts_at'      => Carbon::now(),
                'expires_at'     => $expiresAt,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'mode'    => 'sandbox',
                'message' => 'Congratulations! You have successfully upgraded to Artisan Pro (Sandbox Mode).',
                'data'    => [
                    'plan'            => 'pro',
                    'commission_rate' => 3.00,
                    'listing_limit'   => null,
                    'expires_at'      => $expiresAt->toIso8601String(),
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

        // Active Pro subscriptions: status is active and not expired
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
                ->where('subscription_plan', '!=', 'pro')
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
                $q->where('subscription_plan', 'pro')
                  ->orWhereIn('id', $activeProUserIds);
            })
            ->count();

        // Guard: pro count is at least the number of active subscriptions
        $proSellersCount = max($proSellersCount, count($activeProUserIds));
        $basicSellersCount = max(0, $totalSellers - $proSellersCount);

        // Monthly Recurring Revenue = Active Pro Sellers * ₱299
        $mrr = $proSellersCount * 299.00;

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
