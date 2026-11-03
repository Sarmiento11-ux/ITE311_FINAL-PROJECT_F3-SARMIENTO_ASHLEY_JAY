<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /**
     * Display a listing of the customer's wishlist items.
     *
     * GET /api/wishlist
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $wishlistItems = Wishlist::with(['product.category', 'product.seller:id,name'])
            ->where('user_id', $userId)
            ->latest()
            ->get();

        $items = $wishlistItems->map(function ($item) {
            $product = $item->product;

            return [
                'id'            => $item->id,
                'product_id'    => $item->product_id,
                'name'          => $product ? $product->name : 'Unavailable Product',
                'title'         => $product ? $product->name : 'Unavailable Product',
                'price'         => $product ? (float) $product->price : 0,
                'image'         => $product ? $product->image_url : null,
                'stock'         => $product ? (int) $product->stock : 0,
                'status'        => $product ? $product->status : 'inactive',
                'is_available'  => $product && $product->status === 'active' && $product->stock > 0,
                'seller_name'   => $product?->seller?->name ?? 'Artisan Maker',
                'seller'        => $product?->seller?->name ?? 'Artisan Maker',
                'category_name' => $product?->category?->name ?? 'Handmade',
                'category'      => $product?->category?->name ?? 'Handmade',
                'rating'        => $product ? $product->avg_rating : 0,
                'created_at'    => $item->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'count'   => $items->count(),
            'data'    => $items,
        ]);
    }

    /**
     * Retrieve just the wishlisted product IDs for fast frontend matching.
     *
     * GET /api/wishlist/ids
     */
    public function ids(Request $request)
    {
        $userId = $request->user()->id;

        $productIds = Wishlist::where('user_id', $userId)
            ->pluck('product_id')
            ->values()
            ->all();

        return response()->json([
            'success'     => true,
            'product_ids' => $productIds,
        ]);
    }

    /**
     * Check if a specific product is in the customer's wishlist.
     *
     * GET /api/wishlist/status/{productId}
     */
    public function status(Request $request, int $productId)
    {
        $userId = $request->user()->id;

        $inWishlist = Wishlist::where('user_id', $userId)
            ->where('product_id', $productId)
            ->exists();

        return response()->json([
            'success'     => true,
            'product_id'  => $productId,
            'in_wishlist' => $inWishlist,
        ]);
    }

    /**
     * Add a product to the customer's wishlist.
     *
     * POST /api/wishlist
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
        ]);

        $userId = $request->user()->id;
        $productId = (int) $validated['product_id'];

        $product = Product::with(['category', 'seller:id,name'])->findOrFail($productId);

        // Prevent duplicate entries
        $existing = Wishlist::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            return response()->json([
                'success'     => true,
                'message'     => 'Product is already in your wishlist.',
                'in_wishlist' => true,
                'data'        => [
                    'id'         => $existing->id,
                    'product_id' => $existing->product_id,
                ],
            ], 200);
        }

        $wishlist = Wishlist::create([
            'user_id'    => $userId,
            'product_id' => $productId,
        ]);

        return response()->json([
            'success'     => true,
            'message'     => 'Product saved to your favorites.',
            'in_wishlist' => true,
            'data'        => [
                'id'            => $wishlist->id,
                'product_id'    => $wishlist->product_id,
                'name'          => $product->name,
                'price'         => (float) $product->price,
                'image'         => $product->image_url,
                'seller_name'   => $product->seller?->name ?? 'Artisan Maker',
                'category_name' => $product->category?->name ?? 'Handmade',
                'is_available'  => $product->status === 'active' && $product->stock > 0,
            ],
        ], 201);
    }

    /**
     * Remove a product from the customer's wishlist.
     *
     * DELETE /api/wishlist/{productId}
     */
    public function destroy(Request $request, int $productId)
    {
        $userId = $request->user()->id;

        $deleted = Wishlist::where('user_id', $userId)
            ->where('product_id', $productId)
            ->delete();

        if (!$deleted) {
            return response()->json([
                'success'     => false,
                'message'     => 'Product not found in your wishlist.',
                'in_wishlist' => false,
            ], 404);
        }

        return response()->json([
            'success'     => true,
            'message'     => 'Product removed from your wishlist.',
            'in_wishlist' => false,
        ]);
    }

    /**
     * Clear all items from customer's wishlist.
     *
     * DELETE /api/wishlist
     */
    public function clear(Request $request)
    {
        $userId = $request->user()->id;

        Wishlist::where('user_id', $userId)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Wishlist cleared successfully.',
        ]);
    }
}
