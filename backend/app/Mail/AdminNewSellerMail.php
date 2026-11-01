<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminNewSellerMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user)
    {
    }

    public function envelope(): Envelope
    {
        $shopName = $this->user->sellerProfile->shop_name ?? $this->user->name;
        return new Envelope(
            subject: "New Artisan Workshop Registered: {$shopName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin.new_seller',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
