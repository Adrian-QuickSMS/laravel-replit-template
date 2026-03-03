<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribe</title>
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
        .card h1 {
            font-size: 20px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 12px;
        }
        .card p {
            font-size: 14px;
            color: #666;
            margin-bottom: 28px;
            line-height: 1.5;
        }
        .btn {
            display: inline-block;
            background: #dc3545;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 14px 32px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
            width: 100%;
        }
        .btn:hover { background: #c82333; }
        .btn:active { background: #b21f2d; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Unsubscribe</h1>
        <p>Click the button below to unsubscribe from future messages.</p>
        <form method="POST" action="/o/{{ $token }}/confirm">
            @csrf
            <button type="submit" class="btn">Click to unsubscribe</button>
        </form>
    </div>
</body>
</html>
