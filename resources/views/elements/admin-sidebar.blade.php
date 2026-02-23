<div class="dlabnav admin-sidebar">
    <div class="dlabnav-scroll">
        <ul class="metismenu" id="menu">
            <li class="{{ request()->routeIs('admin.dashboard') ? 'mm-active' : '' }}">
                <a href="{{ route('admin.dashboard') }}" aria-expanded="false">
                    <i class="fas fa-tachometer-alt"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            
            <li class="{{ request()->routeIs('admin.accounts.*') ? 'mm-active' : '' }}">
                <a href="{{ route('admin.accounts.overview') }}" aria-expanded="false">
                    <i class="fas fa-building"></i>
                    <span class="nav-text">Accounts</span>
                </a>
            </li>
            
            <li class="{{ request()->routeIs('admin.reporting.*') ? 'mm-active' : '' }}">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false">
                    <i class="fas fa-chart-line"></i>
                    <span class="nav-text">Reporting</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('admin.reporting.message-log') }}" class="{{ request()->routeIs('admin.reporting.message-log') ? 'mm-active' : '' }}">Message Log (Global)</a></li>
                    <li><a href="{{ route('admin.reporting.client') }}" class="{{ request()->routeIs('admin.reporting.client') ? 'mm-active' : '' }}">Client Reporting</a></li>
                    <li><a href="{{ route('admin.reporting.supplier') }}" class="{{ request()->routeIs('admin.reporting.supplier') ? 'mm-active' : '' }}">Supplier Reporting</a></li>
                </ul>
            </li>
            
            <li class="{{ request()->routeIs('admin.assets.*') ? 'mm-active' : '' }}">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false">
                    <i class="fas fa-puzzle-piece"></i>
                    <span class="nav-text">Management</span>
                </a>
                <ul aria-expanded="false">
                    @php
                        $senderIdPendingCount = \App\Models\SenderId::withoutGlobalScopes()->whereIn('workflow_status', ['submitted', 'info_provided'])->count();
                    @endphp
                    <li><a href="{{ route('admin.assets.sender-ids') }}" class="{{ request()->routeIs('admin.assets.sender-ids') ? 'mm-active' : '' }}">Sender ID Approvals @if($senderIdPendingCount > 0)<span class="badge bg-danger" style="font-size: 0.6rem; width: 18px; height: 18px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; margin-left: 0.25rem; padding: 0;">{{ $senderIdPendingCount }}</span>@endif</a></li>
                    <li><a href="{{ route('admin.assets.rcs-agents') }}" class="{{ request()->routeIs('admin.assets.rcs-agents') ? 'mm-active' : '' }}">RCS Agent Registration <span class="badge bg-danger rounded-circle" style="font-size: 0.6rem; padding: 0.25rem 0.4rem; margin-left: 0.25rem;">3</span></a></li>
                    <li><a href="{{ route('admin.assets.campaigns') }}" class="{{ request()->routeIs('admin.assets.campaigns') ? 'mm-active' : '' }}">Campaigns</a></li>
                    <li><a href="{{ route('admin.management.templates') }}" class="{{ request()->routeIs('admin.management.templates') ? 'mm-active' : '' }}">Templates</a></li>
                    <li><a href="{{ route('admin.assets.numbers') }}" class="{{ request()->routeIs('admin.assets.numbers') ? 'mm-active' : '' }}">Numbers</a></li>
                    <li><a href="{{ route('admin.assets.email-to-sms') }}" class="{{ request()->routeIs('admin.assets.email-to-sms') ? 'mm-active' : '' }}">Email-to-SMS</a></li>
                    <li><a href="{{ route('admin.management.pricing') }}" class="{{ request()->routeIs('admin.management.pricing') ? 'mm-active' : '' }}"><i class="fas fa-tags me-1"></i>Pricing</a></li>
                </ul>
            </li>
            
            <li class="{{ request()->routeIs('admin.api.*') ? 'mm-active' : '' }}">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false">
                    <i class="fas fa-plug"></i>
                    <span class="nav-text">API & Integrations</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('admin.api.connections') }}" class="{{ request()->routeIs('admin.api.connections') ? 'mm-active' : '' }}">API Connections</a></li>
                    <li><a href="{{ route('admin.api.callbacks') }}" class="{{ request()->routeIs('admin.api.callbacks') ? 'mm-active' : '' }}">Delivery Callbacks</a></li>
                    <li><a href="{{ route('admin.api.health') }}" class="{{ request()->routeIs('admin.api.health') ? 'mm-active' : '' }}">Integration Health</a></li>
                </ul>
            </li>
            
            <li class="{{ request()->routeIs('admin.billing.*') ? 'mm-active' : '' }}">
                <a href="{{ route('admin.billing.invoices') }}" aria-expanded="false">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span class="nav-text">Invoices</span>
                </a>
            </li>

            <li class="{{ request()->routeIs('admin.suppliers.*') || request()->routeIs('admin.gateways.*') || request()->routeIs('admin.rate-cards.*') || request()->routeIs('admin.mcc-mnc.*') ? 'mm-active' : '' }}">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false">
                    <i class="fas fa-dollar-sign"></i>
                    <span class="nav-text">Supplier Management</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('admin.suppliers.index') }}" class="{{ request()->routeIs('admin.suppliers.*') ? 'mm-active' : '' }}">Suppliers</a></li>
                    <li><a href="{{ route('admin.gateways.index') }}" class="{{ request()->routeIs('admin.gateways.*') ? 'mm-active' : '' }}">Gateways</a></li>
                    <li><a href="{{ route('admin.rate-cards.index') }}" class="{{ request()->routeIs('admin.rate-cards.*') ? 'mm-active' : '' }}">Rate Cards</a></li>
                    <li><a href="{{ route('admin.mcc-mnc.index') }}" class="{{ request()->routeIs('admin.mcc-mnc.*') ? 'mm-active' : '' }}">MCC/MNC Reference</a></li>
                </ul>
            </li>

            <li class="{{ request()->routeIs('admin.security.*') ? 'mm-active' : '' }}">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false">
                    <i class="fas fa-shield-alt"></i>
                    <span class="nav-text">Security</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('admin.security.audit-logs') }}" class="{{ request()->routeIs('admin.security.audit-logs') ? 'mm-active' : '' }}">Audit Logs</a></li>
                    <li><a href="{{ route('admin.security.country-controls') }}" class="{{ request()->routeIs('admin.security.country-controls') ? 'mm-active' : '' }}">Country Controls</a></li>
                    <li><a href="{{ route('admin.security.security-compliance-controls') }}" class="{{ request()->routeIs('admin.security.security-compliance-controls') ? 'mm-active' : '' }}">Spam Filter</a></li>
                    <li><a href="{{ route('admin.security.ip-allowlists') }}" class="{{ request()->routeIs('admin.security.ip-allowlists') ? 'mm-active' : '' }}">IP Allow Lists</a></li>
                    @if(in_array(session('admin_role', 'super_admin'), ['super_admin', 'internal_support']))
                    <li><a href="{{ route('admin.security.admin-users') }}" class="{{ request()->routeIs('admin.security.admin-users') ? 'mm-active' : '' }}">Admin Users</a></li>
                    @endif
                </ul>
            </li>
            
            <li class="{{ request()->routeIs('admin.system.*') ? 'mm-active' : '' }}">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false">
                    <i class="fas fa-server"></i>
                    <span class="nav-text">System Settings</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('admin.system.pricing') }}" class="{{ request()->routeIs('admin.system.pricing') ? 'mm-active' : '' }}">Supplier Pricing</a></li>
                    <li><a href="{{ route('admin.system.routing') }}" class="{{ request()->routeIs('admin.system.routing') ? 'mm-active' : '' }}">Routing Rules</a></li>
                    <li><a href="{{ route('admin.system.flags') }}" class="{{ request()->routeIs('admin.system.flags') ? 'mm-active' : '' }}">Platform Flags</a></li>
                </ul>
            </li>
        </ul>
        
        <div class="copyright admin-copyright">
            <p><strong>QuickSMS Admin</strong></p>
            <p class="fs-12">Internal Use Only</p>
            <p class="fs-12">&copy; {{ date('Y') }}</p>
        </div>
    </div>
</div>
