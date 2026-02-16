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
                            <a class="all-notification" href="{{ route('admin.security.audit-logs') }}">View all activity</a>
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
                            <a href="#" class="dropdown-item ai-icon">
                                <i class="fas fa-history text-info"></i>
                                <span class="ms-2">My Activity</span>
                            </a>
                            <a href="#" class="dropdown-item ai-icon">
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
        fetch('/admin/api/notifications?unread=1')
            .then(function(r) { return r.json(); })
            .then(function(result) {
                if (!result.success) return;
                var items = result.data || [];
                var countEl = document.getElementById('adminNotifCount');
                var dropdownEl = document.getElementById('adminNotifDropdown');
                var markAllBtn = document.getElementById('adminMarkAllRead');

                if (items.length > 0) {
                    countEl.textContent = items.length;
                    countEl.style.display = 'inline-block';
                    if (markAllBtn) markAllBtn.style.display = 'inline-block';

                    var html = '<ul class="timeline">';
                    items.forEach(function(n) {
                        var icon = 'fa-bell';
                        var mediaClass = 'media-info';
                        if (n.type === 'SENDERID_CUSTOMER_RESPONDED') {
                            icon = 'fa-reply';
                            mediaClass = 'media-success';
                        } else if (n.type === 'SENDERID_SUBMITTED') {
                            icon = 'fa-paper-plane';
                            mediaClass = 'media-warning';
                        }
                        html += '<li>';
                        html += '<a href="' + (n.deep_link || '#') + '" class="timeline-panel admin-notif-link" data-uuid="' + n.uuid + '" style="text-decoration: none; color: inherit;">';
                        html += '<div class="media me-2 ' + mediaClass + '"><i class="fas ' + icon + '"></i></div>';
                        html += '<div class="media-body">';
                        html += '<h6 class="mb-1">' + escapeAdminHtml(n.title || n.type) + '</h6>';
                        html += '<small class="d-block">' + escapeAdminHtml(n.message || '') + '</small>';
                        html += '</div>';
                        html += '</a></li>';
                    });
                    html += '</ul>';
                    dropdownEl.innerHTML = html;
                } else {
                    countEl.style.display = 'none';
                    if (markAllBtn) markAllBtn.style.display = 'none';
                    dropdownEl.innerHTML = '<div class="text-muted text-center py-3 small">No new notifications</div>';
                }
            })
            .catch(function() {});
    }

    function escapeAdminHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    document.addEventListener('DOMContentLoaded', function() {
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
                }).then(function() { loadAdminNotifications(); });
            });
        }
    });
})();
</script>
