<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SellerProfile;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;

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
        $isActivePro = $sellerUser
            && $sellerUser->subscription_plan === 'pro'
            && ($sellerUser->subscription_status ?? 'active') === 'active'
            && (!$sellerUser->subscription_expires_at || $sellerUser->subscription_expires_at->isFuture());
        $commissionRate = $isActivePro ? 3.0 : 8.0;

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
        if ($newStatus === 'shipped' && !empty($validated['tracking_number'])) {
            $updateData['tracking_number'] = $validated['tracking_number'];
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
