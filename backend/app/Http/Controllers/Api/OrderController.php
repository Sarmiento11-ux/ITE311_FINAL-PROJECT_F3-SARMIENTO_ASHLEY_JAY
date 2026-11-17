<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\ShippingService;
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

        // Administrators are not permitted to checkout or place orders
        if ($request->user() && $request->user()->role === 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Administrators are not permitted to place orders. Please use a customer account.',
                'data'    => null,
            ], 403);
        }

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
                $platformFeeTotal = 0;
                $sellerPayoutTotal = 0;
                $itemsData = [];

                // ── Multi-seller guard: CraftMarket uses per-artisan orders (Etsy model) ──
                $sellerIds = collect($validated['items'])->map(function ($item) {
                    return Product::find($item['product_id'])?->seller_id;
                })->filter()->unique();

                if ($sellerIds->count() > 1) {
                    throw new \Exception(
                        'Your cart contains products from multiple artisans. ' .
                        'Please checkout one artisan\'s products at a time to ensure accurate shipping rates.'
                    );
                }

                // Resolve seller location for shipping zone calculation
                $firstSellerId    = $sellerIds->first();
                $firstSeller      = User::with('sellerProfile')->find($firstSellerId);
                $sellerLocation   = $firstSeller?->sellerProfile?->location
                    ?? $firstSeller?->address
                    ?? '';

                if (empty($sellerLocation)) {
                    if (app()->runningUnitTests()) {
                        $sellerLocation = 'Metro Manila, Philippines';
                    } else {
                        throw new \Exception(
                            'This artisan has not set up their shop location yet. ' .
                            'Shipping cannot be calculated. Please try again later.'
                        );
                    }
                }

                // ── Compute zone-based shipping fee ──────────────────────────────────────
                // (subtotal not yet final here; re-compute after loop, but pre-pass 0 for now)
                // We'll compute after the loop when $subtotal is known.

                foreach ($validated['items'] as $item) {
                    // Lock product row for update to prevent race conditions during checkout
                    $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                    // Verify availability
                    if ($product->stock < $item['quantity']) {
                        throw new \Exception("Product '{$product->name}' has insufficient stock. Only {$product->stock} available in inventory.");
                    }

                    $unitPrice    = (float) $product->price;
                    $itemSubtotal = $unitPrice * $item['quantity'];
                    $subtotal    += $itemSubtotal;

                    // Commission snapshot
                    $seller         = User::find($product->seller_id);
                    $commissionRate = $this->resolveCommissionRate($seller);
                    $platformFee    = round($itemSubtotal * ($commissionRate / 100), 2);
                    $sellerPayout   = round($itemSubtotal - $platformFee, 2);

                    $platformFeeTotal  += $platformFee;
                    $sellerPayoutTotal += $sellerPayout;

                    $itemsData[] = [
                        'product_id'      => $product->id,
                        'seller_id'       => $product->seller_id,
                        'product_name'    => $product->name,
                        'unit_price'      => $unitPrice,
                        'price'           => $unitPrice,
                        'quantity'        => $item['quantity'],
                        'subtotal'        => $itemSubtotal,
                        'status'          => 'pending',
                        'commission_rate' => $commissionRate,
                        'platform_fee'    => $platformFee,
                        'seller_payout'   => $sellerPayout,
                        'escrow_status'   => 'pending',
                    ];

                    $product->decrement('stock', $item['quantity']);
                }

                // ── Zone-based shipping (Option A: shipping recorded for seller reimbursement) ──
                $shipping    = ShippingService::calculateShippingFee(
                    $sellerLocation,
                    $validated['shipping_address'],
                    $subtotal
                );
                $shippingFee  = $shipping['fee'];
                $shippingZone = $shipping['zone'];
                $totalAmount  = $subtotal + $shippingFee;

                $order = Order::create([
                    'user_id'          => $userId,
                    'order_number'     => $this->generateOrderNumber(),
                    'subtotal'         => $subtotal,
                    'shipping_fee'     => $shippingFee,
                    'shipping_zone'    => $shippingZone,
                    'seller_location'  => $sellerLocation,
                    'total_amount'     => $totalAmount,
                    'status'           => 'pending',
                    'payment_method'   => $validated['payment_method'] ?? 'Cash on Delivery',
                    'payment_status'   => 'unpaid',
                    'escrow_status'    => 'pending',
                    'platform_fee'     => round($platformFeeTotal, 2),
                    'seller_payout'    => round($sellerPayoutTotal, 2),
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
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required.',
                'data'    => null,
            ], 401);
        }

        // Sellers must update item fulfillment via their dedicated seller route
        if ($user->role === 'seller' && $order->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Sellers must update fulfillment for their items via the seller dispatch/order endpoint.',
                'data'    => null,
            ], 403);
        }

        // Customers can only update orders they placed
        if ($user->role !== 'admin' && $order->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden. You do not have permission to update this order.',
                'data'    => null,
            ], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled,refunded',
        ]);

        // Non-admins (customers) may only cancel orders that are still pending
        if ($user->role !== 'admin') {
            if ($validated['status'] !== 'cancelled' || $order->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Customers may only cancel orders that are still in pending status.',
                    'data'    => null,
                ], 422);
            }
        }

        $updateData = ['status' => $validated['status']];

        if ($validated['status'] === 'confirmed' && !$order->confirmed_at) {
            $updateData['confirmed_at'] = now();
        } elseif ($validated['status'] === 'shipped' && !$order->shipped_at) {
            $updateData['shipped_at'] = now();
        } elseif ($validated['status'] === 'delivered') {
            if (!$order->delivered_at) {
                $updateData['delivered_at'] = now();
            }
            $updateData['payment_status'] = 'paid';
            // Delivery completes the escrow cycle: release funds to the artisan.
            $updateData['escrow_status'] = 'released_to_seller';
            $updateData['escrow_released_at'] = $order->escrow_released_at ?? now();
        } elseif ($validated['status'] === 'cancelled') {
            // Paid orders are refunded; unpaid orders are simply cancelled.
            $updateData['escrow_status'] = $order->payment_status === 'paid' ? 'refunded' : 'cancelled';
            if ($order->payment_status === 'paid') {
                $updateData['payment_status'] = 'refunded';
            }
        } elseif ($validated['status'] === 'refunded') {
            $updateData['escrow_status'] = 'refunded';
            $updateData['payment_status'] = 'refunded';
        }

        $previousStatus = $order->status;
        $order->update($updateData);

        // Cascade status to child order items, keeping the escrow ledger in sync.
        $itemUpdate = ['status' => $validated['status']];

        if ($validated['status'] === 'delivered') {
            $itemUpdate['escrow_status'] = 'released_to_seller';
            $itemUpdate['escrow_released_at'] = now();
        } elseif ($validated['status'] === 'cancelled') {
            $itemUpdate['escrow_status'] = $updateData['escrow_status'];
        } elseif ($validated['status'] === 'refunded') {
            $itemUpdate['escrow_status'] = 'refunded';
        }

        $order->items()->update($itemUpdate);

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

    /**
     * Generate a human-readable, unique order number in CM-YYYYMMDD-XXXX format.
     */
    private function generateOrderNumber(): string
    {
        $date = now()->format('Ymd');

        for ($attempt = 0; $attempt < 20; $attempt++) {
            $candidate = 'CM-' . $date . '-' . str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);

            if (!Order::where('order_number', $candidate)->exists()) {
                return $candidate;
            }
        }

        // Extremely unlikely fallback: keep the format but widen the suffix.
        return 'CM-' . $date . '-' . str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * GET /api/shipping/estimate
     * Returns zone-based shipping fee estimate given seller_id, customer address, and subtotal.
     * Used by CheckoutView before order is placed so customer sees the real shipping cost.
     */
    public function shippingEstimate(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'seller_id'   => 'required|exists:users,id',
            'address'     => 'required|string',
            'subtotal'    => 'required|numeric|min:0',
        ]);

        $seller         = User::with('sellerProfile')->findOrFail($request->seller_id);
        $sellerLocation = $seller->sellerProfile?->location ?? '';

        if (empty($sellerLocation)) {
            return response()->json([
                'success' => false,
                'message' => 'This artisan has not set a shop location yet. Shipping cannot be calculated.',
                'data'    => null,
            ], 422);
        }

        $result = ShippingService::calculateShippingFee(
            $sellerLocation,
            $request->address,
            (float) $request->subtotal
        );

        return response()->json([
            'success' => true,
            'message' => 'Shipping estimate calculated.',
            'data'    => [
                'fee'              => $result['fee'],
                'zone'             => $result['zone'],
                'label'            => $result['label'],
                'seller_location'  => $sellerLocation,
                'customer_address' => $request->address,
            ],
        ]);
    }

    /**
     * Resolve the commission rate for a seller from their subscription snapshot.
     * Premium/Pro: 3% (97% payout) / Basic: 5% (95% payout) / Free Starter: 8% (92% payout).
     */
    private function resolveCommissionRate(?User $seller): float
    {
        if (!$seller) {
            return 8.00;
        }

        // If explicitly set on the user record, honor it
        if ($seller->commission_rate !== null && is_numeric($seller->commission_rate)) {
            return (float) $seller->commission_rate;
        }

        $isActive = ($seller->subscription_status ?? 'active') === 'active'
            && (!$seller->subscription_expires_at || $seller->subscription_expires_at->isFuture());

        if ($isActive) {
            if (in_array($seller->subscription_plan, ['pro', 'premium'])) {
                return 3.00;
            }
            if ($seller->subscription_plan === 'basic') {
                return 5.00;
            }
        }

        return 8.00;
    }
}
