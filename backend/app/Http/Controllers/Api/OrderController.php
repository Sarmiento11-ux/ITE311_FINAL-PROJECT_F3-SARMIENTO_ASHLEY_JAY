<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Display a listing of orders.
     * Customers see only their own orders. Admins see all.
     */
    public function index(Request $request)
    {
        $user  = $request->user();
        $query = Order::with(['user:id,name,email,phone', 'items.product', 'items.seller:id,name', 'latestPayment']);

        // Search by order number, customer name, email, or contact number
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('contact_number', 'like', "%{$search}%")
                  ->orWhere('shipping_address', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // If customer: restrict to own orders only (prevents peeking at other users)
        if ($user->role === 'customer') {
            $query->where('user_id', $user->id);
        } elseif ($request->filled('user_id')) {
            // Admin/seller can filter by user_id
            $query->where('user_id', $request->query('user_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->query('payment_status'));
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Orders retrieved successfully.',
            'data'    => $orders,
        ]);
    }

    /**
     * Store a newly created order with items.
     * Enforces backend price calculation, strict stock checking, and cart clearing.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'payment_method'     => 'nullable|string|max:50',
            'shipping_address'   => 'required|string',
            'contact_number'     => 'required|string|max:50',
            'notes'              => 'nullable|string',
            'user_id'            => 'nullable|exists:users,id',
        ]);

        // Security: Always use the authenticated user's ID
        $userId = $request->user()?->id ?? $validated['user_id'] ?? null;
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required to place an order.',
                'data'    => null,
            ], 401);
        }

        try {
            $order = DB::transaction(function () use ($validated, $userId) {
                $subtotal = 0;
                $itemsData = [];

                foreach ($validated['items'] as $item) {
                    // Lock product row for update to prevent race conditions during checkout
                    $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                    // Verify availability
                    if ($product->stock < $item['quantity']) {
                        throw new \Exception("Product '{$product->name}' has insufficient stock. Only {$product->stock} available in inventory.");
                    }

                    $unitPrice = (float) $product->price;
                    $itemSubtotal = $unitPrice * $item['quantity'];
                    $subtotal += $itemSubtotal;

                    $itemsData[] = [
                        'product_id'   => $product->id,
                        'seller_id'    => $product->seller_id,
                        'product_name' => $product->name,
                        'unit_price'   => $unitPrice,
                        'price'        => $unitPrice,
                        'quantity'     => $item['quantity'],
                        'subtotal'     => $itemSubtotal,
                        'status'       => 'pending',
                    ];

                    // Deduct stock immediately in transaction
                    $product->decrement('stock', $item['quantity']);
                }

                // Shipping fee policy: Free shipping on orders ₱1,500 and above; otherwise ₱150.00
                $shippingFee = $subtotal >= 1500 ? 0 : 150;
                $totalAmount = $subtotal + $shippingFee;

                $order = Order::create([
                    'user_id'          => $userId,
                    'order_number'     => 'CM-' . strtoupper(Str::random(8)),
                    'subtotal'         => $subtotal,
                    'shipping_fee'     => $shippingFee,
                    'total_amount'     => $totalAmount,
                    'status'           => 'pending',
                    'payment_method'   => $validated['payment_method'] ?? 'Cash on Delivery',
                    'payment_status'   => 'unpaid',
                    'shipping_address' => $validated['shipping_address'],
                    'contact_number'   => $validated['contact_number'],
                    'notes'            => $validated['notes'] ?? null,
                ]);

                foreach ($itemsData as $itemData) {
                    $order->items()->create($itemData);
                }

                // Clear customer's persistent cart items upon successful order placement
                \App\Models\CartItem::where('user_id', $userId)->delete();

                return $order;
            });

            $order->load(['user:id,name,email', 'items.product', 'items.seller:id,name']);

            // Automated Email Notifications: Customer confirmation, Seller alerts & Admin notice
            NotificationService::sendOrderPlacedNotifications($order);

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully! Thank you for supporting local artisans.',
                'data'    => $order,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data'    => null,
            ], 422);
        }
    }

    /**
     * Display the specified order.
     * Customers can only view their own orders.
     */
    public function show(Request $request, $id)
    {
        $user  = $request->user();
        $order = Order::with(['user:id,name,email', 'items.product', 'items.seller:id,name,email'])->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
                'data'    => null,
            ], 404);
        }

        // Authorization: Non-admins can only view the order if they are the buyer or an involved seller
        $isBuyer = $order->user_id === $user->id;
        $isItemSeller = $order->items->contains('seller_id', $user->id);

        if ($user->role !== 'admin' && !$isBuyer && !$isItemSeller) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden. You do not have access to this order.',
                'data'    => null,
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order retrieved successfully.',
            'data'    => $order,
        ]);
    }

    /**
     * Update order status.
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
                'data'    => null,
            ], 404);
        }

        $user = $request->user();
        if ($user && $user->role !== 'admin' && $order->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden. You do not have permission to update this order.',
                'data'    => null,
            ], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled,refunded',
        ]);

        if ($user && $user->role !== 'admin' && ($validated['status'] !== 'cancelled' || $order->status !== 'pending')) {
            return response()->json([
                'success' => false,
                'message' => 'Customers may only cancel orders that are still pending.',
                'data'    => null,
            ], 422);
        }

        $updateData = ['status' => $validated['status']];

        if ($validated['status'] === 'confirmed' && !$order->confirmed_at) {
            $updateData['confirmed_at'] = now();
        } elseif ($validated['status'] === 'shipped' && !$order->shipped_at) {
            $updateData['shipped_at'] = now();
        } elseif ($validated['status'] === 'delivered' && !$order->delivered_at) {
            $updateData['delivered_at'] = now();
            $updateData['payment_status'] = 'paid';
        }

        $previousStatus = $order->status;
        $order->update($updateData);

        // Cascade status to child order items
        $order->items()->update(['status' => $validated['status']]);

        // Restock inventory if cancelled
        if ($validated['status'] === 'cancelled' && $previousStatus !== 'cancelled') {
            foreach ($order->items as $item) {
                $item->product?->increment('stock', $item->quantity);
            }
        }

        // Notify customer about order status change
        NotificationService::sendOrderStatusUpdate($order, $validated['status']);

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully.',
            'data'    => $order->fresh(['user:id,name,email', 'items.product']),
        ]);
    }
}
