<?php

namespace App\Services\Billing;

use App\Models\Billing\AutoTopUpConfig;
use App\Models\Billing\AutoTopUpEvent;
use App\Models\Notification;
use App\Models\AdminNotification;
use App\Mail\AutoTopUpSuccessMail;
use App\Mail\AutoTopUpFailedMail;
use App\Mail\AutoTopUpRequiresActionMail;
use App\Mail\AutoTopUpDisabledMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AutoTopUpNotificationService
{
    public function notifySuccess(AutoTopUpConfig $config, AutoTopUpEvent $event): void
    {
        $account = $config->account;

        if ($config->notify_inapp_success) {
            $this->createInAppNotification(
                $config->account_id,
                'auto_topup_success',
                'info',
                'Auto Top-Up Successful',
                "Your account was automatically topped up with £{$event->topup_amount} (+ VAT). Your balance has been updated.",
                '/payments/auto-topup'
            );
        }

        if ($config->notify_email_success && $account) {
            $this->sendEmail($account->email, new AutoTopUpSuccessMail($event, $account));
        }
    }

    public function notifyFailure(AutoTopUpConfig $config, AutoTopUpEvent $event): void
    {
        $account = $config->account;

        if ($config->notify_inapp_failure) {
            $this->createInAppNotification(
                $config->account_id,
                'auto_topup_failure',
                'warning',
                'Auto Top-Up Failed',
                "An automatic top-up of £{$event->topup_amount} failed: {$event->failure_message}. Please check your payment method.",
                '/payments/auto-topup'
            );
        }

        if ($config->notify_email_failure && $account) {
            $this->sendEmail($account->email, new AutoTopUpFailedMail($event, $account));
        }

        // Admin notification for failures
        if ($account) {
            $this->createAdminNotification(
                'auto_topup_failure',
                'warning',
                "Auto Top-Up Failed: {$account->company_name}",
                "Auto top-up of £{$event->topup_amount} failed for account {$account->company_name}. Failure count: {$config->consecutive_failure_count}.",
                ['account_id' => $config->account_id, 'event_id' => $event->id]
            );
        }
    }

    public function notifyRequiresAction(AutoTopUpConfig $config, AutoTopUpEvent $event): void
    {
        $account = $config->account;

        if ($config->notify_requires_action) {
            $this->createInAppNotification(
                $config->account_id,
                'auto_topup_requires_action',
                'warning',
                'Payment Authentication Required',
                "Your auto top-up of £{$event->topup_amount} requires authentication. Please complete the payment to add credit to your account.",
                $event->requires_action_url,
                'Complete Payment',
                $event->requires_action_url
            );
        }

        // Requires action email is always sent (critical notification) but respects notify_requires_action preference
        if ($config->notify_requires_action && $account) {
            $this->sendEmail($account->email, new AutoTopUpRequiresActionMail($event, $account));
        }
    }

    public function notifyAutoDisabled(AutoTopUpConfig $config): void
    {
        $account = $config->account;

        $this->createInAppNotification(
            $config->account_id,
            'auto_topup_disabled',
            'error',
            'Auto Top-Up Disabled',
            'Auto Top-Up has been automatically disabled after repeated payment failures. Please review your payment method and re-enable when ready.',
            '/payments/auto-topup'
        );

        if ($account) {
            $this->sendEmail($account->email, new AutoTopUpDisabledMail($account, 'consecutive_failures'));

            $this->createAdminNotification(
                'auto_topup_auto_disabled',
                'warning',
                "Auto Top-Up Auto-Disabled: {$account->company_name}",
                "Auto top-up was automatically disabled for {$account->company_name} after {$config->consecutive_failure_count} consecutive failures.",
                ['account_id' => $config->account_id]
            );
        }
    }

    public function notifyAdminDisabled(AutoTopUpConfig $config): void
    {
        $this->createInAppNotification(
            $config->account_id,
            'auto_topup_admin_disabled',
            'error',
            'Auto Top-Up Disabled by Support',
            'Auto Top-Up has been disabled by our support team. Please contact us for more information.',
            '/payments/auto-topup'
        );

        $account = $config->account;
        if ($account) {
            $this->sendEmail($account->email, new AutoTopUpDisabledMail($account, 'admin'));
        }
    }

    public function notifyPaymentMethodRemoved(string $accountId): void
    {
        $this->createInAppNotification(
            $accountId,
            'auto_topup_pm_removed',
            'warning',
            'Payment Method Removed',
            'Your saved payment method has been removed. Auto Top-Up has been disabled. Add a new payment method to re-enable.',
            '/payments/auto-topup'
        );
    }

    private function createInAppNotification(
        string $tenantId,
        string $type,
        string $severity,
        string $title,
        string $body,
        ?string $deepLink = null,
        ?string $actionLabel = null,
        ?string $actionUrl = null,
    ): void {
        try {
            Notification::create([
                'tenant_id' => $tenantId,
                'type' => $type,
                'severity' => $severity,
                'category' => 'billing',
                'title' => $title,
                'body' => $body,
                'deep_link' => $deepLink,
                'action_label' => $actionLabel,
                'action_url' => $actionUrl,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to create in-app notification', ['type' => $type, 'error' => $e->getMessage()]);
        }
    }

    private function createAdminNotification(
        string $type,
        string $severity,
        string $title,
        string $body,
        ?array $meta = null,
    ): void {
        try {
            AdminNotification::create([
                'type' => $type,
                'severity' => $severity,
                'category' => 'billing',
                'title' => $title,
                'body' => $body,
                'meta' => $meta,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to create admin notification', ['type' => $type, 'error' => $e->getMessage()]);
        }
    }

    private function sendEmail(string $to, $mailable): void
    {
        try {
            Mail::to($to)->queue($mailable);
        } catch (\Throwable $e) {
            Log::error('Failed to send auto top-up email', [
                'to' => $to,
                'mailable' => get_class($mailable),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
