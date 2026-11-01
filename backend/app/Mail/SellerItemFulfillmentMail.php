<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SellerItemFulfillmentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public OrderItem $item,
        public Order $order
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Fulfillment Update: {$this->item->product_name} in {$this->order->order_number} is now " . ucfirst($this->item->status),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.seller.fulfillment_update',
            with: [
                'item'  => $this->item,
                'order' => $this->order,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
