<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Authentication Required</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; }
        .header { background: #6f42c1; padding: 24px 32px; }
        .header h1 { color: #fff; margin: 0; font-size: 20px; }
        .content { padding: 32px; }
        .alert-box { background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 20px; margin: 20px 0; }
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
            <h2>Payment Authentication Required</h2>
            <p>Hi {{ $account->company_name }},</p>
            <p>Your auto top-up of <strong>&pound;{{ number_format($event->topup_amount, 2) }}</strong> (+ VAT) requires additional authentication from your bank before it can be completed.</p>

            <div class="alert-box">
                <strong>Action needed:</strong> Please complete the authentication to add credit to your account. Your balance will not be updated until this step is completed.
            </div>

            <p>Click the button below to authenticate your payment. This link will expire after 24 hours.</p>

            <a href="{{ $event->requires_action_url }}" class="btn">Complete Payment Authentication</a>
        </div>
        <div class="footer">
            <p>This is an automated notification from QuickSMS. If you did not initiate this payment, please contact our support team.</p>
        </div>
    </div>
</body>
</html>
