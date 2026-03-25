<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auto Top-Up Disabled</title>
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
            <h2>Auto Top-Up Has Been Disabled</h2>
            <p>Hi {{ $account->company_name }},</p>

            @if($reason === 'consecutive_failures')
                <div class="alert-box">
                    <strong>Reason:</strong> Auto Top-Up has been automatically disabled after repeated payment failures.
                </div>
                <p>Please review your payment method to ensure it is valid and has sufficient funds. Once resolved, you can re-enable Auto Top-Up from your payment settings.</p>
            @elseif($reason === 'admin')
                <div class="alert-box">
                    <strong>Reason:</strong> Auto Top-Up has been disabled by our support team.
                </div>
                <p>If you believe this was done in error or need further assistance, please contact our support team.</p>
            @else
                <div class="alert-box">
                    <strong>Auto Top-Up has been disabled for your account.</strong>
                </div>
            @endif

            <a href="{{ config('app.url') }}/payments/auto-topup" class="btn">View Auto Top-Up Settings</a>
        </div>
        <div class="footer">
            <p>This is an automated notification from QuickSMS.</p>
        </div>
    </div>
</body>
</html>
