<div class="header admin-header">
    <div class="header-content">
        <nav class="navbar navbar-expand">
            <div class="collapse navbar-collapse justify-content-between">
                <div class="header-left">
                    <div class="dashboard_bar">@yield('title', $page_title ?? 'Admin Dashboard')</div>
                </div>
                <ul class="navbar-nav header-right">
                    <li class="nav-item dropdown notification_dropdown">
                        <a class="nav-link bell-link ai-icon" href="javascript:void(0)" role="button" data-bs-toggle="dropdown" id="adminNotificationBell">
                            <i class="fas fa-bell"></i>
                            <span class="badge badge-circle badge-danger" id="adminNotifCount" style="display: none;">0</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end" style="min-width: 350px;">
                            <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Notifications</h6>
                                <button class="btn btn-link btn-sm p-0 text-muted" id="adminMarkAllRead" style="display: none;">Mark all read</button>
                            </div>
                            <div id="adminNotifDropdown" class="widget-media dlab-scroll p-3" style="max-height: 350px; overflow-y: auto;">
                                <div class="text-muted text-center py-3 small">No new notifications</div>
                            </div>
                            <a class="all-notification" href="{{ route('admin.management.notification-centre') }}">View all notifications</a>
                        </div>
                    </li>
                    
                    <li class="nav-item dropdown notification_dropdown">
                        <a class="nav-link" href="javascript:void(0)" data-bs-toggle="dropdown">
                            <i class="fas fa-search"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end p-3" style="min-width: 300px;">
                            <form>
                                <div class="input-group search-area">
                                    <input type="text" class="form-control" placeholder="Search accounts, messages...">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                            </form>
                        </div>
                    </li>
                    
                    <li class="nav-item dropdown header-profile">
                        <a class="nav-link" href="javascript:void(0)" role="button" data-bs-toggle="dropdown">
                            <div class="header-info2 d-flex align-items-center">
                                <div class="header-media admin-avatar">
                                    <i class="fas fa-user-shield"></i>
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a href="#" class="dropdown-item ai-icon">
                                <i class="fas fa-user-cog text-primary"></i>
                                <span class="ms-2">Admin Settings</span>
                            </a>
                            <a href="{{ route('admin.security.audit-logs') }}" class="dropdown-item ai-icon">
                                <i class="fas fa-history text-info"></i>
                                <span class="ms-2">My Activity</span>
                            </a>
                            <a href="{{ route('admin.logout') }}" class="dropdown-item ai-icon" onclick="return confirm('Are you sure you want to log out?');">
                                <i class="fas fa-sign-out-alt text-danger"></i>
                                <span class="ms-2">Logout</span>
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</div>

<script>
(function() {
    function loadAdminNotifications() {
        fetch('/admin/api/notifications/?per_page=5&unread_only=1')
            .then(function(r) {
                if (!r.ok) throw new Error('[NotificationCentre] Admin bell fetch failed: ' + r.status);
                return r.json();
            })
            .then(function(result) {
                if (!result.success) return;
                var items = result.data || [];
                var unreadCount = result.unread_count || 0;
                var countEl = document.getElementById('adminNotifCount');
                var dropdownEl = document.getElementById('adminNotifDropdown');
                var markAllBtn = document.getElementById('adminMarkAllRead');

                if (unreadCount > 0) {
                    countEl.textContent = unreadCount > 99 ? '99+' : unreadCount;
                    countEl.style.display = 'inline-block';
                    if (markAllBtn) markAllBtn.style.display = 'inline-block';
                } else {
                    countEl.style.display = 'none';
                    if (markAllBtn) markAllBtn.style.display = 'none';
                }

                if (items.length > 0) {
                    var html = '<ul class="timeline">';
                    items.slice(0, 5).forEach(function(n) {
                        var icon = 'fa-bell';
                        var mediaClass = 'media-info';
                        var severity = n.severity || 'info';
                        if (severity === 'critical') { icon = 'fa-exclamation-circle'; mediaClass = 'media-danger'; }
                        else if (severity === 'warning') { icon = 'fa-exclamation-triangle'; mediaClass = 'media-warning'; }
                        var isUnread = !n.read_at;
                        html += '<li>';
                        var safeLink = sanitizeAdminUrl(n.deep_link);
                        html += '<a href="' + escapeAdminHtml(safeLink) + '" class="timeline-panel admin-notif-link" data-uuid="' + escapeAdminHtml(n.uuid || '') + '" style="text-decoration: none; color: inherit;' + (isUnread ? ' font-weight: 600;' : '') + '">';
                        html += '<div class="media me-2 ' + mediaClass + '"><i class="fas ' + icon + '"></i></div>';
                        html += '<div class="media-body">';
                        html += '<h6 class="mb-1" style="font-size: 0.85rem;">' + escapeAdminHtml(n.title || n.type) + '</h6>';
                        html += '<small class="d-block text-muted">' + escapeAdminHtml(n.body || '') + '</small>';
                        html += '<small class="d-block text-muted" style="font-size: 0.7rem; margin-top: 0.2rem;">' + formatTimeAgo(n.created_at) + '</small>';
                        html += '</div>';
                        html += '</a></li>';
                    });
                    html += '</ul>';
                    dropdownEl.innerHTML = html;
                } else {
                    dropdownEl.innerHTML = '<div class="text-muted text-center py-3 small">No new notifications</div>';
                }
            })
            .catch(function(err) { console.warn(err.message || err); });
    }

    function sanitizeAdminUrl(url) {
        if (!url || typeof url !== 'string') return '#';
        var trimmed = url.trim();
        if (/^https?:\/\//i.test(trimmed) || trimmed.startsWith('/')) return trimmed;
        return '#';
    }

    function escapeAdminHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    function formatTimeAgo(dateStr) {
        if (!dateStr) return '';
        var d = new Date(dateStr);
        var now = new Date();
        var diffMs = now - d;
        var diffMin = Math.floor(diffMs / 60000);
        if (diffMin < 1) return 'Just now';
        if (diffMin < 60) return diffMin + 'm ago';
        var diffHr = Math.floor(diffMin / 60);
        if (diffHr < 24) return diffHr + 'h ago';
        var diffDay = Math.floor(diffHr / 24);
        return diffDay + 'd ago';
    }

    document.addEventListener('DOMContentLoaded', function() {
        console.log('[NotificationCentre] Admin bell initialized');
        loadAdminNotifications();
        setInterval(loadAdminNotifications, 60000);

        var markAllBtn = document.getElementById('adminMarkAllRead');
        if (markAllBtn) {
            markAllBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var csrfToken = document.querySelector('meta[name="csrf-token"]');
                fetch('/admin/api/notifications/mark-all-read', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken ? csrfToken.content : '',
                        'Content-Type': 'application/json'
                    }
                }).then(function(r) {
                    if (!r.ok) throw new Error('[NotificationCentre] Mark all read failed');
                    loadAdminNotifications();
                }).catch(function(err) { console.warn(err.message || err); });
            });
        }
    });
})();
</script>
