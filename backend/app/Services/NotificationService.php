<?php

namespace App\Services;

use App\Mail\AdminNewOrderMail;
use App\Mail\AdminNewSellerMail;
use App\Mail\CustomerWelcomeMail;
use App\Mail\OrderPlacedCustomerMail;
use App\Mail\OrderStatusUpdatedCustomerMail;
use App\Mail\PaymentSuccessCustomerMail;
use App\Mail\SellerItemFulfillmentMail;
use App\Mail\SellerNewOrderMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * In-memory deduplication cache for the current request lifecycle.
     */
    private static array $sentCache = [];

    /**
     * Get all admin emails configured or present in the database.
     */
    public static function getAdminEmails(): array
    {
        $emails = User::where('role', 'admin')->where('is_active', true)->pluck('email')->toArray();
        $fallback = config('mail.admin_email', env('ADMIN_NOTIFICATION_EMAIL', 'admin@craftmarket.ph'));

        if ($fallback && !in_array($fallback, $emails)) {
            $emails[] = $fallback;
        }

        return array_unique(array_filter($emails));
    }

    /**
     * Send welcome email to newly registered customer or seller.
     */
    public static function sendCustomerWelcome(User $user): void
    {
        $key = "welcome_{$user->id}";
        if (isset(self::$sentCache[$key])) return;
        self::$sentCache[$key] = true;

        try {
            Mail::to($user->email, $user->name)->send(new CustomerWelcomeMail($user));
            Log::info("[NotificationService] Welcome email sent to {$user->email} ({$user->role})");
        } catch (\Throwable $e) {
            Log::error("[NotificationService] Failed to send welcome email to {$user->email}: " . $e->getMessage());
        }
    }

    /**
     * Send new seller registration alert to marketplace admins.
     */
    public static function sendAdminNewSeller(User $seller): void
    {
        $key = "admin_seller_{$seller->id}";
        if (isset(self::$sentCache[$key])) return;
        self::$sentCache[$key] = true;

        try {
            $adminEmails = self::getAdminEmails();
            if (!empty($adminEmails)) {
                Mail::to($adminEmails)->send(new AdminNewSellerMail($seller));
                Log::info("[NotificationService] New seller alert sent to admins for {$seller->name}");
            }
        } catch (\Throwable $e) {
            Log::error("[NotificationService] Failed to send new seller alert to admins: " . $e->getMessage());
        }
    }

    /**
     * Comprehensive order creation notifications:
     * 1. Customer: Order confirmation email
     * 2. Sellers: Individual notifications with ONLY their specific products
     * 3. Admins: Marketplace order notification
     *
     * Wrapped in try/catch to ensure order creation NEVER fails due to email issues.
     */
    public static function sendOrderPlacedNotifications(Order $order): void
    {
        $key = "order_placed_{$order->id}";
        if (isset(self::$sentCache[$key])) return;
        self::$sentCache[$key] = true;

        // Ensure relations are loaded
        $order->loadMissing(['user', 'items.product', 'items.seller.sellerProfile']);

        // 1. Send confirmation to the buyer/customer
        try {
            if ($order->user && $order->user->email) {
                Mail::to($order->user->email, $order->user->name)->send(new OrderPlacedCustomerMail($order));
                Log::info("[NotificationService] Order confirmation sent to buyer {$order->user->email} for {$order->order_number}");
            }
        } catch (\Throwable $e) {
            Log::error("[NotificationService] Failed to send buyer order confirmation: " . $e->getMessage());
        }

        // 2. Send seller notifications — group items by seller_id
        try {
            $itemsBySeller = $order->items->groupBy('seller_id');

            foreach ($itemsBySeller as $sellerId => $sellerItems) {
                $seller = $sellerItems->first()?->seller;

                // If relationship wasn't loaded directly, fetch seller
                if (!$seller && $sellerId) {
                    $seller = User::with('sellerProfile')->find($sellerId);
                }

                if ($seller && $seller->email) {
                    Mail::to($seller->email, $seller->name)->send(new SellerNewOrderMail($seller, $order, $sellerItems));
                    Log::info("[NotificationService] Seller order alert sent to {$seller->email} for {$order->order_number} ({$sellerItems->count()} items)");
                }
            }
        } catch (\Throwable $e) {
            Log::error("[NotificationService] Failed to send seller order notifications: " . $e->getMessage());
        }

        // 3. Send admin order notification
        try {
            $adminEmails = self::getAdminEmails();
            if (!empty($adminEmails)) {
                Mail::to($adminEmails)->send(new AdminNewOrderMail($order));
                Log::info("[NotificationService] Admin order notification sent for {$order->order_number}");
            }
        } catch (\Throwable $e) {
            Log::error("[NotificationService] Failed to send admin order notification: " . $e->getMessage());
        }
    }

    /**
     * Send order status update email to the customer.
     */
    public static function sendOrderStatusUpdate(Order $order, string $newStatus): void
    {
        $key = "order_status_{$order->id}_{$newStatus}";
        if (isset(self::$sentCache[$key])) return;
        self::$sentCache[$key] = true;

        $order->loadMissing('user');

        try {
            if ($order->user && $order->user->email) {
                Mail::to($order->user->email, $order->user->name)->send(new OrderStatusUpdatedCustomerMail($order, $newStatus));
                Log::info("[NotificationService] Order status update ({$newStatus}) sent to {$order->user->email} for {$order->order_number}");
            }
        } catch (\Throwable $e) {
            Log::error("[NotificationService] Failed to send order status update email: " . $e->getMessage());
        }
    }

    /**
     * Send payment success email receipt to the customer.
     * Deduplicated by order and payment reference.
     */
    public static function sendPaymentSuccess(Order $order, float $amount, string $paymentMethod, ?string $reference = null): void
    {
        $key = "payment_success_{$order->id}_" . ($reference ?? 'ref');
        if (isset(self::$sentCache[$key])) return;
        self::$sentCache[$key] = true;

        $order->loadMissing('user');

        try {
            if ($order->user && $order->user->email) {
                Mail::to($order->user->email, $order->user->name)->send(new PaymentSuccessCustomerMail($order, $amount, $paymentMethod, $reference));
                Log::info("[NotificationService] Payment receipt sent to {$order->user->email} for {$order->order_number} (₱{$amount})");
            }
        } catch (\Throwable $e) {
            Log::error("[NotificationService] Failed to send payment receipt: " . $e->getMessage());
        }
    }

    /**
     * Send item fulfillment update notification.
     */
    public static function sendSellerItemFulfillment(OrderItem $item): void
    {
        $key = "item_fulfillment_{$item->id}_{$item->status}";
        if (isset(self::$sentCache[$key])) return;
        self::$sentCache[$key] = true;

        $item->loadMissing(['order.user', 'seller']);

        try {
            if ($item->seller && $item->seller->email && $item->order) {
                Mail::to($item->seller->email, $item->seller->name)->send(new SellerItemFulfillmentMail($item, $item->order));
                Log::info("[NotificationService] Item fulfillment update sent to seller {$item->seller->email}");
            }
        } catch (\Throwable $e) {
            Log::error("[NotificationService] Failed to send item fulfillment update: " . $e->getMessage());
        }
    }
}
