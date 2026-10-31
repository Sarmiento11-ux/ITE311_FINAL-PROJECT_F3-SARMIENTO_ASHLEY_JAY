<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    /**
     * Get products belonging to a seller.
     */
    public function products(Request $request)
    {
        $sellerId = $request->query('seller_id', $request->user()?->id);

        if (!$sellerId) {
            return response()->json([
                'success' => false,
                'message' => 'Seller ID is required.',
                'data'    => null,
            ], 400);
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
        $sellerId = $request->query('seller_id', $request->user()?->id);

        if (!$sellerId) {
            return response()->json([
                'success' => false,
                'message' => 'Seller ID is required.',
                'data'    => null,
            ], 400);
        }

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
        $sellerId = $request->query('seller_id', $request->user()?->id);

        if (!$sellerId) {
            return response()->json([
                'success' => false,
                'message' => 'Seller ID is required.',
                'data'    => null,
            ], 400);
        }

        $totalProducts = Product::where('seller_id', $sellerId)->count();
        $activeProducts = Product::where('seller_id', $sellerId)->where('status', 'active')->count();

        $orderItems = OrderItem::with('order')->where('seller_id', $sellerId)->get();
        $totalOrders = $orderItems->pluck('order_id')->unique()->count();
        $totalSales = $orderItems->sum('subtotal');

        $pendingOrders = $orderItems->filter(fn($item) => $item->order && $item->order->status === 'pending')->pluck('order_id')->unique()->count();
        $completedOrders = $orderItems->filter(fn($item) => $item->order && $item->order->status === 'delivered')->pluck('order_id')->unique()->count();

        return response()->json([
            'success' => true,
            'message' => 'Seller statistics retrieved successfully.',
            'data'    => [
                'total_products'    => $totalProducts,
                'active_products'   => $activeProducts,
                'total_orders'      => $totalOrders,
                'total_sales'       => (float) $totalSales,
                'pending_orders'    => $pendingOrders,
                'completed_orders'  => $completedOrders,
            ],
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
