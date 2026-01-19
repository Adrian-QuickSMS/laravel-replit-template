<div class="dlabnav">
    <div class="dlabnav-scroll">
        <ul class="metismenu" id="menu">
            <li data-nav="dashboard" class="{{ request()->routeIs('dashboard') ? 'mm-active' : '' }}">
                <a href="{{ route('dashboard') }}" aria-expanded="false">
                    <i class="fas fa-home"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            
            <li data-nav="messages" class="{{ request()->routeIs('messages') || request()->routeIs('messages.*') ? 'mm-active' : '' }}">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false">
                    <i class="fas fa-envelope"></i>
                    <span class="nav-text">Messages</span>
                </a>
                <ul aria-expanded="false">
                    <li data-subnav="send"><a href="{{ route('messages.send') }}" class="{{ request()->routeIs('messages.send') ? 'mm-active' : '' }}">Send Message</a></li>
                    <li data-subnav="inbox"><a href="{{ route('messages.inbox') }}" class="{{ request()->routeIs('messages.inbox') ? 'mm-active' : '' }}">Inbox</a></li>
                    <li data-subnav="campaign-history"><a href="{{ route('messages.campaign-history') }}" class="{{ request()->routeIs('messages.campaign-history') ? 'mm-active' : '' }}">Campaign History</a></li>
                    <li data-subnav="campaign-approvals" data-requires-role="campaign-approver,admin,owner"><a href="{{ route('messages.campaign-approvals') }}" class="{{ request()->routeIs('messages.campaign-approvals') ? 'mm-active' : '' }}"><i class="fas fa-clipboard-check me-1" style="font-size: 0.7rem;"></i>Campaign Approvals</a></li>
                </ul>
            </li>
            
            <li data-nav="contacts" class="{{ request()->routeIs('contacts') || request()->routeIs('contacts.*') ? 'mm-active' : '' }}">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false">
                    <i class="fas fa-address-book"></i>
                    <span class="nav-text">Contact Book</span>
                </a>
                <ul aria-expanded="false">
                    <li data-subnav="all"><a href="{{ route('contacts.all') }}" class="{{ request()->routeIs('contacts.all') ? 'mm-active' : '' }}">All Contacts</a></li>
                    <li data-subnav="lists"><a href="{{ route('contacts.lists') }}" class="{{ request()->routeIs('contacts.lists') ? 'mm-active' : '' }}">Lists</a></li>
                    <li data-subnav="tags"><a href="{{ route('contacts.tags') }}" class="{{ request()->routeIs('contacts.tags') ? 'mm-active' : '' }}">Tags</a></li>
                    <li data-subnav="opt-out"><a href="{{ route('contacts.opt-out') }}" class="{{ request()->routeIs('contacts.opt-out') ? 'mm-active' : '' }}">Opt-Out Lists</a></li>
                </ul>
            </li>
            
            <li data-nav="reporting" class="{{ request()->routeIs('reporting') || request()->routeIs('reporting.*') ? 'mm-active' : '' }}">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false">
                    <i class="fas fa-chart-bar"></i>
                    <span class="nav-text">Reporting</span>
                </a>
                <ul aria-expanded="false">
                    <li data-subnav="dashboard"><a href="{{ route('reporting.dashboard') }}" class="{{ request()->routeIs('reporting.dashboard') ? 'mm-active' : '' }}">Dashboard</a></li>
                    <li data-subnav="message-log"><a href="{{ route('reporting.message-log') }}" class="{{ request()->routeIs('reporting.message-log') ? 'mm-active' : '' }}">Message Log</a></li>
                    <li data-subnav="finance-data"><a href="{{ route('reporting.finance-data') }}" class="{{ request()->routeIs('reporting.finance-data') ? 'mm-active' : '' }}">Finance Data</a></li>
                    <li data-subnav="invoices"><a href="{{ route('reporting.invoices') }}" class="{{ request()->routeIs('reporting.invoices') ? 'mm-active' : '' }}">Invoices</a></li>
                    <li data-subnav="download-area"><a href="{{ route('reporting.download-area') }}" class="{{ request()->routeIs('reporting.download-area') ? 'mm-active' : '' }}">Download Area</a></li>
                </ul>
            </li>
            
            <li data-nav="purchase" class="{{ request()->routeIs('purchase') || request()->routeIs('purchase.*') ? 'mm-active' : '' }}">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="nav-text">Purchase</span>
                </a>
                <ul aria-expanded="false">
                    <li data-subnav="messages"><a href="{{ route('purchase.messages') }}" class="{{ request()->routeIs('purchase.messages') ? 'mm-active' : '' }}">Messages</a></li>
                    <li data-subnav="numbers"><a href="{{ route('purchase.numbers') }}" class="{{ request()->routeIs('purchase.numbers') ? 'mm-active' : '' }}">Numbers</a></li>
                </ul>
            </li>
            
            <li data-nav="management" class="{{ request()->routeIs('management') || request()->routeIs('management.*') ? 'mm-active' : '' }}">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false">
                    <i class="fas fa-cogs"></i>
                    <span class="nav-text">Management</span>
                </a>
                <ul aria-expanded="false">
                    <li data-subnav="rcs-agent"><a href="{{ route('management.rcs-agent') }}" class="{{ request()->routeIs('management.rcs-agent') ? 'mm-active' : '' }}">RCS Agent Registrations</a></li>
                    <li data-subnav="sms-sender-id"><a href="{{ route('management.sms-sender-id') }}" class="{{ request()->routeIs('management.sms-sender-id') ? 'mm-active' : '' }}">SMS SenderID Registration</a></li>
                    <li data-subnav="templates"><a href="{{ route('management.templates') }}" class="{{ request()->routeIs('management.templates') ? 'mm-active' : '' }}">Message Templates</a></li>
                    <li data-subnav="api-connections"><a href="{{ route('management.api-connections') }}" class="{{ request()->routeIs('management.api-connections') ? 'mm-active' : '' }}">API Connections</a></li>
                    <li data-subnav="email-to-sms"><a href="{{ route('management.email-to-sms') }}" class="{{ request()->routeIs('management.email-to-sms') ? 'mm-active' : '' }}">Email-to-SMS</a></li>
                    <li data-subnav="numbers"><a href="{{ route('management.numbers') }}" class="{{ request()->routeIs('management.numbers') ? 'mm-active' : '' }}">Numbers</a></li>
                </ul>
            </li>
            
            <li data-nav="account" class="{{ request()->routeIs('account') || request()->routeIs('account.*') ? 'mm-active' : '' }}">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false">
                    <i class="fas fa-user-circle"></i>
                    <span class="nav-text">Account</span>
                </a>
                <ul aria-expanded="false">
                    <li data-subnav="details"><a href="{{ route('account.details') }}" class="{{ request()->routeIs('account.details') ? 'mm-active' : '' }}">Details</a></li>
                    <li data-subnav="users"><a href="{{ route('account.users') }}" class="{{ request()->routeIs('account.users') || request()->routeIs('account.sub-accounts') || request()->routeIs('account.sub-accounts.*') ? 'mm-active' : '' }}">Sub Accounts, Users and Permissions</a></li>
                    <li data-subnav="audit-logs"><a href="{{ route('account.audit-logs') }}" class="{{ request()->routeIs('account.audit-logs') ? 'mm-active' : '' }}">Audit Logs</a></li>
                    <li data-subnav="security"><a href="{{ route('account.security') }}" class="{{ request()->routeIs('account.security') ? 'mm-active' : '' }}">Security Settings</a></li>
                </ul>
            </li>
            
            <li data-nav="support" class="{{ request()->routeIs('support') || request()->routeIs('support.*') ? 'mm-active' : '' }}">
                <a class="has-arrow" href="javascript:void()" aria-expanded="false">
                    <i class="fas fa-headset"></i>
                    <span class="nav-text">Support</span>
                </a>
                <ul aria-expanded="false">
                    <li data-subnav="dashboard"><a href="{{ route('support.dashboard') }}" class="{{ request()->routeIs('support.dashboard') ? 'mm-active' : '' }}">Dashboard</a></li>
                    <li data-subnav="create-ticket"><a href="{{ route('support.create-ticket') }}" class="{{ request()->routeIs('support.create-ticket') ? 'mm-active' : '' }}">Create a Ticket</a></li>
                    <li data-subnav="knowledge-base"><a href="{{ route('support.knowledge-base') }}" class="{{ request()->routeIs('support.knowledge-base') ? 'mm-active' : '' }}">Knowledge Base</a></li>
                </ul>
            </li>
        </ul>
        
        <div class="copyright">
            <p><strong>QuickSMS</strong></p>
            <p class="fs-12">&copy; {{ date('Y') }} All Rights Reserved</p>
        </div>
    </div>
</div>
