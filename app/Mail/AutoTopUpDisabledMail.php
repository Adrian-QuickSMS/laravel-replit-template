<?php

namespace App\Mail;

use App\Models\Account;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AutoTopUpDisabledMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Account $account,
        public string $reason,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Auto Top-Up Has Been Disabled — QuickSMS',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.billing.auto-topup-disabled',
        );
    }
}
