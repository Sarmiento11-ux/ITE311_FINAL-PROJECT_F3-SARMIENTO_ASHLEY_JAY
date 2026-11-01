<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminNewOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    public function envelope(): Envelope
    {
        $formattedAmount = number_format($this->order->total_amount, 2);
        return new Envelope(
            subject: "New Marketplace Order: {$this->order->order_number} (₱{$formattedAmount})",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin.new_order',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
