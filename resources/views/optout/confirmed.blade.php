<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribed</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            padding: 40px 32px;
            max-width: 400px;
            width: 100%;
            text-align: center;
        }
        .checkmark {
            width: 56px; height: 56px;
            background: #28a745;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
        }
        .checkmark svg { width: 28px; height: 28px; fill: none; stroke: #fff; stroke-width: 3; stroke-linecap: round; stroke-linejoin: round; }
        .card h1 { font-size: 20px; font-weight: 600; color: #1a1a1a; margin-bottom: 8px; }
        .card p { font-size: 14px; color: #666; line-height: 1.5; }
    </style>
</head>
<body>
    <div class="card">
        <div class="checkmark">
            <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
        </div>
        <h1>Unsubscribed</h1>
        <p>You have been successfully unsubscribed and will no longer receive messages.</p>
    </div>
</body>
</html>
