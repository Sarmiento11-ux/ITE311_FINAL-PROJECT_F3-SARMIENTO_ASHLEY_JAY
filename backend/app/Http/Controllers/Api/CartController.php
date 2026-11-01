<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display a listing of the customer's cart items.
     *
     * GET /api/cart
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $cartItems = CartItem::with(['product.category', 'product.seller:id,name'])
            ->where('user_id', $userId)
            ->get();

        $subtotal = 0;
        $totalItems = 0;

        $items = $cartItems->map(function ($item) use (&$subtotal, &$totalItems) {
            $product = $item->product;
            $unitPrice = (float) ($product ? $product->price : 0);
            $qty = $item->quantity;
            $itemSubtotal = $unitPrice * $qty;

            $subtotal += $itemSubtotal;
            $totalItems += $qty;

            return [
                'id'           => $item->id,
                'product_id'   => $item->product_id,
                'quantity'     => $qty,
                'product_name' => $product ? $product->name : 'Unavailable Product',
                'price'        => $unitPrice,
                'image'        => $product ? $product->image : null,
                'stock'        => $product ? $product->stock : 0,
                'seller_name'  => $product?->seller?->name ?? 'Artisan Maker',
                'category'     => $product?->category?->name ?? 'Handmade',
                'subtotal'     => $itemSubtotal,
                'in_stock'     => $product && $product->stock >= $qty,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Cart retrieved successfully.',
            'data'    => [
                'items'       => $items,
                'subtotal'    => (float) $subtotal,
                'total_items' => $totalItems,
            ],
        ]);
    }

    /**
     * Add a product to the cart.
     *
     * POST /api/cart
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'nullable|integer|min:1',
        ]);

        $userId = $request->user()->id;
        $product = Product::findOrFail($validated['product_id']);
        $quantityToAdd = $validated['quantity'] ?? 1;

        if ($product->stock <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'This product is currently out of stock.',
                'data'    => null,
            ], 422);
        }

        $existing = CartItem::where('user_id', $userId)
            ->where('product_id', $product->id)
            ->first();

        $totalDesired = $quantityToAdd;
        if ($existing) {
            $totalDesired += $existing->quantity;
        }

        if ($totalDesired > $product->stock) {
            return response()->json([
                'success' => false,
                'message' => "Cannot add more. Only {$product->stock} available in stock (you already have {$existing?->quantity} in cart).",
                'data'    => null,
            ], 422);
        }

        if ($existing) {
            $existing->update(['quantity' => $totalDesired]);
            $cartItem = $existing;
        } else {
            $cartItem = CartItem::create([
                'user_id'    => $userId,
                'product_id' => $product->id,
                'quantity'   => $totalDesired,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Added {$product->name} to your cart.",
            'data'    => $cartItem->fresh(['product.category', 'product.seller:id,name']),
        ], 201);
    }

    /**
     * Update the quantity of a cart item.
     *
     * PUT /api/cart/{id}
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $userId = $request->user()->id;
        $cartItem = CartItem::with('product')->where('user_id', $userId)->find($id);

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found or unauthorized.',
                'data'    => null,
            ], 404);
        }

        $product = $cartItem->product;
        if ($validated['quantity'] > $product->stock) {
            return response()->json([
                'success' => false,
                'message' => "Requested quantity exceeds available stock ({$product->stock} available).",
                'data'    => null,
            ], 422);
        }

        $cartItem->update(['quantity' => $validated['quantity']]);

        return response()->json([
            'success' => true,
            'message' => 'Cart item quantity updated.',
            'data'    => $cartItem->fresh('product'),
        ]);
    }

    /**
     * Remove an item from the cart.
     *
     * DELETE /api/cart/{id}
     */
    public function destroy(Request $request, $id)
    {
        $userId = $request->user()->id;
        $cartItem = CartItem::where('user_id', $userId)->find($id);

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found or unauthorized.',
                'data'    => null,
            ], 404);
        }

        $cartItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart.',
            'data'    => null,
        ]);
    }

    /**
     * Clear all items from the customer's cart.
     *
     * DELETE /api/cart
     */
    public function clear(Request $request)
    {
        $userId = $request->user()->id;
        CartItem::where('user_id', $userId)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully.',
            'data'    => null,
        ]);
    }
}
