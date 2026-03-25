<?php

namespace App\Mail;

use App\Models\Account;
use App\Models\Billing\AutoTopUpEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AutoTopUpRequiresActionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public AutoTopUpEvent $event,
        public Account $account,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Action Required: Complete Your Auto Top-Up Payment — QuickSMS',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.billing.auto-topup-requires-action',
        );
    }
}
