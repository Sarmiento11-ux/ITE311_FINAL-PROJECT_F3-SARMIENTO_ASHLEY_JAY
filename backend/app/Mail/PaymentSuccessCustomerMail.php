<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentSuccessCustomerMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public float $amount,
        public string $paymentMethod,
        public ?string $reference = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Official Payment Receipt: {$this->order->order_number} — Bayad Na!",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.customer.payment_success',
            with: [
                'order'         => $this->order,
                'amount'        => $this->amount,
                'paymentMethod' => $this->paymentMethod,
                'reference'     => $this->reference,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
