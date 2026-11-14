<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\PayoutRequest;
use App\Models\Product;
use App\Models\SellerProfile;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SellerController extends Controller
{
    /**
     * Get products belonging to a seller.
     * Sellers may only see their own products. Admins see any seller's products.
     */
    public function products(Request $request)
    {
        $user = $request->user();

        // Admin can query any seller_id; seller is locked to own id
        if ($user->role === 'admin') {
            $sellerId = $request->query('seller_id', $user->id);
        } else {
            $sellerId = $user->id; // seller always scoped to self
        }

        $products = Product::with('category')
            ->where('seller_id', $sellerId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Seller products retrieved successfully.',
            'data'    => $products,
        ]);
    }

    /**
     * Get orders/items received by a seller.
     */
    public function orders(Request $request)
    {
        $user = $request->user();
        $sellerId = $user->role === 'admin' 
            ? $request->query('seller_id', $user->id) 
            : $user->id;

        $items = OrderItem::with(['order.user:id,name,email', 'product'])
            ->where('seller_id', $sellerId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Seller orders retrieved successfully.',
            'data'    => $items,
        ]);
    }

    /**
     * Get dashboard summary stats for a seller.
     */
    public function stats(Request $request)
    {
        $user = $request->user();
        $sellerId = $user->role === 'admin' 
            ? $request->query('seller_id', $user->id) 
            : $user->id;

        $totalProducts = Product::where('seller_id', $sellerId)->count();
        $activeProducts = Product::where('seller_id', $sellerId)->where('status', 'active')->count();

        $orderItems = OrderItem::with('order')->where('seller_id', $sellerId)->get();
        $totalOrders = $orderItems->pluck('order_id')->unique()->count();
        $validSalesItems = $orderItems->filter(function ($item) {
            $itemStatus = strtolower($item->status ?? '');
            $orderStatus = strtolower($item->order->status ?? '');
            return !in_array($itemStatus, ['cancelled', 'refunded']) && !in_array($orderStatus, ['cancelled', 'refunded']);
        });
        $totalSales = (float) $validSalesItems->sum('subtotal');

        $sellerUser = \App\Models\User::find($sellerId);
        $commissionRate = 8.0;
        if ($sellerUser) {
            if ($sellerUser->commission_rate !== null && is_numeric($sellerUser->commission_rate)) {
                $commissionRate = (float) $sellerUser->commission_rate;
            } else {
                $isActive = ($sellerUser->subscription_status ?? 'active') === 'active'
                    && (!$sellerUser->subscription_expires_at || $sellerUser->subscription_expires_at->isFuture());
                if ($isActive) {
                    if (in_array($sellerUser->subscription_plan, ['pro', 'premium'])) {
                        $commissionRate = 3.0;
                    } elseif ($sellerUser->subscription_plan === 'basic') {
                        $commissionRate = 5.0;
                    }
                }
            }
        }

        // Prefer the per-item commission snapshot recorded at checkout.
        $snapshotFees = (float) $validSalesItems->sum(fn($item) => (float) ($item->platform_fee ?? 0));
        $snapshotPayout = (float) $validSalesItems->sum(fn($item) => (float) ($item->seller_payout ?? 0));

        if ($snapshotFees > 0 || $snapshotPayout > 0) {
            $platformFee = round($snapshotFees, 2);
            $netEarnings = round($snapshotPayout, 2);
        } else {
            $platformFee = round($totalSales * ($commissionRate / 100), 2);
            $netEarnings = round($totalSales - $platformFee, 2);
        }

        $pendingStatuses = ['pending', 'confirmed', 'processing', 'shipped'];
        $pendingOrders = $orderItems->filter(function ($item) use ($pendingStatuses) {
            $itemStatus = strtolower($item->status ?? '');
            $orderStatus = strtolower($item->order->status ?? '');
            return in_array($itemStatus, $pendingStatuses) || in_array($orderStatus, $pendingStatuses);
        })->pluck('order_id')->unique()->count();
        $completedOrders = $orderItems->filter(fn($item) => $item->status === 'delivered')->pluck('order_id')->unique()->count();

        // ─── Revenue analytics: 6-month trend, top crafts, growth ────────────
        $salesTrend = [];
        $cursor = now()->startOfMonth()->subMonths(5);

        for ($i = 0; $i < 6; $i++) {
            $monthKey = $cursor->format('Y-m');
            $monthItems = $validSalesItems->filter(
                fn($item) => $item->created_at && $item->created_at->format('Y-m') === $monthKey
            );

            $salesTrend[] = [
                'month'  => $monthKey,
                'label'  => $cursor->format('M Y'),
                'sales'  => round((float) $monthItems->sum('subtotal'), 2),
                'orders' => $monthItems->pluck('order_id')->unique()->count(),
            ];

            $cursor->addMonth();
        }

        $topProducts = $validSalesItems
            ->groupBy('product_id')
            ->map(function ($items) {
                $first = $items->first();

                return [
                    'product_id' => (int) $first->product_id,
                    'name'       => $first->product_name,
                    'quantity'   => (int) $items->sum('quantity'),
                    'revenue'    => round((float) $items->sum('subtotal'), 2),
                ];
            })
            ->sortByDesc('revenue')
            ->take(5)
            ->values();

        $currentMonthSales = (float) $validSalesItems
            ->filter(fn($item) => $item->created_at && $item->created_at->isSameMonth(now()))
            ->sum('subtotal');
        $lastMonthSales = (float) $validSalesItems
            ->filter(fn($item) => $item->created_at && $item->created_at->isSameMonth(now()->subMonth()))
            ->sum('subtotal');
        $revenueGrowth = $lastMonthSales > 0
            ? round((($currentMonthSales - $lastMonthSales) / $lastMonthSales) * 100, 1)
            : ($currentMonthSales > 0 ? 100.0 : 0.0);

        return response()->json([
            'success' => true,
            'message' => 'Seller statistics retrieved successfully.',
            'data'    => [
                'total_products'    => $totalProducts,
                'active_products'   => $activeProducts,
                'total_orders'      => $totalOrders,
                'total_sales'       => $totalSales,
                'commission_rate'   => $commissionRate,
                'platform_fee'      => $platformFee,
                'net_earnings'      => $netEarnings,
                'pending_orders'    => $pendingOrders,
                'completed_orders'  => $completedOrders,
                'average_order_value' => $totalOrders > 0 ? round($totalSales / $totalOrders, 2) : 0.0,
                'revenue_this_month'  => round($currentMonthSales, 2),
                'revenue_last_month'  => round($lastMonthSales, 2),
                'revenue_growth_percent' => $revenueGrowth,
                'sales_trend'       => $salesTrend,
                'top_products'      => $topProducts,
            ],
        ]);
    }

    /**
     * Update the fulfillment status of an individual seller order item.
     * Supports multi-seller orders where each artisan fulfills their own items.
     *
     * PATCH /api/seller/order-items/{id}/status
     */
    public function updateItemStatus(Request $request, $id)
    {
        $user = $request->user();
        $item = OrderItem::with('order.items')->find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Order item not found.',
                'data'    => null,
            ], 404);
        }

        // Authorization: Only the artisan who owns this product or an admin may update fulfillment
        if ($user->role !== 'admin' && $item->seller_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden. You can only update fulfillment for your own craft items.',
                'data'    => null,
            ], 403);
        }

        $validated = $request->validate([
            'status'          => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled',
            'tracking_number' => 'nullable|string|max:120',
        ]);

        $newStatus = $validated['status'];
        $currentStatus = $item->status ?? 'pending';

        // Validate state transitions
        $invalidTransitions = [
            'delivered' => ['pending', 'confirmed', 'processing'],
            'cancelled' => ['pending', 'confirmed', 'processing', 'shipped', 'delivered'],
        ];

        if (isset($invalidTransitions[$currentStatus]) && in_array($newStatus, $invalidTransitions[$currentStatus])) {
            return response()->json([
                'success' => false,
                'message' => "Cannot transition order item from '{$currentStatus}' to '{$newStatus}'.",
                'data'    => null,
            ], 422);
        }

        $order = $item->order;

        $updateData = ['status' => $newStatus];
        if ($newStatus === 'shipped') {
            if (!empty($validated['tracking_number'])) {
                $updateData['tracking_number'] = $validated['tracking_number'];
            }
            if (!$item->dispatch_code) {
                $updateData['dispatch_code'] = $this->generateUniqueDispatchCode();
            }
            $updateData['dispatched_at'] = $item->dispatched_at ?? now();
        }
        if ($newStatus === 'delivered') {
            $updateData['dispatch_confirmed_at'] = $item->dispatch_confirmed_at ?? now();
        }

        // Keep the escrow ledger in sync with the item's fulfillment state.
        if ($newStatus === 'delivered') {
            $updateData['escrow_status'] = 'released_to_seller';
            $updateData['escrow_released_at'] = now();
        } elseif ($newStatus === 'cancelled') {
            $updateData['escrow_status'] = $order && $order->payment_status === 'paid' ? 'refunded' : 'cancelled';
        }

        $item->update($updateData);

        // Send fulfillment update notification
        NotificationService::sendSellerItemFulfillment($item);

        // Evaluate parent order status across all multi-seller items
        if ($order) {
            $allItems = $order->items()->get();
            $allDelivered = $allItems->every(fn($i) => $i->status === 'delivered');
            $allCancelled = $allItems->every(fn($i) => $i->status === 'cancelled');

            if ($allDelivered) {
                $order->update([
                    'status'             => 'delivered',
                    'delivered_at'       => now(),
                    'payment_status'     => 'paid',
                    'escrow_status'      => 'released_to_seller',
                    'escrow_released_at' => $order->escrow_released_at ?? now(),
                ]);
                NotificationService::sendOrderStatusUpdate($order, 'delivered');
            } elseif ($allCancelled) {
                $isPaid = $order->payment_status === 'paid';
                $order->update([
                    'status'         => 'cancelled',
                    'escrow_status'  => $isPaid ? 'refunded' : 'cancelled',
                    'payment_status' => $isPaid ? 'refunded' : $order->payment_status,
                ]);
                NotificationService::sendOrderStatusUpdate($order, 'cancelled');
            } elseif (in_array($newStatus, ['processing', 'shipped']) && $order->status === 'pending') {
                $order->update(['status' => 'processing']);
                NotificationService::sendOrderStatusUpdate($order, 'processing');
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Order item fulfillment updated to '{$newStatus}'.",
            'data'    => $item->fresh(['order.user:id,name', 'product']),
        ]);
    }

    /**
     * GET /api/seller/payouts
     * Show released earnings, reserved payouts, and the seller's payout history.
     */
    public function payouts(Request $request)
    {
        $user = $request->user();
        $sellerId = $user->role === 'admin'
            ? (int) $request->query('seller_id', $user->id)
            : $user->id;

        $requests = PayoutRequest::where('seller_id', $sellerId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Payout ledger retrieved successfully.',
            'data'    => array_merge($this->payoutSummary($sellerId), [
                'requests' => $requests,
            ]),
        ]);
    }

    /**
     * POST /api/seller/payouts
     * Request a cash-out from escrow funds that have already been released.
     */
    public function requestPayout(Request $request)
    {
        $user = $request->user();
        $sellerId = $user->role === 'admin'
            ? (int) $request->input('seller_id', $user->id)
            : $user->id;

        $validated = $request->validate([
            'amount'         => 'required|numeric|min:1|max:1000000',
            'payout_method'  => 'nullable|string|max:100',
            'account_name'   => 'nullable|string|max:150',
            'account_number' => 'nullable|string|max:100',
            'notes'          => 'nullable|string|max:2000',
        ]);

        $summary = $this->payoutSummary($sellerId);

        if (round((float) $validated['amount'], 2) > $summary['available_balance']) {
            return response()->json([
                'success' => false,
                'message' => 'Requested amount exceeds your available released escrow balance of ₱' . number_format($summary['available_balance'], 2) . '.',
                'data'    => $summary,
            ], 422);
        }

        $payout = PayoutRequest::create([
            'seller_id'      => $sellerId,
            'amount'         => round((float) $validated['amount'], 2),
            'status'         => 'pending',
            'payout_method'  => $validated['payout_method'] ?? null,
            'account_name'   => $validated['account_name'] ?? null,
            'account_number' => $validated['account_number'] ?? null,
            'notes'          => $validated['notes'] ?? null,
            'requested_at'   => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payout request submitted. An administrator will review it shortly.',
            'data'    => array_merge($this->payoutSummary($sellerId), [
                'request' => $payout,
            ]),
        ], 201);
    }

    /**
     * DELETE /api/seller/payouts/{id}
     * Cancel a payout request that is still pending review.
     */
    public function cancelPayout(Request $request, $id)
    {
        $user = $request->user();
        $sellerId = $user->role === 'admin'
            ? (int) $request->query('seller_id', $user->id)
            : $user->id;

        $payout = PayoutRequest::where('seller_id', $sellerId)->find($id);

        if (!$payout) {
            return response()->json([
                'success' => false,
                'message' => 'Payout request not found.',
                'data'    => null,
            ], 404);
        }

        if ($payout->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending payout requests can be cancelled.',
                'data'    => null,
            ], 422);
        }

        $payout->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payout request cancelled.',
            'data'    => $this->payoutSummary($sellerId),
        ]);
    }

    /**
     * POST /api/seller/order-items/{id}/dispatch-code
     * Generate (or return) the QR dispatch payload for an order item.
     */
    public function generateDispatchCode(Request $request, $id)
    {
        $user = $request->user();
        $item = OrderItem::with('order')->find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Order item not found.',
                'data'    => null,
            ], 404);
        }

        if ($user->role !== 'admin' && $item->seller_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden. You can only dispatch your own craft items.',
                'data'    => null,
            ], 403);
        }

        if (in_array($item->status, ['delivered', 'cancelled'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'A dispatch QR code cannot be generated for a completed or cancelled item.',
                'data'    => null,
            ], 422);
        }

        $code = $item->dispatch_code ?: $this->generateUniqueDispatchCode();

        if (!$item->dispatch_code) {
            $item->update(['dispatch_code' => $code]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Dispatch QR code ready.',
            'data'    => [
                'order_item_id' => $item->id,
                'order_number'  => $item->order?->order_number,
                'dispatch_code' => $code,
                'qr_payload'    => json_encode([
                    'type'          => 'craftmarket_dispatch',
                    'order_item_id' => $item->id,
                    'order_number'  => $item->order?->order_number,
                    'dispatch_code' => $code,
                ]),
                'dispatched_at' => $item->dispatched_at?->toISOString(),
                'dispatch_confirmed_at' => $item->dispatch_confirmed_at?->toISOString(),
            ],
        ]);
    }

    /**
     * POST /api/seller/order-items/{id}/dispatch-confirm
     * Confirm an artisan dispatch by scanning/verifying the QR dispatch code.
     */
    public function confirmDispatch(Request $request, $id)
    {
        $user = $request->user();
        $item = OrderItem::with('order')->find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Order item not found.',
                'data'    => null,
            ], 404);
        }

        if ($user->role !== 'admin' && $item->seller_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden. You can only confirm dispatch for your own craft items.',
                'data'    => null,
            ], 403);
        }

        $validated = $request->validate([
            'dispatch_code'   => 'required|string|max:120',
            'tracking_number' => 'nullable|string|max:120',
        ]);

        if (!$item->dispatch_code || !hash_equals($item->dispatch_code, $validated['dispatch_code'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid dispatch code. Please scan the QR code generated for this order item.',
                'data'    => null,
            ], 422);
        }

        if (in_array($item->status, ['delivered', 'cancelled'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'This order item is already completed and cannot be dispatched again.',
                'data'    => null,
            ], 422);
        }

        $update = [
            'status'                => 'shipped',
            'dispatched_at'         => $item->dispatched_at ?? now(),
            'dispatch_confirmed_at' => now(),
        ];

        if (!empty($validated['tracking_number'])) {
            $update['tracking_number'] = $validated['tracking_number'];
        }

        $item->update($update);

        $order = $item->order;
        if ($order && $order->status === 'pending') {
            $order->update(['status' => 'processing']);
            NotificationService::sendOrderStatusUpdate($order, 'processing');
        }

        NotificationService::sendSellerItemFulfillment($item->fresh());

        return response()->json([
            'success' => true,
            'message' => 'Dispatch confirmed via QR scan. The item is now marked as shipped.',
            'data'    => $item->fresh(['order.user:id,name', 'product']),
        ]);
    }

    /**
     * Compute the seller's escrow earnings and payout balance.
     */
    private function payoutSummary(int $sellerId): array
    {
        $released = (float) OrderItem::where('seller_id', $sellerId)
            ->where('escrow_status', 'released_to_seller')
            ->sum('seller_payout');

        $held = (float) OrderItem::where('seller_id', $sellerId)
            ->where('escrow_status', 'held')
            ->sum('seller_payout');

        $reserved = (float) PayoutRequest::where('seller_id', $sellerId)
            ->whereIn('status', ['pending', 'approved'])
            ->sum('amount');

        $paid = (float) PayoutRequest::where('seller_id', $sellerId)
            ->where('status', 'paid')
            ->sum('amount');

        $available = max(0, round($released - $reserved - $paid, 2));

        return [
            'released_earnings' => round($released, 2),
            'escrow_held'       => round($held, 2),
            'reserved_amount'   => round($reserved, 2),
            'paid_amount'       => round($paid, 2),
            'available_balance' => $available,
            'pending_count'     => PayoutRequest::where('seller_id', $sellerId)->where('status', 'pending')->count(),
            'approved_count'    => PayoutRequest::where('seller_id', $sellerId)->where('status', 'approved')->count(),
        ];
    }

    /**
     * Generate a collision-free dispatch code for an order item.
     */
    private function generateUniqueDispatchCode(): string
    {
        for ($attempt = 0; $attempt < 20; $attempt++) {
            $code = 'CM-DSP-' . strtoupper(Str::random(10));

            if (!OrderItem::where('dispatch_code', $code)->exists()) {
                return $code;
            }
        }

        return 'CM-DSP-' . strtoupper(Str::random(20));
    }

    /**
     * POST /api/seller/verification-request
     * Submit or re-submit the artisan storefront for administrator review.
     */
    public function requestVerification(Request $request)
    {
        $user = $request->user();

        $sellerId = $user->role === 'admin'
            ? (int) $request->input('seller_id', $user->id)
            : $user->id;

        $profile = SellerProfile::where('user_id', $sellerId)->first();

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Seller storefront profile not found.',
                'data'    => null,
            ], 404);
        }

        if ($profile->verification_status === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Your artisan workshop is already verified and approved.',
                'data'    => $profile,
            ], 422);
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $profile->update([
            'verification_status' => 'pending',
            'verification_notes'  => $validated['notes'] ?? $profile->verification_notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Storefront verification request submitted for administrator review.',
            'data'    => $profile->fresh(),
        ]);
    }

    /**
     * Get seller profile information.
     */
    public function profile($id)
    {
        $seller = User::with('sellerProfile')->where('id', $id)->first();

        if (!$seller) {
            return response()->json([
                'success' => false,
                'message' => 'Seller not found.',
                'data'    => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Seller profile retrieved successfully.',
            'data'    => $seller,
        ]);
    }
}
