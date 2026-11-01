<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user)
    {
    }

    public function envelope(): Envelope
    {
        $roleTitle = $this->user->role === 'seller' ? 'Artisan Maker' : 'Shopper';
        return new Envelope(
            subject: "Welcome to CraftMarket Philippines! Your Handcrafted Journey Begins",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.customer.welcome',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
