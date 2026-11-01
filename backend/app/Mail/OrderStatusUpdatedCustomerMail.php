<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdatedCustomerMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $newStatus
    ) {}

    public function envelope(): Envelope
    {
        $statusLabel = match($this->newStatus) {
            'confirmed'  => 'Confirmed',
            'processing' => 'Being Prepared',
            'shipped'    => 'Shipped & On The Way',
            'delivered'  => 'Delivered',
            'cancelled'  => 'Cancelled',
            default      => ucfirst($this->newStatus),
        };

        return new Envelope(
            subject: "Order Update: {$this->order->order_number} is now {$statusLabel}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.customer.order_status',
            with: [
                'order'     => $this->order,
                'newStatus' => $this->newStatus,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
