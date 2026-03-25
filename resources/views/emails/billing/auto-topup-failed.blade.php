<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auto Top-Up Failed</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; }
        .header { background: #6f42c1; padding: 24px 32px; }
        .header h1 { color: #fff; margin: 0; font-size: 20px; }
        .content { padding: 32px; }
        .alert-box { background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .btn { display: inline-block; background: #6f42c1; color: #fff; text-decoration: none; padding: 12px 24px; border-radius: 6px; margin-top: 16px; }
        .footer { padding: 24px 32px; background: #f9fafb; color: #6b7280; font-size: 13px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>QuickSMS</h1>
        </div>
        <div class="content">
            <h2>Auto Top-Up Payment Failed</h2>
            <p>Hi {{ $account->company_name }},</p>
            <p>We attempted to automatically top up your account with <strong>&pound;{{ number_format($event->topup_amount, 2) }}</strong>, but the payment was unsuccessful.</p>

            <div class="alert-box">
                <strong>Reason:</strong> {{ $event->failure_message ?? 'Payment declined' }}
            </div>

            <p>Your account balance has not been changed. Please check your payment method and try again, or manually top up your account to avoid service interruption.</p>

            <a href="{{ config('app.url') }}/payments/auto-topup" class="btn">Review Payment Method</a>
        </div>
        <div class="footer">
            <p>This is an automated notification from QuickSMS. You can manage your notification preferences in your Auto Top-Up settings.</p>
        </div>
    </div>
</body>
</html>
