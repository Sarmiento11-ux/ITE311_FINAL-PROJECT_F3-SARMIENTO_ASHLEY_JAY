<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SellerNewOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $seller,
        public Order $order,
        public Collection|array $sellerItems
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "New Craft Order Received! Order {$this->order->order_number}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.seller.new_order',
            with: [
                'seller'      => $this->seller,
                'order'       => $this->order,
                'sellerItems' => $this->sellerItems,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
