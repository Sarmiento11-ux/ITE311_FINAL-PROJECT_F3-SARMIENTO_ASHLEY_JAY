<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SellerVerificationUpdateMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $seller,
        public string $status,
        public ?string $notes = null,
    ) {
    }

    public function envelope(): Envelope
    {
        $shopName = $this->seller->sellerProfile->shop_name ?? $this->seller->name;

        $subject = match ($this->status) {
            'approved' => "Your CraftMarket Storefront is Approved: {$shopName}",
            'rejected' => "Update on Your CraftMarket Storefront Application: {$shopName}",
            default    => "Your CraftMarket Storefront is Back Under Review: {$shopName}",
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.seller.verification_update',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
