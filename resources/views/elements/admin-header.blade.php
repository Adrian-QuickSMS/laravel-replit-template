<div class="header admin-header">
    <div class="header-content">
        <nav class="navbar navbar-expand">
            <div class="collapse navbar-collapse justify-content-between">
                <div class="header-left">
                    <div class="dashboard_bar">@yield('title', $page_title ?? 'Admin Dashboard')</div>
                </div>
                <ul class="navbar-nav header-right">
                    <li class="nav-item dropdown notification_dropdown">
                        <a class="nav-link bell-link ai-icon" href="javascript:void(0)" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-bell"></i>
                            <span class="badge badge-circle badge-danger">3</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <div id="DZ_W_Notification1" class="widget-media dlab-scroll p-3" style="max-height: 300px;">
                                <ul class="timeline">
                                    <li>
                                        <div class="timeline-panel">
                                            <div class="media me-2 media-warning">
                                                <i class="fas fa-exclamation-triangle"></i>
                                            </div>
                                            <div class="media-body">
                                                <h6 class="mb-1">Sender ID Pending Approval</h6>
                                                <small class="d-block">3 new requests require review</small>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="timeline-panel">
                                            <div class="media me-2 media-danger">
                                                <i class="fas fa-shield-alt"></i>
                                            </div>
                                            <div class="media-body">
                                                <h6 class="mb-1">Fraud Alert</h6>
                                                <small class="d-block">Unusual activity detected on ACC-1234</small>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="timeline-panel">
                                            <div class="media me-2 media-info">
                                                <i class="fas fa-server"></i>
                                            </div>
                                            <div class="media-body">
                                                <h6 class="mb-1">Supplier Route Issue</h6>
                                                <small class="d-block">Route UK-TIER1 showing delays</small>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <a class="all-notification" href="{{ route('admin.security.audit-logs') }}">See all notifications</a>
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
