<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        // Commission ledger and escrow exposure
        $commissionEarned = (float) Order::whereNotIn('status', ['cancelled', 'refunded'])
            ->sum('platform_fee');
        $escrowHeld = (float) Order::where('escrow_status', 'held')->sum('seller_payout');
        $escrowReleased = (float) Order::where('escrow_status', 'released_to_seller')->sum('seller_payout');

        return response()->json([
            'success' => true,
            'message' => 'Admin dashboard statistics retrieved successfully.',
            'data'    => [
                'total_users'     => $totalUsers,
                'total_sellers'   => $totalSellers,
                'total_customers' => $totalCustomers,
                'total_products'  => $totalProducts,
                'total_orders'    => $totalOrders,
                'total_sales'     => round($totalSales, 2),
                'orders_pending'  => $ordersPending,
                'orders_paid'     => $ordersPaid,
                'commission_earned' => round($commissionEarned, 2),
                'escrow_held'       => round($escrowHeld, 2),
                'escrow_released'   => round($escrowReleased, 2),
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
     * Comprehensive reports: sales, best sellers, active sellers, status breakdown.
     */
    public function reports()
    {
        // 1. Total Sales & Orders
        $totalSales = (float) Order::whereNotIn('status', ['cancelled'])
            ->where(function ($q) {
                $q->where('payment_status', 'paid')
                  ->orWhereIn('status', ['confirmed', 'processing', 'shipped', 'delivered']);
            })
            ->sum('total_amount');

        $totalOrders = Order::count();

        // 2. Order status summary
        $statusCounts = Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $allStatuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
        $orderStatusSummary = [];
        foreach ($allStatuses as $st) {
            $orderStatusSummary[$st] = $statusCounts[$st] ?? 0;
        }

        // 3. Payment status summary
        $paymentStatusSummary = Order::select('payment_status', DB::raw('count(*) as count'))
            ->groupBy('payment_status')
            ->pluck('count', 'payment_status')
            ->toArray();

        // 4. Best-selling products (actual aggregation from order_items)
        $bestSellingProducts = OrderItem::select(
                'product_id',
                'product_name',
                DB::raw('SUM(quantity) as total_sold'),
                DB::raw('SUM(subtotal) as total_revenue')
            )
            ->whereHas('order', function ($q) {
                $q->whereNotIn('status', ['cancelled']);
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

        // 6. Commission ledger and escrow breakdown
        $escrowTotals = Order::select(
                'escrow_status',
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(platform_fee) as platform_fees'),
                DB::raw('SUM(seller_payout) as seller_payouts')
            )
            ->groupBy('escrow_status')
            ->get()
            ->keyBy('escrow_status');

        $commissionLedger = [];

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

        return response()->json([
            'success' => true,
            'message' => 'Reports data generated successfully.',
            'data'    => [
                'total_sales'           => round($totalSales, 2),
                'total_orders'          => $totalOrders,
                'order_status_summary'  => $orderStatusSummary,
                'payment_status_summary'=> $paymentStatusSummary,
                'best_selling_products' => $bestSellingProducts,
                'most_active_sellers'   => $mostActiveSellers,
                'total_commission'      => round($totalCommission, 2),
                'commission_ledger'     => $commissionLedger,
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
                'bio'            => $s->sellerProfile?->bio,
                'products_count' => $s->products_count,
                'total_sales'    => $totalSales,
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
                'bio'                => $seller->sellerProfile?->bio,
                'products_count'     => $seller->products_count,
                'total_sales'        => $totalSales,
                'total_order_items'  => $totalOrderItems,
                'products'           => $seller->products,
            ],
        ]);
    }
}
