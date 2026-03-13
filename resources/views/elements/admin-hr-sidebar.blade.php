<div class="dlabnav admin-sidebar">
    <div class="dlabnav-scroll">
        <ul class="metismenu" id="menu">
            <li>
                <a href="{{ route('admin.landing') }}" aria-expanded="false">
                    <i class="fas fa-th-large"></i>
                    <span class="nav-text">Admin Home</span>
                </a>
            </li>
            
            <li class="nav-label">HR Management</li>
            
            <li class="{{ request()->routeIs('admin.hr.dashboard') ? 'mm-active' : '' }}">
                <a href="{{ route('admin.hr.dashboard') }}" aria-expanded="false">
                    <i class="fas fa-tachometer-alt"></i>
                    <span class="nav-text">Dashboard
                        @php
                            $hrRole = session('admin_auth.hr_role', 'none');
                            $hrAdminId = session('admin_auth.admin_id');
                            if (in_array($hrRole, ['hr_admin']) || session('admin_auth.role') === 'super_admin') {
                                $hrPendingCount = \App\Models\Hr\LeaveRequest::where('status', 'pending')->count();
                            } elseif ($hrRole === 'manager' && $hrAdminId) {
                                $mgrProfile = \App\Models\Hr\EmployeeHrProfile::where('admin_user_id', $hrAdminId)->first();
                                $hrPendingCount = $mgrProfile ? \App\Models\Hr\LeaveRequest::where('status', 'pending')->whereIn('employee_id', $mgrProfile->directReports()->pluck('id'))->count() : 0;
                            } else {
                                $hrPendingCount = 0;
                            }
                        @endphp
                        @if($hrPendingCount > 0)<span class="badge bg-warning" style="font-size: 0.6rem; width: 18px; height: 18px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; margin-left: 0.25rem; padding: 0;">{{ $hrPendingCount }}</span>@endif
                    </span>
                </a>
            </li>
            
            <li class="{{ request()->routeIs('admin.hr.my-leave') ? 'mm-active' : '' }}">
                <a href="{{ route('admin.hr.my-leave') }}" aria-expanded="false">
                    <i class="fas fa-calendar-check"></i>
                    <span class="nav-text">My Leave</span>
                </a>
            </li>
            
            <li class="{{ request()->routeIs('admin.hr.team-calendar') ? 'mm-active' : '' }}">
                <a href="{{ route('admin.hr.team-calendar') }}" aria-expanded="false">
                    <i class="fas fa-calendar-alt"></i>
                    <span class="nav-text">Team Calendar</span>
                </a>
            </li>
            
            @if(in_array(session('admin_auth.hr_role', 'none'), ['hr_admin']) || session('admin_auth.role') === 'super_admin')
            <li class="{{ request()->routeIs('admin.hr.settings') ? 'mm-active' : '' }}">
                <a href="{{ route('admin.hr.settings') }}" aria-expanded="false">
                    <i class="fas fa-cog"></i>
                    <span class="nav-text">Settings</span>
                </a>
            </li>
            @endif
        </ul>
        
        <div class="copyright admin-copyright">
            <p><strong>QuickSMS HR</strong></p>
            <p class="fs-12">Internal Use Only</p>
            <p class="fs-12">&copy; {{ date('Y') }}</p>
        </div>
    </div>
</div>
