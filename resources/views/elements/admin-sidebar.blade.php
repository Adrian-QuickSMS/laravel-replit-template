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
                <a class="has-arrow" href="javascript:void()" aria-expanded="false">
                    <i class="fas fa-building"></i>
                    <span class="nav-text">Accounts</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('admin.accounts.overview') }}" class="{{ request()->routeIs('admin.accounts.overview') ? 'mm-active' : '' }}">Account Overview</a></li>
                    <li><a href="{{ route('admin.accounts.sub-accounts') }}" class="{{ request()->routeIs('admin.accounts.sub-accounts') ? 'mm-active' : '' }}">Sub Accounts</a></li>
                    <li><a href="{{ route('admin.accounts.balances') }}" class="{{ request()->routeIs('admin.accounts.balances') ? 'mm-active' : '' }}">Balances & Credit</a></li>
                    <li><a href="{{ route('admin.accounts.details') }}" class="{{ request()->routeIs('admin.accounts.details') ? 'mm-active' : '' }}">Account Details</a></li>
                </ul>
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
                    <li><a href="{{ route('admin.reporting.finance') }}" class="{{ request()->routeIs('admin.reporting.finance') ? 'mm-active' : '' }}">Finance Reports</a></li>
                </ul>
            </li>
            
            <li class="{{ request()->routeIs('admin.campaigns.*') ? 'mm-active' : '' }}">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false">
                    <i class="fas fa-bullhorn"></i>
                    <span class="nav-text">Campaign Oversight</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('admin.campaigns.active') }}" class="{{ request()->routeIs('admin.campaigns.active') ? 'mm-active' : '' }}">Active / Scheduled</a></li>
                    <li><a href="{{ route('admin.campaigns.approvals') }}" class="{{ request()->routeIs('admin.campaigns.approvals') ? 'mm-active' : '' }}">Approvals Queue</a></li>
                    <li><a href="{{ route('admin.campaigns.blocked') }}" class="{{ request()->routeIs('admin.campaigns.blocked') ? 'mm-active' : '' }}">Blocked / Failed</a></li>
                </ul>
            </li>
            
            <li class="{{ request()->routeIs('admin.assets.*') ? 'mm-active' : '' }}">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false">
                    <i class="fas fa-puzzle-piece"></i>
                    <span class="nav-text">Messaging Assets</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('admin.assets.sender-ids') }}" class="{{ request()->routeIs('admin.assets.sender-ids') ? 'mm-active' : '' }}">Sender ID Approvals</a></li>
                    <li><a href="{{ route('admin.assets.rcs-agents') }}" class="{{ request()->routeIs('admin.assets.rcs-agents') ? 'mm-active' : '' }}">RCS Agent Registration</a></li>
                    <li><a href="{{ route('admin.assets.templates') }}" class="{{ request()->routeIs('admin.assets.templates') ? 'mm-active' : '' }}">Templates</a></li>
                    <li><a href="{{ route('admin.assets.numbers') }}" class="{{ request()->routeIs('admin.assets.numbers') ? 'mm-active' : '' }}">Numbers</a></li>
                    <li><a href="{{ route('admin.assets.email-to-sms') }}" class="{{ request()->routeIs('admin.assets.email-to-sms') ? 'mm-active' : '' }}">Email-to-SMS</a></li>
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
                <a class="has-arrow" href="javascript:void()" aria-expanded="false">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span class="nav-text">Invoices & Payments</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('admin.billing.invoices') }}" class="{{ request()->routeIs('admin.billing.invoices') ? 'mm-active' : '' }}">Invoices (All Clients)</a></li>
                    <li><a href="{{ route('admin.billing.payments') }}" class="{{ request()->routeIs('admin.billing.payments') ? 'mm-active' : '' }}">Payments</a></li>
                    <li><a href="{{ route('admin.billing.credits') }}" class="{{ request()->routeIs('admin.billing.credits') ? 'mm-active' : '' }}">Credit Adjustments</a></li>
                </ul>
            </li>
            
            <li class="{{ request()->routeIs('admin.security.*') ? 'mm-active' : '' }}">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false">
                    <i class="fas fa-shield-alt"></i>
                    <span class="nav-text">Security & Compliance</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="{{ route('admin.security.audit-logs') }}" class="{{ request()->routeIs('admin.security.audit-logs') ? 'mm-active' : '' }}">Audit Logs</a></li>
                    <li><a href="{{ route('admin.security.country-controls') }}" class="{{ request()->routeIs('admin.security.country-controls') ? 'mm-active' : '' }}">Country Controls</a></li>
                    <li><a href="{{ route('admin.security.anti-spam') }}" class="{{ request()->routeIs('admin.security.anti-spam') ? 'mm-active' : '' }}">Anti-Spam Rules</a></li>
                    <li><a href="{{ route('admin.security.ip-allowlists') }}" class="{{ request()->routeIs('admin.security.ip-allowlists') ? 'mm-active' : '' }}">IP Allow Lists</a></li>
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
