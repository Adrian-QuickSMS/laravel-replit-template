<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>QuickSMS | @yield('title', 'Dashboard')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 280px;
            --topbar-height: 60px;
            --primary-color: #6366f1;
            --primary-hover: #4f46e5;
            --sidebar-bg: #1e293b;
            --sidebar-text: #94a3b8;
            --sidebar-active: #ffffff;
        }
        
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: #f1f5f9;
            margin: 0;
            padding: 0;
        }
        
        .topbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--topbar-height);
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover) 100%);
            display: flex;
            align-items: center;
            padding: 0 1rem;
            z-index: 1030;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .topbar .brand {
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .topbar .brand i {
            font-size: 1.75rem;
        }
        
        .sidebar-toggle {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
            margin-right: 0.5rem;
        }
        
        .sidebar {
            position: fixed;
            top: var(--topbar-height);
            left: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background-color: var(--sidebar-bg);
            overflow-y: auto;
            z-index: 1020;
            transition: transform 0.3s ease;
        }
        
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: var(--topbar-height);
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1015;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--topbar-height);
            padding: 1.5rem;
            min-height: calc(100vh - var(--topbar-height));
        }
        
        .nav-menu {
            list-style: none;
            padding: 0.5rem 0;
            margin: 0;
        }
        
        .nav-item {
            margin: 2px 0;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.25rem;
            color: var(--sidebar-text);
            text-decoration: none;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
        }
        
        .nav-link:hover {
            background-color: rgba(255,255,255,0.05);
            color: var(--sidebar-active);
        }
        
        .nav-link.active {
            background-color: rgba(99, 102, 241, 0.2);
            color: var(--sidebar-active);
            border-left-color: var(--primary-color);
        }
        
        .nav-link i {
            width: 1.5rem;
            margin-right: 0.75rem;
            font-size: 1.1rem;
        }
        
        .nav-link .arrow {
            margin-left: auto;
            transition: transform 0.2s ease;
        }
        
        .nav-item.expanded > .nav-link .arrow {
            transform: rotate(90deg);
        }
        
        .submenu {
            list-style: none;
            padding: 0;
            margin: 0;
            display: none;
            background-color: rgba(0,0,0,0.15);
        }
        
        .nav-item.expanded > .submenu {
            display: block;
        }
        
        .submenu .nav-link {
            padding-left: 3.5rem;
            font-size: 0.9rem;
        }
        
        .page-header {
            margin-bottom: 1.5rem;
        }
        
        .page-title {
            font-size: 1.75rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }
        
        .card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .card-header {
            background-color: transparent;
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 1.25rem;
        }
        
        .placeholder-panel {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border: 2px dashed #cbd5e1;
            border-radius: 0.75rem;
            padding: 3rem;
            text-align: center;
            color: #64748b;
        }
        
        .placeholder-panel i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #94a3b8;
        }
        
        .placeholder-panel h4 {
            color: #475569;
            margin-bottom: 0.5rem;
        }
        
        @media (max-width: 991.98px) {
            .sidebar-toggle {
                display: block;
            }
            
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .sidebar-overlay.show {
                display: block;
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <button class="sidebar-toggle" onclick="toggleSidebar()">
            <i class="bi bi-list"></i>
        </button>
        <a href="{{ route('dashboard') }}" class="brand">
            <i class="bi bi-chat-dots-fill"></i>
            QuickSMS
        </a>
    </header>
    
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>
    
    <nav class="sidebar">
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>
                    Dashboard
                </a>
            </li>
            
            <li class="nav-item {{ request()->routeIs('messages.*') ? 'expanded' : '' }}">
                <a href="#" class="nav-link" onclick="toggleSubmenu(event, this)">
                    <i class="bi bi-envelope"></i>
                    Messages
                    <i class="bi bi-chevron-right arrow"></i>
                </a>
                <ul class="submenu">
                    <li class="nav-item">
                        <a href="{{ route('messages.send') }}" class="nav-link {{ request()->routeIs('messages.send') ? 'active' : '' }}">
                            <i class="bi bi-send"></i>
                            Send Message
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('messages.inbox') }}" class="nav-link {{ request()->routeIs('messages.inbox') ? 'active' : '' }}">
                            <i class="bi bi-inbox"></i>
                            Inbox
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('messages.campaign-history') }}" class="nav-link {{ request()->routeIs('messages.campaign-history') ? 'active' : '' }}">
                            <i class="bi bi-clock-history"></i>
                            Campaign History
                        </a>
                    </li>
                </ul>
            </li>
            
            <li class="nav-item {{ request()->routeIs('contacts.*') ? 'expanded' : '' }}">
                <a href="#" class="nav-link" onclick="toggleSubmenu(event, this)">
                    <i class="bi bi-person-lines-fill"></i>
                    Contact Book
                    <i class="bi bi-chevron-right arrow"></i>
                </a>
                <ul class="submenu">
                    <li class="nav-item">
                        <a href="{{ route('contacts.all') }}" class="nav-link {{ request()->routeIs('contacts.all') ? 'active' : '' }}">
                            <i class="bi bi-people"></i>
                            All Contacts
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('contacts.lists') }}" class="nav-link {{ request()->routeIs('contacts.lists') ? 'active' : '' }}">
                            <i class="bi bi-list-ul"></i>
                            Lists
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('contacts.tags') }}" class="nav-link {{ request()->routeIs('contacts.tags') ? 'active' : '' }}">
                            <i class="bi bi-tags"></i>
                            Tags
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('contacts.opt-out') }}" class="nav-link {{ request()->routeIs('contacts.opt-out') ? 'active' : '' }}">
                            <i class="bi bi-person-x"></i>
                            Opt-Out Lists
                        </a>
                    </li>
                </ul>
            </li>
            
            <li class="nav-item {{ request()->routeIs('reporting.*') ? 'expanded' : '' }}">
                <a href="#" class="nav-link" onclick="toggleSubmenu(event, this)">
                    <i class="bi bi-bar-chart-line"></i>
                    Reporting
                    <i class="bi bi-chevron-right arrow"></i>
                </a>
                <ul class="submenu">
                    <li class="nav-item">
                        <a href="{{ route('reporting.dashboard') }}" class="nav-link {{ request()->routeIs('reporting.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-graph-up"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('reporting.message-log') }}" class="nav-link {{ request()->routeIs('reporting.message-log') ? 'active' : '' }}">
                            <i class="bi bi-journal-text"></i>
                            Message Log
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('reporting.finance-data') }}" class="nav-link {{ request()->routeIs('reporting.finance-data') ? 'active' : '' }}">
                            <i class="bi bi-currency-dollar"></i>
                            Finance Data
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('reporting.invoices') }}" class="nav-link {{ request()->routeIs('reporting.invoices') ? 'active' : '' }}">
                            <i class="bi bi-receipt"></i>
                            Invoices
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('reporting.download-area') }}" class="nav-link {{ request()->routeIs('reporting.download-area') ? 'active' : '' }}">
                            <i class="bi bi-download"></i>
                            Download Area
                        </a>
                    </li>
                </ul>
            </li>
            
            <li class="nav-item">
                <a href="{{ route('purchase') }}" class="nav-link {{ request()->routeIs('purchase') ? 'active' : '' }}">
                    <i class="bi bi-cart3"></i>
                    Purchase
                </a>
            </li>
            
            <li class="nav-item {{ request()->routeIs('management.*') ? 'expanded' : '' }}">
                <a href="#" class="nav-link" onclick="toggleSubmenu(event, this)">
                    <i class="bi bi-gear"></i>
                    Management
                    <i class="bi bi-chevron-right arrow"></i>
                </a>
                <ul class="submenu">
                    <li class="nav-item">
                        <a href="{{ route('management.rcs-agent') }}" class="nav-link {{ request()->routeIs('management.rcs-agent') ? 'active' : '' }}">
                            <i class="bi bi-robot"></i>
                            RCS Agent Registrations
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('management.sms-sender-id') }}" class="nav-link {{ request()->routeIs('management.sms-sender-id') ? 'active' : '' }}">
                            <i class="bi bi-card-text"></i>
                            SMS SenderID Registration
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('management.templates') }}" class="nav-link {{ request()->routeIs('management.templates') ? 'active' : '' }}">
                            <i class="bi bi-file-earmark-text"></i>
                            Templates
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('management.api-connections') }}" class="nav-link {{ request()->routeIs('management.api-connections') ? 'active' : '' }}">
                            <i class="bi bi-plug"></i>
                            API Connections
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('management.email-to-sms') }}" class="nav-link {{ request()->routeIs('management.email-to-sms') ? 'active' : '' }}">
                            <i class="bi bi-envelope-arrow-up"></i>
                            Email-to-SMS
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('management.numbers') }}" class="nav-link {{ request()->routeIs('management.numbers') ? 'active' : '' }}">
                            <i class="bi bi-telephone"></i>
                            Numbers
                        </a>
                    </li>
                </ul>
            </li>
            
            <li class="nav-item {{ request()->routeIs('account.*') ? 'expanded' : '' }}">
                <a href="#" class="nav-link" onclick="toggleSubmenu(event, this)">
                    <i class="bi bi-person-circle"></i>
                    Account
                    <i class="bi bi-chevron-right arrow"></i>
                </a>
                <ul class="submenu">
                    <li class="nav-item">
                        <a href="{{ route('account.details') }}" class="nav-link {{ request()->routeIs('account.details') ? 'active' : '' }}">
                            <i class="bi bi-info-circle"></i>
                            Details
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('account.users') }}" class="nav-link {{ request()->routeIs('account.users') ? 'active' : '' }}">
                            <i class="bi bi-people-fill"></i>
                            Users and Access
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('account.sub-accounts') }}" class="nav-link {{ request()->routeIs('account.sub-accounts') ? 'active' : '' }}">
                            <i class="bi bi-diagram-3"></i>
                            Sub Accounts
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('account.audit-logs') }}" class="nav-link {{ request()->routeIs('account.audit-logs') ? 'active' : '' }}">
                            <i class="bi bi-file-earmark-lock"></i>
                            Audit Logs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('account.security') }}" class="nav-link {{ request()->routeIs('account.security') ? 'active' : '' }}">
                            <i class="bi bi-shield-lock"></i>
                            Security Settings
                        </a>
                    </li>
                </ul>
            </li>
            
            <li class="nav-item {{ request()->routeIs('support.*') ? 'expanded' : '' }}">
                <a href="#" class="nav-link" onclick="toggleSubmenu(event, this)">
                    <i class="bi bi-headset"></i>
                    Support
                    <i class="bi bi-chevron-right arrow"></i>
                </a>
                <ul class="submenu">
                    <li class="nav-item">
                        <a href="{{ route('support.dashboard') }}" class="nav-link {{ request()->routeIs('support.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-house-door"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('support.create-ticket') }}" class="nav-link {{ request()->routeIs('support.create-ticket') ? 'active' : '' }}">
                            <i class="bi bi-ticket-perforated"></i>
                            Create a Ticket
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('support.knowledge-base') }}" class="nav-link {{ request()->routeIs('support.knowledge-base') ? 'active' : '' }}">
                            <i class="bi bi-book"></i>
                            Knowledge Base
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>
    
    <main class="main-content">
        @yield('content')
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('show');
            document.querySelector('.sidebar-overlay').classList.toggle('show');
        }
        
        function toggleSubmenu(event, element) {
            event.preventDefault();
            const navItem = element.closest('.nav-item');
            navItem.classList.toggle('expanded');
        }
    </script>
</body>
</html>
