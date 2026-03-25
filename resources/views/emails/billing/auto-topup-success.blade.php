<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auto Top-Up Successful</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; }
        .header { background: #6f42c1; padding: 24px 32px; }
        .header h1 { color: #fff; margin: 0; font-size: 20px; }
        .content { padding: 32px; }
        .summary-box { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .summary-row { display: flex; justify-content: space-between; padding: 6px 0; }
        .summary-label { color: #6b7280; }
        .summary-value { font-weight: 600; }
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
            <h2>Auto Top-Up Successful</h2>
            <p>Hi {{ $account->company_name }},</p>
            <p>Your account has been automatically topped up. Here are the details:</p>

            <div class="summary-box">
                <table width="100%" cellpadding="4" cellspacing="0">
                    <tr><td style="color:#6b7280">Top-Up Amount</td><td align="right" style="font-weight:600">&pound;{{ number_format($event->topup_amount, 2) }}</td></tr>
                    <tr><td style="color:#6b7280">VAT (20%)</td><td align="right" style="font-weight:600">&pound;{{ number_format($event->vat_amount, 2) }}</td></tr>
                    <tr style="border-top:1px solid #d1d5db"><td style="color:#6b7280;font-weight:600">Total Charged</td><td align="right" style="font-weight:600">&pound;{{ number_format($event->total_charge_amount, 2) }}</td></tr>
                </table>
            </div>

            <p>The credit has been added to your account balance and is available for use immediately.</p>

            <a href="{{ config('app.url') }}/payments/auto-topup" class="btn">View Auto Top-Up Settings</a>
        </div>
        <div class="footer">
            <p>This is an automated notification from QuickSMS. You can manage your notification preferences in your Auto Top-Up settings.</p>
        </div>
    </div>
</body>
</html>
