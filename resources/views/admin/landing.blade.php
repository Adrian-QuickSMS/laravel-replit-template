@php
    $adminSession = session('admin_auth', []);
    $adminName = $adminSession['name'] ?? 'Admin User';
    $adminRole = $adminSession['role'] ?? 'super_admin';
    $hrRole = $adminSession['hr_role'] ?? 'none';
    $hasHrAccess = in_array($hrRole, ['employee', 'manager', 'hr_admin']) || $adminRole === 'super_admin';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>QuickSMS Admin</title>
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: #0f2137;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .landing-header {
            background: #1e3a5f;
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .landing-header img { height: 32px; }
        .landing-header .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: rgba(255,255,255,0.7);
            font-size: 0.85rem;
        }
        .landing-header .user-info .name { color: #fff; font-weight: 500; }
        .landing-header .logout-btn {
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            font-size: 0.8rem;
            padding: 0.4rem 0.8rem;
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 6px;
            transition: all 0.2s;
        }
        .landing-header .logout-btn:hover {
            color: #fff;
            border-color: rgba(255,255,255,0.5);
        }
        .landing-main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .landing-content { text-align: center; max-width: 800px; }
        .landing-content h1 {
            color: #fff;
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .landing-content p {
            color: rgba(255,255,255,0.5);
            font-size: 0.95rem;
            margin-bottom: 2.5rem;
        }
        .area-cards {
            display: flex;
            gap: 2rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .area-card {
            background: #1e3a5f;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px;
            padding: 3rem 2.5rem;
            width: 300px;
            text-decoration: none;
            transition: all 0.3s;
            cursor: pointer;
            display: block;
        }
        .area-card:hover {
            transform: translateY(-4px);
            border-color: rgba(255,255,255,0.3);
            box-shadow: 0 12px 40px rgba(0,0,0,0.3);
        }
        .area-card .icon-wrap {
            width: 72px;
            height: 72px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 1.8rem;
        }
        .area-card.messaging .icon-wrap {
            background: rgba(59,130,246,0.15);
            color: #60a5fa;
        }
        .area-card.hr .icon-wrap {
            background: rgba(34,197,94,0.15);
            color: #4ade80;
        }
        .area-card h2 {
            color: #fff;
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .area-card p {
            color: rgba(255,255,255,0.5);
            font-size: 0.85rem;
            margin: 0;
            line-height: 1.5;
        }
        .area-card .arrow {
            margin-top: 1.5rem;
            color: rgba(255,255,255,0.3);
            font-size: 1.2rem;
            transition: all 0.3s;
        }
        .area-card:hover .arrow {
            color: rgba(255,255,255,0.7);
            transform: translateX(4px);
        }
    </style>
</head>
<body>
    <div class="landing-header">
        <img src="{{ asset('images/quicksms-logo-white.png') }}" alt="QuickSMS">
        <div class="user-info">
            <span>Welcome, <span class="name">{{ $adminName }}</span></span>
            <a href="{{ route('admin.logout') }}" class="logout-btn" onclick="return confirm('Are you sure you want to log out?');">
                <i class="fas fa-sign-out-alt me-1"></i> Logout
            </a>
        </div>
    </div>

    <div class="landing-main">
        <div class="landing-content">
            <h1>Admin Console</h1>
            <p>Select an area to get started</p>
            <div class="area-cards">
                <a href="{{ route('admin.messaging.dashboard') }}" class="area-card messaging">
                    <div class="icon-wrap">
                        <i class="fas fa-satellite-dish"></i>
                    </div>
                    <h2>Messaging Platform</h2>
                    <p>Accounts, campaigns, routing, suppliers, reporting and system management</p>
                    <div class="arrow"><i class="fas fa-arrow-right"></i></div>
                </a>

                @if($hasHrAccess)
                <a href="{{ route('admin.hr.dashboard') }}" class="area-card hr">
                    <div class="icon-wrap">
                        <i class="fas fa-users"></i>
                    </div>
                    <h2>HR Management</h2>
                    <p>Leave requests, team calendar, Bradford Factor scores and HR settings</p>
                    <div class="arrow"><i class="fas fa-arrow-right"></i></div>
                </a>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
