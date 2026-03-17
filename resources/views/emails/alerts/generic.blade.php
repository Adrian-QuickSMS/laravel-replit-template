<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 0; padding: 0; background-color: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .card { background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header { padding: 24px 24px 16px; border-bottom: 1px solid #eee; }
        .severity-badge { display: inline-block; padding: 4px 12px; border-radius: 4px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .severity-critical { background: #fde8e8; color: #dc3545; }
        .severity-warning { background: #fff8e1; color: #f59e0b; }
        .severity-info { background: #e8f4fd; color: #0078d7; }
        .body { padding: 24px; }
        .title { margin: 12px 0 8px; font-size: 20px; font-weight: 600; color: #1a1a1a; }
        .description { color: #555; font-size: 14px; line-height: 1.6; }
        .meta-table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        .meta-table td { padding: 8px 0; font-size: 13px; border-bottom: 1px solid #f0f0f0; }
        .meta-table td:first-child { color: #888; width: 120px; }
        .meta-table td:last-child { color: #333; }
        .footer { padding: 16px 24px; background: #fafafa; border-top: 1px solid #eee; text-align: center; }
        .footer p { margin: 0; font-size: 12px; color: #999; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <span class="severity-badge severity-{{ $severity }}">{{ strtoupper($severity) }}</span>
                <h1 class="title">{{ $title }}</h1>
            </div>
            <div class="body">
                <p class="description">{{ $body }}</p>

                <table class="meta-table">
                    <tr>
                        <td>Category</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $category)) }}</td>
                    </tr>
                    <tr>
                        <td>Alert Type</td>
                        <td>{{ $triggerKey }}</td>
                    </tr>
                    @if($triggerValue)
                    <tr>
                        <td>Value</td>
                        <td>{{ $triggerValue }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td>Time</td>
                        <td>{{ $timestamp }}</td>
                    </tr>
                </table>
            </div>
            <div class="footer">
                <p>You're receiving this because of your alert settings on QuickSMS.</p>
                <p>Manage your notification preferences in your account settings.</p>
            </div>
        </div>
    </div>
</body>
</html>
