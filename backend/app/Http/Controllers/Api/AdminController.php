<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PayoutRequest;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminController extends Controller
{
    /**
     * GET /api/admin/stats
     * Overview summary cards: total users, sellers, customers, products, orders, sales.
     */
    public function stats()
    {
        $totalUsers     = User::count();
        $totalSellers   = User::where('role', 'seller')->count();
        $totalCustomers = User::where('role', 'customer')->count();
        $totalProducts  = Product::count();
        $totalOrders    = Order::count();

        // Total sales: sum of non-cancelled orders with paid status or delivered status
        $totalSales = (float) Order::whereNotIn('status', ['cancelled'])
            ->where(function ($q) {
                $q->where('payment_status', 'paid')
                  ->orWhereIn('status', ['confirmed', 'processing', 'shipped', 'delivered']);
            })
            ->sum('total_amount');

        // Order count by status
        $ordersPending = Order::where('status', 'pending')->count();
        $ordersPaid    = Order::where('payment_status', 'paid')->count();

        // Commission ledger and escrow exposure (safely guarded against missing schema columns)
        $hasEscrowColumns = Schema::hasColumn('orders', 'platform_fee');
        $commissionEarned = $hasEscrowColumns
            ? (float) Order::whereNotIn('status', ['cancelled', 'refunded'])->sum('platform_fee')
            : 0.0;
        $escrowHeld = ($hasEscrowColumns && Schema::hasColumn('orders', 'escrow_status'))
            ? (float) Order::where('escrow_status', 'held')->sum('seller_payout')
            : 0.0;
        $escrowReleased = ($hasEscrowColumns && Schema::hasColumn('orders', 'escrow_status'))
            ? (float) Order::where('escrow_status', 'released_to_seller')->sum('seller_payout')
            : 0.0;

        $payoutsPending = (float) PayoutRequest::whereIn('status', ['pending', 'approved'])->sum('amount');
        $payoutsPaid    = (float) PayoutRequest::where('status', 'paid')->sum('amount');

        // Platform Monetization: Commission + Pro Subscriptions
        $subscriptionRevenue = class_exists(\App\Models\ArtisanSubscription::class)
            ? (float) \App\Models\ArtisanSubscription::sum('amount')
            : 0.0;
        $proSellersCount = User::where('role', 'seller')
            ->whereIn('subscription_plan', ['pro', 'premium'])
            ->where('subscription_status', 'active')
            ->count();
        $subscriptionMrr = $proSellersCount * 299.00;
        $totalPlatformRevenue = $commissionEarned + $subscriptionRevenue;

        return response()->json([
            'success' => true,
            'message' => 'Admin dashboard statistics retrieved successfully.',
            'data'    => [
                'total_users'            => $totalUsers,
                'total_sellers'          => $totalSellers,
                'total_customers'        => $totalCustomers,
                'total_products'         => $totalProducts,
                'total_orders'           => $totalOrders,
                'total_sales'            => round($totalSales, 2),
                'orders_pending'         => $ordersPending,
                'orders_paid'            => $ordersPaid,
                'commission_earned'      => round($commissionEarned, 2),
                'subscription_revenue'   => round($subscriptionRevenue, 2),
                'subscription_mrr'       => round($subscriptionMrr, 2),
                'total_platform_revenue' => round($totalPlatformRevenue, 2),
                'pro_sellers_count'      => $proSellersCount,
                'escrow_held'            => round($escrowHeld, 2),
                'escrow_released'        => round($escrowReleased, 2),
                'payouts_pending'        => round($payoutsPending, 2),
                'payouts_paid'           => round($payoutsPaid, 2),
            ],
        ]);
    }

    /**
     * GET /api/admin/recent-activity
     * Returns recent orders, recently registered users, and latest marketplace activities.
     */
    public function recentActivity()
    {
        // 1. Recent Orders
        $recentOrders = Order::with(['user:id,name,email', 'items.product', 'latestPayment'])
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        // 2. Recently Registered Users
        $recentUsers = User::with('sellerProfile')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        // 3. Synthesize chronological marketplace activities
        $activities = [];

        // Latest orders
        foreach ($recentOrders->take(4) as $o) {
            $activities[] = [
                'id'         => 'order_' . $o->id,
                'type'       => 'order',
                'title'      => "Order {$o->order_number} placed",
                'desc'       => "₱" . number_format($o->total_amount, 2) . " by " . ($o->user?->name ?? 'Customer'),
                'status'     => $o->status,
                'created_at' => $o->created_at->toISOString(),
            ];
        }

        // Latest users
        foreach ($recentUsers->take(4) as $u) {
            $activities[] = [
                'id'         => 'user_' . $u->id,
                'type'       => 'user',
                'title'      => "New {$u->role} joined",
                'desc'       => "{$u->name} ({$u->email})",
                'status'     => $u->is_active ? 'active' : 'inactive',
                'created_at' => $u->created_at->toISOString(),
            ];
        }

        // Latest products
        $recentProducts = Product::with('seller:id,name')->orderBy('created_at', 'desc')->limit(4)->get();
        foreach ($recentProducts as $p) {
            $activities[] = [
                'id'         => 'prod_' . $p->id,
                'type'       => 'product',
                'title'      => "New craft listed",
                'desc'       => "\"{$p->name}\" by " . ($p->seller?->name ?? 'Artisan'),
                'status'     => $p->status,
                'created_at' => $p->created_at->toISOString(),
            ];
        }

        // Sort combined activities by created_at descending
        usort($activities, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));
        $activities = array_slice($activities, 0, 8);

        return response()->json([
            'success' => true,
            'data'    => [
                'recent_orders' => $recentOrders,
                'recent_users'  => $recentUsers,
                'activities'    => $activities,
            ],
        ]);
    }

    /**
     * GET /api/admin/reports
     * Comprehensive reports: sales, best sellers, active sellers, status breakdown with period filtering.
     */
    public function reports(Request $request)
    {
        $period = $request->query('period', 'all');
        $startDate = match ($period) {
            'today' => now()->startOfDay(),
            'week'  => now()->subDays(7)->startOfDay(),
            'month' => now()->subDays(30)->startOfDay(),
            'year'  => now()->subDays(365)->startOfDay(),
            default => null,
        };

        // 1. Total Sales & Orders
        $salesQuery = Order::whereNotIn('status', ['cancelled'])
            ->where(function ($q) {
                $q->where('payment_status', 'paid')
                  ->orWhereIn('status', ['confirmed', 'processing', 'shipped', 'delivered']);
            });

        $ordersQuery = Order::query();

        if ($startDate) {
            $salesQuery->where('created_at', '>=', $startDate);
            $ordersQuery->where('created_at', '>=', $startDate);
        }

        $totalSales = (float) $salesQuery->sum('total_amount');
        $totalOrders = $ordersQuery->count();

        // Count only completed/fulfilled non-cancelled orders for accurate AOV
        $completedQuery = Order::whereNotIn('status', ['cancelled', 'refunded'])
            ->where(function ($q) {
                $q->where('payment_status', 'paid')
                  ->orWhereIn('status', ['confirmed', 'processing', 'shipped', 'delivered']);
            });

        if ($startDate) {
            $completedQuery->where('created_at', '>=', $startDate);
        }

        $completedOrdersCount = $completedQuery->count();
        $averageOrderValue = $completedOrdersCount > 0 ? round($totalSales / $completedOrdersCount, 2) : 0;

        // 2. Order status summary
        $statusQuery = Order::select('status', DB::raw('count(*) as count'));
        if ($startDate) {
            $statusQuery->where('created_at', '>=', $startDate);
        }
        $statusCounts = $statusQuery->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $allStatuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
        $orderStatusSummary = [];
        foreach ($allStatuses as $st) {
            $orderStatusSummary[$st] = $statusCounts[$st] ?? 0;
        }

        // 3. Payment status summary
        $paymentQuery = Order::select('payment_status', DB::raw('count(*) as count'));
        if ($startDate) {
            $paymentQuery->where('created_at', '>=', $startDate);
        }
        $paymentStatusSummary = $paymentQuery->groupBy('payment_status')
            ->pluck('count', 'payment_status')
            ->toArray();

        // 4. Best-selling products (actual aggregation from order_items)
        $bestSellingProducts = OrderItem::select(
                'product_id',
                'product_name',
                DB::raw('SUM(quantity) as total_sold'),
                DB::raw('SUM(subtotal) as total_revenue')
            )
            ->whereHas('order', function ($q) use ($startDate) {
                $q->whereNotIn('status', ['cancelled']);
                if ($startDate) {
                    $q->where('created_at', '>=', $startDate);
                }
            })
            ->groupBy('product_id', 'product_name')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                $product = Product::with('category')->find($item->product_id);
                return [
                    'product_id'    => $item->product_id,
                    'name'          => $item->product_name,
                    'category'      => $product?->category?->name ?? 'Handmade',
                    'image'         => $product?->image,
                    'unit_price'    => (float) ($product?->price ?? 0),
                    'total_sold'    => (int) $item->total_sold,
                    'total_revenue' => (float) $item->total_revenue,
                ];
            });

        // 5. Most active sellers (by product count and total sales)
        $sellers = User::where('role', 'seller')
            ->with('sellerProfile')
            ->withCount('products')
            ->get();

        $mostActiveSellers = $sellers->map(function ($seller) {
            $sales = (float) OrderItem::where('seller_id', $seller->id)
                ->whereHas('order', function ($q) {
                    $q->whereNotIn('status', ['cancelled']);
                })
                ->sum('subtotal');

            $ordersCount = OrderItem::where('seller_id', $seller->id)
                ->distinct('order_id')
                ->count('order_id');

            return [
                'id'             => $seller->id,
                'name'           => $seller->name,
                'email'          => $seller->email,
                'shop_name'      => $seller->sellerProfile?->shop_name ?? ($seller->name . "'s Shop"),
                'products_count' => $seller->products_count,
                'orders_count'   => $ordersCount,
                'total_sales'    => $sales,
                'is_active'      => (bool) $seller->is_active,
            ];
        })
        ->sortByDesc('total_sales')
        ->values()
        ->take(5);

        // 6. Commission ledger and escrow breakdown (safely guarded against missing schema columns)
        $hasEscrowColumns = Schema::hasColumn('orders', 'platform_fee');
        $commissionLedger = [];

        if ($hasEscrowColumns && Schema::hasColumn('orders', 'escrow_status')) {
            $escrowTotals = Order::select(
                    'escrow_status',
                    DB::raw('COUNT(*) as orders_count'),
                    DB::raw('SUM(platform_fee) as platform_fees'),
                    DB::raw('SUM(seller_payout) as seller_payouts')
                )
                ->groupBy('escrow_status')
                ->get()
                ->keyBy('escrow_status');

            foreach (['pending', 'held', 'released_to_seller', 'refunded', 'cancelled'] as $escrowStatus) {
                $row = $escrowTotals->get($escrowStatus);

                $commissionLedger[$escrowStatus] = [
                    'orders_count'   => (int) ($row->orders_count ?? 0),
                    'platform_fees'  => round((float) ($row->platform_fees ?? 0), 2),
                    'seller_payouts' => round((float) ($row->seller_payouts ?? 0), 2),
                ];
            }

            $totalCommission = (float) Order::whereNotIn('status', ['cancelled', 'refunded'])
                ->sum('platform_fee');
        } else {
            foreach (['pending', 'held', 'released_to_seller', 'refunded', 'cancelled'] as $escrowStatus) {
                $commissionLedger[$escrowStatus] = [
                    'orders_count'   => 0,
                    'platform_fees'  => 0.0,
                    'seller_payouts' => 0.0,
                ];
            }
            $totalCommission = 0.0;
        }

        // 7. Seller payout settlement summary (cash-out requests)
        $payoutSummary = [
            'pending_count'   => PayoutRequest::where('status', 'pending')->count(),
            'pending_amount'  => round((float) PayoutRequest::where('status', 'pending')->sum('amount'), 2),
            'approved_count'  => PayoutRequest::where('status', 'approved')->count(),
            'approved_amount' => round((float) PayoutRequest::where('status', 'approved')->sum('amount'), 2),
            'paid_count'      => PayoutRequest::where('status', 'paid')->count(),
            'paid_amount'     => round((float) PayoutRequest::where('status', 'paid')->sum('amount'), 2),
            'rejected_count'  => PayoutRequest::where('status', 'rejected')->count(),
            'rejected_amount' => round((float) PayoutRequest::where('status', 'rejected')->sum('amount'), 2),
            'total_requested' => round((float) PayoutRequest::sum('amount'), 2),
        ];

        // 8. Subscription revenue (Tiered Artisan Monetization: Free, Basic, Premium/Pro)
        $proSubscribers = User::where('role', 'seller')
            ->whereIn('subscription_plan', ['pro', 'premium'])
            ->where('subscription_status', 'active')
            ->count();

        $basicSubscribers = User::where('role', 'seller')
            ->where('subscription_plan', 'basic')
            ->where('subscription_status', 'active')
            ->count();

        $freeSellers = User::where('role', 'seller')
            ->where(function ($q) {
                $q->whereIn('subscription_plan', ['free'])
                  ->orWhereNull('subscription_plan')
                  ->orWhere('subscription_status', '!=', 'active');
            })
            ->count();

        $mrr = round(($proSubscribers * 299.00) + ($basicSubscribers * 149.00), 2);

        $subscriptionRevenue = [
            'pro_subscribers_count'     => $proSubscribers,
            'premium_subscribers_count' => $proSubscribers,
            'basic_subscribers_count'   => $basicSubscribers,
            'free_sellers_count'        => $freeSellers,
            'mrr'                       => $mrr,
            'all_time_revenue'          => round((float) \App\Models\ArtisanSubscription::sum('amount'), 2),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Reports data generated successfully.',
            'data'    => [
                'period'                 => $period,
                'total_sales'           => round($totalSales, 2),
                'total_orders'          => $totalOrders,
                'completed_orders_count'=> $completedOrdersCount,
                'average_order_value'   => $averageOrderValue,
                'order_status_summary'  => $orderStatusSummary,
                'payment_status_summary'=> $paymentStatusSummary,
                'best_selling_products' => $bestSellingProducts,
                'most_active_sellers'   => $mostActiveSellers,
                'total_commission'      => round($totalCommission, 2),
                'commission_ledger'     => $commissionLedger,
                'payout_summary'        => $payoutSummary,
                'subscription_revenue'  => $subscriptionRevenue,
            ],
        ]);
    }

    /**
     * GET /api/admin/sellers
     * Detailed list of all registered sellers.
     */
    public function sellers(Request $request)
    {
        $query = User::where('role', 'seller')
            ->with('sellerProfile')
            ->withCount('products');

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('sellerProfile', function ($sq) use ($search) {
                      $sq->where('shop_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('is_active') && $request->query('is_active') !== '') {
            $query->where('is_active', filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('verification_status')) {
            $status = $request->query('verification_status');
            $query->whereHas('sellerProfile', function ($sq) use ($status) {
                $sq->where('verification_status', $status);
            });
        }

        $sellers = $query->orderBy('created_at', 'desc')->get()->map(function ($s) {
            $totalSales = (float) OrderItem::where('seller_id', $s->id)
                ->whereHas('order', function ($q) {
                    $q->whereNotIn('status', ['cancelled']);
                })
                ->sum('subtotal');

            return [
                'id'             => $s->id,
                'name'           => $s->name,
                'email'          => $s->email,
                'phone'          => $s->phone ?? $s->sellerProfile?->phone,
                'address'        => $s->address ?? $s->sellerProfile?->address,
                'is_active'      => (bool) $s->is_active,
                'shop_name'      => $s->sellerProfile?->shop_name ?? ($s->name . "'s Workshop"),
                'bio'            => $s->sellerProfile?->shop_description,
                'products_count' => $s->products_count,
                'total_sales'    => $totalSales,
                'is_verified'    => (bool) ($s->sellerProfile?->is_verified ?? false),
                'verification_status' => $s->sellerProfile?->verification_status ?? 'pending',
                'verification_notes'  => $s->sellerProfile?->verification_notes,
                'verified_at'         => $s->sellerProfile?->verified_at?->toISOString(),
                'created_at'     => $s->created_at->format('M d, Y'),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Sellers retrieved successfully.',
            'data'    => $sellers,
        ]);
    }

    /**
     * GET /api/admin/sellers/{id}
     * View detailed seller information including profile and products.
     */
    public function sellerDetails($id)
    {
        $seller = User::where('role', 'seller')
            ->with(['sellerProfile', 'products.category'])
            ->withCount('products')
            ->find($id);

        if (!$seller) {
            return response()->json([
                'success' => false,
                'message' => 'Seller not found.',
                'data'    => null,
            ], 404);
        }

        $totalSales = (float) OrderItem::where('seller_id', $seller->id)
            ->whereHas('order', function ($q) {
                $q->whereNotIn('status', ['cancelled']);
            })
            ->sum('subtotal');

        $totalOrderItems = OrderItem::where('seller_id', $seller->id)->count();

        return response()->json([
            'success' => true,
            'data'    => [
                'id'                 => $seller->id,
                'name'               => $seller->name,
                'email'              => $seller->email,
                'phone'              => $seller->phone,
                'address'            => $seller->address,
                'is_active'          => (bool) $seller->is_active,
                'created_at'         => $seller->created_at->format('M d, Y'),
                'shop_name'          => $seller->sellerProfile?->shop_name,
                'bio'                => $seller->sellerProfile?->shop_description,
                'is_verified'        => (bool) ($seller->sellerProfile?->is_verified ?? false),
                'verification_status' => $seller->sellerProfile?->verification_status ?? 'pending',
                'verification_notes'  => $seller->sellerProfile?->verification_notes,
                'verified_at'         => $seller->sellerProfile?->verified_at?->toISOString(),
                'products_count'     => $seller->products_count,
                'total_sales'        => $totalSales,
                'total_order_items'  => $totalOrderItems,
                'products'           => $seller->products,
            ],
        ]);
    }

    /**
     * PATCH /api/admin/sellers/{id}/verification
     * Approve, reject, or reset an artisan storefront application.
     */
    public function updateSellerVerification(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected,pending',
            'notes'  => 'nullable|string|max:2000',
        ]);

        $seller = User::where('role', 'seller')->with('sellerProfile')->find($id);

        if (!$seller) {
            return response()->json([
                'success' => false,
                'message' => 'Seller not found.',
                'data'    => null,
            ], 404);
        }

        $profile = $seller->sellerProfile;

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'This seller has no storefront profile to review.',
                'data'    => null,
            ], 422);
        }

        $status = $validated['status'];
        $profile->update([
            'verification_status'      => $status,
            'is_verified'              => $status === 'approved',
            'verification_notes'       => $validated['notes'] ?? $profile->verification_notes,
            'verification_reviewed_at' => now(),
            'verified_at'              => $status === 'approved'
                ? ($profile->verified_at ?? now())
                : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Artisan storefront {$status} successfully.",
            'data'    => [
                'id'                  => $seller->id,
                'shop_name'           => $profile->shop_name,
                'is_verified'         => (bool) $profile->fresh()->is_verified,
                'verification_status' => $profile->fresh()->verification_status,
                'verification_notes'  => $profile->fresh()->verification_notes,
                'verified_at'         => $profile->fresh()->verified_at?->toISOString(),
            ],
        ]);
    }

    /**
     * GET /api/admin/payouts
     * Audit seller payout requests and settlement balances.
     */
    public function payoutRequests(Request $request)
    {
        $query = PayoutRequest::with(['seller.sellerProfile'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('seller_id')) {
            $query->where('seller_id', $request->query('seller_id'));
        }

        $requests = $query->get();

        $summary = [
            'pending_count'   => PayoutRequest::where('status', 'pending')->count(),
            'pending_amount'  => round((float) PayoutRequest::where('status', 'pending')->sum('amount'), 2),
            'approved_count'  => PayoutRequest::where('status', 'approved')->count(),
            'approved_amount' => round((float) PayoutRequest::where('status', 'approved')->sum('amount'), 2),
            'paid_count'      => PayoutRequest::where('status', 'paid')->count(),
            'paid_amount'     => round((float) PayoutRequest::where('status', 'paid')->sum('amount'), 2),
            'rejected_count'  => PayoutRequest::where('status', 'rejected')->count(),
            'rejected_amount' => round((float) PayoutRequest::where('status', 'rejected')->sum('amount'), 2),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Payout requests retrieved successfully.',
            'data'    => [
                'summary'  => $summary,
                'requests' => $requests,
            ],
        ]);
    }

    /**
     * PATCH /api/admin/payouts/{id}
     * Approve, reject, or mark an artisan payout as paid.
     */
    public function updatePayoutRequest(Request $request, $id)
    {
        $validated = $request->validate([
            'action'      => 'required|in:approve,reject,pay',
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        $payout = PayoutRequest::with('seller:id,name,email')->find($id);

        if (!$payout) {
            return response()->json([
                'success' => false,
                'message' => 'Payout request not found.',
                'data'    => null,
            ], 404);
        }

        $action = $validated['action'];

        if (in_array($payout->status, ['paid', 'rejected'], true)) {
            return response()->json([
                'success' => false,
                'message' => "This payout request is already {$payout->status} and can no longer be changed.",
                'data'    => null,
            ], 422);
        }

        if ($action === 'pay' && $payout->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Only approved payout requests can be marked as paid.',
                'data'    => null,
            ], 422);
        }

        $update = [
            'admin_notes' => $validated['admin_notes'] ?? $payout->admin_notes,
        ];

        if ($action === 'approve') {
            $update['status'] = 'approved';
            $update['reviewed_at'] = now();
            $message = 'Payout request approved. It is now queued for settlement.';
        } elseif ($action === 'reject') {
            $update['status'] = 'rejected';
            $update['reviewed_at'] = now();
            $message = 'Payout request rejected. The reserved amount is available again.';
        } else {
            $update['status'] = 'paid';
            $update['paid_at'] = now();
            $update['reviewed_at'] = $payout->reviewed_at ?? now();
            $message = 'Payout marked as paid successfully.';
        }

        $payout->update($update);

        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $payout->fresh(['seller:id,name,email']),
        ]);
    }

    /**
     * PATCH /api/admin/orders/{id}/escrow
     * Manual financial audit action to release, hold, or refund order escrow.
     */
    public function releaseEscrow(Request $request, $id)
    {
        $validated = $request->validate([
            'action' => 'required|in:release,hold,refund,cancel',
            'notes'  => 'nullable|string|max:2000',
        ]);

        $order = Order::with('items')->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
                'data'    => null,
            ], 404);
        }

        $action = $validated['action'];

        if ($action === 'release' && $order->payment_status !== 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Escrow can only be released for orders that have been paid.',
                'data'    => null,
            ], 422);
        }

        $orderUpdate = [];
        $itemUpdate  = [];

        if ($action === 'release') {
            $orderUpdate = [
                'escrow_status'      => 'released_to_seller',
                'escrow_released_at' => $order->escrow_released_at ?? now(),
            ];
            $itemUpdate = [
                'escrow_status'      => 'released_to_seller',
                'escrow_released_at' => now(),
            ];
            $message = 'Escrow released to the artisan.';
        } elseif ($action === 'hold') {
            $orderUpdate = [
                'escrow_status'      => 'held',
                'escrow_released_at' => null,
            ];
            $itemUpdate = [
                'escrow_status'      => 'held',
                'escrow_released_at' => null,
            ];
            $message = 'Escrow funds placed back on hold.';
        } elseif ($action === 'refund') {
            $orderUpdate = [
                'escrow_status'  => 'refunded',
                'payment_status' => 'refunded',
            ];
            $itemUpdate = ['escrow_status' => 'refunded'];
            $message = 'Escrow refunded to the customer.';
        } else {
            $orderUpdate = ['escrow_status' => 'cancelled'];
            $itemUpdate  = ['escrow_status' => 'cancelled'];
            $message = 'Escrow ledger marked as cancelled.';
        }

        DB::transaction(function () use ($order, $orderUpdate, $itemUpdate) {
            $order->update($orderUpdate);
            $order->items()->update($itemUpdate);
        });

        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $order->fresh(['user:id,name,email', 'items.product', 'items.seller:id,name']),
        ]);
    }
}
