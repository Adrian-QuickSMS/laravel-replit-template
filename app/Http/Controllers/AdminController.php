<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard', [
            'page_title' => 'Admin Dashboard'
        ]);
    }

    public function approvalQueue()
    {
        return view('admin.approval-queue', [
            'page_title' => 'Approval Queue'
        ]);
    }

    public function accountsOverview()
    {
        return view('admin.accounts.overview', [
            'page_title' => 'Account Overview'
        ]);
    }

    public function accountsSubAccounts()
    {
        return view('admin.accounts.sub-accounts', [
            'page_title' => 'Sub Accounts'
        ]);
    }

    public function accountsBalances()
    {
        return view('admin.accounts.balances', [
            'page_title' => 'Balances & Credit'
        ]);
    }

    public function accountsDetails($accountId)
    {
        return view('admin.accounts.details', [
            'page_title' => 'Account Details',
            'account_id' => $accountId
        ]);
    }

    public function accountsBilling($accountId)
    {
        return view('admin.accounts.billing', [
            'page_title' => 'Account Billing',
            'account_id' => $accountId
        ]);
    }

    public function reportingMessageLog()
    {
        return view('admin.reporting.message-log', [
            'page_title' => 'Message Log (Global)'
        ]);
    }

    public function reportingClient()
    {
        return view('admin.reporting.client', [
            'page_title' => 'Client Reporting'
        ]);
    }

    public function reportingSupplier()
    {
        return view('admin.reporting.supplier', [
            'page_title' => 'Supplier Reporting'
        ]);
    }

    public function reportingFinance()
    {
        return view('admin.reporting.finance', [
            'page_title' => 'Finance Reports'
        ]);
    }

    public function campaignsActive()
    {
        return view('admin.campaigns.active', [
            'page_title' => 'Active / Scheduled Campaigns'
        ]);
    }

    public function campaignsApprovals()
    {
        return view('admin.campaigns.approvals', [
            'page_title' => 'Approvals Queue'
        ]);
    }

    public function campaignsBlocked()
    {
        return view('admin.campaigns.blocked', [
            'page_title' => 'Blocked / Failed Campaigns'
        ]);
    }

    public function assetsSenderIds()
    {
        return view('admin.assets.sender-ids', [
            'page_title' => 'Sender ID Approvals'
        ]);
    }

    public function assetsSenderIdDetail($id)
    {
        return view('admin.assets.sender-id-detail', [
            'page_title' => 'SenderID Approval Detail',
            'sender_id' => $id
        ]);
    }

    public function assetsRcsAgents()
    {
        return view('admin.assets.rcs-agents', [
            'page_title' => 'RCS Agent Registration'
        ]);
    }

    public function assetsRcsAgentDetail($id)
    {
        return view('admin.assets.rcs-agent-detail', [
            'page_title' => 'RCS Agent Approval Detail',
            'agent_id' => $id
        ]);
    }

    public function assetsTemplates()
    {
        return view('admin.assets.templates', [
            'page_title' => 'Templates'
        ]);
    }

    public function assetsCampaigns()
    {
        return view('admin.assets.campaigns', [
            'page_title' => 'Campaign History'
        ]);
    }

    public function assetsNumbers()
    {
        return view('admin.assets.numbers', [
            'page_title' => 'Numbers'
        ]);
    }

    public function assetsNumberConfigure($id)
    {
        return view('admin.assets.number-configure', [
            'page_title' => 'Configure Number',
            'number_id' => $id
        ]);
    }

    public function assetsEmailToSms()
    {
        return view('admin.assets.email-to-sms', [
            'page_title' => 'Email-to-SMS'
        ]);
    }

    public function assetsEmailToSmsStandardEdit($id)
    {
        return view('admin.assets.email-to-sms-standard-edit', [
            'page_title' => 'Edit Standard Email-to-SMS',
            'id' => $id,
            'isAdmin' => true
        ]);
    }

    public function assetsEmailToSmsContactListEdit($id)
    {
        return view('admin.assets.email-to-sms-contact-list-edit', [
            'page_title' => 'Edit Contact List Email-to-SMS',
            'id' => $id,
            'isAdmin' => true
        ]);
    }

    public function apiConnections()
    {
        return view('admin.api.connections', [
            'page_title' => 'API Connections'
        ]);
    }

    public function apiConnectionCreate()
    {
        return view('admin.api.connections-wizard', [
            'page_title' => 'Create API Connection'
        ]);
    }

    public function apiCallbacks()
    {
        return view('admin.api.callbacks', [
            'page_title' => 'Delivery Callbacks'
        ]);
    }

    public function apiHealth()
    {
        return view('admin.api.health', [
            'page_title' => 'Integration Health'
        ]);
    }

    public function billingInvoices()
    {
        return view('admin.billing.invoices', [
            'page_title' => 'Invoices (All Clients)'
        ]);
    }

    public function billingPayments()
    {
        return view('admin.billing.payments', [
            'page_title' => 'Payments'
        ]);
    }

    public function billingCredits()
    {
        return view('admin.billing.credits', [
            'page_title' => 'Credit Adjustments'
        ]);
    }

    public function securityAuditLogs()
    {
        return view('admin.security.audit-logs', [
            'page_title' => 'Audit Logs'
        ]);
    }

    public function securityCountryControls()
    {
        return view('admin.security.country-controls', [
            'page_title' => 'Country Controls'
        ]);
    }

    public function securityAntiSpam()
    {
        return view('admin.security.anti-spam', [
            'page_title' => 'Anti-Spam Rules'
        ]);
    }

    public function securityIpAllowlists()
    {
        return view('admin.security.ip-allowlists', [
            'page_title' => 'IP Allow Lists'
        ]);
    }

    public function securityAdminUsers()
    {
        // INTERNAL ONLY - Gate access to Super Admin and Internal Support roles
        // Hardcoded role check for v1 - TODO: Replace with proper RBAC
        $allowedRoles = ['super_admin', 'internal_support'];
        $currentRole = session('admin_role', 'super_admin'); // Default to super_admin for development
        
        if (!in_array($currentRole, $allowedRoles)) {
            abort(403, 'Access denied. This module is restricted to Super Admin and Internal Support roles.');
        }
        
        // Mock admin users data with expanded fields including created_by and active_sessions
        $adminUsers = [
            ['id' => 'ADM001', 'name' => 'Sarah Johnson', 'email' => 'sarah.johnson@quicksms.co.uk', 'role' => 'Super Admin', 'department' => 'Engineering', 'status' => 'Active', 'mfa_status' => 'Enrolled', 'mfa_method' => 'Authenticator', 'last_login' => '2026-01-27 09:15:00', 'last_activity' => '2026-01-27 11:20:00', 'failed_logins_24h' => 0, 'created_at' => '2024-03-15', 'created_by' => 'System', 'active_sessions' => 2],
            ['id' => 'ADM002', 'name' => 'James Mitchell', 'email' => 'james.mitchell@quicksms.co.uk', 'role' => 'Super Admin', 'department' => 'Operations', 'status' => 'Active', 'mfa_status' => 'Enrolled', 'mfa_method' => 'Both', 'last_login' => '2026-01-27 08:45:00', 'last_activity' => '2026-01-27 10:30:00', 'failed_logins_24h' => 0, 'created_at' => '2024-06-20', 'created_by' => 'Sarah Johnson', 'active_sessions' => 1],
            ['id' => 'ADM003', 'name' => 'Emily Chen', 'email' => 'emily.chen@quicksms.co.uk', 'role' => 'Internal Support', 'department' => 'Customer Success', 'status' => 'Active', 'mfa_status' => 'Enrolled', 'mfa_method' => 'SMS', 'last_login' => '2026-01-26 16:30:00', 'last_activity' => '2026-01-26 17:45:00', 'failed_logins_24h' => 1, 'created_at' => '2024-09-10', 'created_by' => 'Sarah Johnson', 'active_sessions' => 0],
            ['id' => 'ADM004', 'name' => 'Michael Brown', 'email' => 'michael.brown@quicksms.co.uk', 'role' => 'Internal Support', 'department' => 'Customer Success', 'status' => 'Active', 'mfa_status' => 'Enrolled', 'mfa_method' => 'Authenticator', 'last_login' => '2026-01-25 14:20:00', 'last_activity' => '2026-01-25 16:00:00', 'failed_logins_24h' => 0, 'created_at' => '2025-01-05', 'created_by' => 'James Mitchell', 'active_sessions' => 0],
            ['id' => 'ADM005', 'name' => 'Anna Williams', 'email' => 'anna.williams@quicksms.co.uk', 'role' => 'Internal Support', 'department' => 'Technical Support', 'status' => 'Suspended', 'mfa_status' => 'Enrolled', 'mfa_method' => 'Authenticator', 'last_login' => '2026-01-10 11:00:00', 'last_activity' => '2026-01-10 11:00:00', 'failed_logins_24h' => 5, 'created_at' => '2024-11-22', 'created_by' => 'David Lee', 'active_sessions' => 0],
            ['id' => 'ADM006', 'name' => 'David Lee', 'email' => 'david.lee@quicksms.co.uk', 'role' => 'Super Admin', 'department' => 'Security', 'status' => 'Active', 'mfa_status' => 'Enrolled', 'mfa_method' => 'Both', 'last_login' => '2026-01-27 10:00:00', 'last_activity' => '2026-01-27 11:15:00', 'failed_logins_24h' => 0, 'created_at' => '2023-08-01', 'created_by' => 'System', 'active_sessions' => 1],
            ['id' => 'ADM007', 'name' => 'Rachel Green', 'email' => 'rachel.green@quicksms.co.uk', 'role' => 'Internal Support', 'department' => 'Customer Success', 'status' => 'Invited', 'mfa_status' => 'Not Enrolled', 'mfa_method' => null, 'last_login' => null, 'last_activity' => null, 'failed_logins_24h' => 0, 'created_at' => '2026-01-25', 'created_by' => 'Sarah Johnson', 'active_sessions' => 0, 'invite_sent_at' => '2026-01-25 10:00:00'],
            ['id' => 'ADM008', 'name' => 'Tom Harris', 'email' => 'tom.harris@quicksms.co.uk', 'role' => 'Super Admin', 'department' => 'Engineering', 'status' => 'Active', 'mfa_status' => 'Enrolled', 'mfa_method' => 'Authenticator', 'last_login' => '2026-01-27 07:30:00', 'last_activity' => '2026-01-27 09:45:00', 'failed_logins_24h' => 0, 'created_at' => '2023-05-12', 'created_by' => 'System', 'active_sessions' => 1],
            ['id' => 'ADM009', 'name' => 'Lisa Wong', 'email' => 'lisa.wong@quicksms.co.uk', 'role' => 'Internal Support', 'department' => 'Technical Support', 'status' => 'Active', 'mfa_status' => 'Enrolled', 'mfa_method' => 'SMS', 'last_login' => '2026-01-26 09:00:00', 'last_activity' => '2026-01-26 12:30:00', 'failed_logins_24h' => 0, 'created_at' => '2024-02-18', 'created_by' => 'Tom Harris', 'active_sessions' => 0],
            ['id' => 'ADM010', 'name' => 'Chris Taylor', 'email' => 'chris.taylor@quicksms.co.uk', 'role' => 'Internal Support', 'department' => 'Operations', 'status' => 'Archived', 'mfa_status' => 'Enrolled', 'mfa_method' => 'Authenticator', 'last_login' => '2025-12-15 10:00:00', 'last_activity' => '2025-12-15 10:00:00', 'failed_logins_24h' => 0, 'created_at' => '2023-11-01', 'created_by' => 'Sarah Johnson', 'active_sessions' => 0],
            ['id' => 'ADM011', 'name' => 'Maria Garcia', 'email' => 'maria.garcia@quicksms.co.uk', 'role' => 'Super Admin', 'department' => 'Security', 'status' => 'Active', 'mfa_status' => 'Enrolled', 'mfa_method' => 'Both', 'last_login' => '2026-01-27 08:00:00', 'last_activity' => '2026-01-27 11:00:00', 'failed_logins_24h' => 0, 'created_at' => '2024-01-15', 'created_by' => 'David Lee', 'active_sessions' => 1],
            ['id' => 'ADM012', 'name' => 'Kevin Patel', 'email' => 'kevin.patel@quicksms.co.uk', 'role' => 'Internal Support', 'department' => 'Customer Success', 'status' => 'Active', 'mfa_status' => 'Enrolled', 'mfa_method' => 'Authenticator', 'last_login' => '2026-01-26 14:00:00', 'last_activity' => '2026-01-26 17:30:00', 'failed_logins_24h' => 2, 'created_at' => '2024-08-22', 'created_by' => 'Emily Chen', 'active_sessions' => 0],
            ['id' => 'ADM013', 'name' => 'Sophie Martin', 'email' => 'sophie.martin@quicksms.co.uk', 'role' => 'Internal Support', 'department' => 'Technical Support', 'status' => 'Active', 'mfa_status' => 'Enrolled', 'mfa_method' => 'SMS', 'last_login' => '2026-01-27 09:30:00', 'last_activity' => '2026-01-27 10:45:00', 'failed_logins_24h' => 0, 'created_at' => '2024-07-10', 'created_by' => 'Lisa Wong', 'active_sessions' => 1],
            ['id' => 'ADM014', 'name' => 'Alex Thompson', 'email' => 'alex.thompson@quicksms.co.uk', 'role' => 'Super Admin', 'department' => 'Engineering', 'status' => 'Active', 'mfa_status' => 'Enrolled', 'mfa_method' => 'Authenticator', 'last_login' => '2026-01-27 06:45:00', 'last_activity' => '2026-01-27 08:30:00', 'failed_logins_24h' => 0, 'created_at' => '2023-09-05', 'created_by' => 'System', 'active_sessions' => 0],
            ['id' => 'ADM015', 'name' => 'Nina Roberts', 'email' => 'nina.roberts@quicksms.co.uk', 'role' => 'Internal Support', 'department' => 'Operations', 'status' => 'Invited', 'mfa_status' => 'Not Enrolled', 'mfa_method' => null, 'last_login' => null, 'last_activity' => null, 'failed_logins_24h' => 0, 'created_at' => '2026-01-26', 'created_by' => 'James Mitchell', 'active_sessions' => 0, 'invite_sent_at' => '2026-01-26 14:30:00'],
            ['id' => 'ADM016', 'name' => 'Ben Wilson', 'email' => 'ben.wilson@quicksms.co.uk', 'role' => 'Internal Support', 'department' => 'Customer Success', 'status' => 'Active', 'mfa_status' => 'Enrolled', 'mfa_method' => 'Both', 'last_login' => '2026-01-25 11:00:00', 'last_activity' => '2026-01-25 15:00:00', 'failed_logins_24h' => 0, 'created_at' => '2024-04-18', 'created_by' => 'Sarah Johnson', 'active_sessions' => 0],
            ['id' => 'ADM017', 'name' => 'Claire Adams', 'email' => 'claire.adams@quicksms.co.uk', 'role' => 'Super Admin', 'department' => 'Security', 'status' => 'Active', 'mfa_status' => 'Enrolled', 'mfa_method' => 'Authenticator', 'last_login' => '2026-01-27 07:15:00', 'last_activity' => '2026-01-27 09:00:00', 'failed_logins_24h' => 0, 'created_at' => '2023-12-01', 'created_by' => 'David Lee', 'active_sessions' => 1],
            ['id' => 'ADM018', 'name' => 'Daniel Scott', 'email' => 'daniel.scott@quicksms.co.uk', 'role' => 'Internal Support', 'department' => 'Technical Support', 'status' => 'Suspended', 'mfa_status' => 'Enrolled', 'mfa_method' => 'SMS', 'last_login' => '2026-01-20 09:00:00', 'last_activity' => '2026-01-20 09:00:00', 'failed_logins_24h' => 3, 'created_at' => '2024-05-30', 'created_by' => 'Tom Harris', 'active_sessions' => 0],
            ['id' => 'ADM019', 'name' => 'Emma Davis', 'email' => 'emma.davis@quicksms.co.uk', 'role' => 'Internal Support', 'department' => 'Customer Success', 'status' => 'Active', 'mfa_status' => 'Enrolled', 'mfa_method' => 'Authenticator', 'last_login' => '2026-01-26 13:00:00', 'last_activity' => '2026-01-26 16:45:00', 'failed_logins_24h' => 0, 'created_at' => '2024-10-05', 'created_by' => 'Emily Chen', 'active_sessions' => 0],
            ['id' => 'ADM020', 'name' => 'Frank Miller', 'email' => 'frank.miller@quicksms.co.uk', 'role' => 'Super Admin', 'department' => 'Operations', 'status' => 'Active', 'mfa_status' => 'Enrolled', 'mfa_method' => 'Both', 'last_login' => '2026-01-27 08:30:00', 'last_activity' => '2026-01-27 10:15:00', 'failed_logins_24h' => 0, 'created_at' => '2023-07-22', 'created_by' => 'System', 'active_sessions' => 1],
            ['id' => 'ADM021', 'name' => 'Grace Hill', 'email' => 'grace.hill@quicksms.co.uk', 'role' => 'Internal Support', 'department' => 'Technical Support', 'status' => 'Active', 'mfa_status' => 'Enrolled', 'mfa_method' => 'Authenticator', 'last_login' => '2026-01-25 10:30:00', 'last_activity' => '2026-01-25 14:00:00', 'failed_logins_24h' => 1, 'created_at' => '2024-06-15', 'created_by' => 'Lisa Wong', 'active_sessions' => 0],
            ['id' => 'ADM022', 'name' => 'Henry Clark', 'email' => 'henry.clark@quicksms.co.uk', 'role' => 'Internal Support', 'department' => 'Operations', 'status' => 'Active', 'mfa_status' => 'Enrolled', 'mfa_method' => 'SMS', 'last_login' => '2026-01-26 08:45:00', 'last_activity' => '2026-01-26 11:30:00', 'failed_logins_24h' => 0, 'created_at' => '2024-03-20', 'created_by' => 'James Mitchell', 'active_sessions' => 0],
            ['id' => 'ADM023', 'name' => 'Ivy Moore', 'email' => 'ivy.moore@quicksms.co.uk', 'role' => 'Super Admin', 'department' => 'Engineering', 'status' => 'Active', 'mfa_status' => 'Enrolled', 'mfa_method' => 'Both', 'last_login' => '2026-01-27 07:00:00', 'last_activity' => '2026-01-27 09:30:00', 'failed_logins_24h' => 0, 'created_at' => '2023-10-10', 'created_by' => 'Sarah Johnson', 'active_sessions' => 2],
            ['id' => 'ADM024', 'name' => 'Jack White', 'email' => 'jack.white@quicksms.co.uk', 'role' => 'Internal Support', 'department' => 'Customer Success', 'status' => 'Archived', 'mfa_status' => 'Enrolled', 'mfa_method' => 'Authenticator', 'last_login' => '2025-11-30 09:00:00', 'last_activity' => '2025-11-30 09:00:00', 'failed_logins_24h' => 0, 'created_at' => '2023-06-01', 'created_by' => 'System', 'active_sessions' => 0],
        ];

        return view('admin.security.admin-users', [
            'page_title' => 'Admin Users',
            'adminUsers' => $adminUsers
        ]);
    }

    public function systemPricing()
    {
        return view('admin.system.pricing', [
            'page_title' => 'Supplier Pricing'
        ]);
    }

    public function systemRouting()
    {
        return view('admin.system.routing', [
            'page_title' => 'Routing Rules'
        ]);
    }

    public function systemFlags()
    {
        return view('admin.system.flags', [
            'page_title' => 'Platform Flags'
        ]);
    }

    public function managementTemplates()
    {
        return view('admin.management.templates', [
            'page_title' => 'Global Templates Library'
        ]);
    }

    public function managementTemplateEdit($accountId, $templateId)
    {
        $sender_ids = [
            ['id' => 1, 'name' => 'QuickSMS', 'type' => 'alphanumeric'],
            ['id' => 2, 'name' => 'ALERTS', 'type' => 'alphanumeric'],
            ['id' => 3, 'name' => '+447700900100', 'type' => 'numeric'],
        ];

        $rcs_agents = [
            ['id' => 1, 'name' => 'QuickSMS Brand', 'logo' => '/images/rcs-agents/quicksms-brand.svg', 'tagline' => 'Fast messaging for everyone', 'brand_color' => '#886CC0', 'status' => 'approved'],
            ['id' => 2, 'name' => 'Promotions Agent', 'logo' => '/images/rcs-agents/promotions-agent.svg', 'tagline' => 'Exclusive deals & offers', 'brand_color' => '#E91E63', 'status' => 'approved'],
        ];

        // TODO: Replace with API call - adminTemplatesService.getTemplate(accountId, templateId)
        $template = $this->getAdminMockTemplate($accountId, $templateId);
        $accountName = $this->getAccountName($accountId);

        return view('shared.template-wizard', [
            'page_title' => 'Edit Template',
            'mode' => 'edit',
            'isAdminMode' => true,
            'isEditMode' => true,
            'showRichRcs' => false,
            'accountId' => $accountId,
            'accountName' => $accountName,
            'templateId' => $templateId,
            'template' => $template,
            'sender_ids' => $sender_ids,
            'rcs_agents' => $rcs_agents
        ]);
    }

    private function getAdminMockTemplate($accountId, $templateId)
    {
        // TODO: Replace with API call - adminTemplatesService.getTemplate(accountId, templateId)
        $mockTemplates = [
            'TPL-12345678' => [
                'id' => 1,
                'name' => 'Winter Sale 2026',
                'templateId' => 'TPL-12345678',
                'trigger' => 'portal',
                'channel' => 'sms',
                'content' => 'Hi {FirstName}! Our Winter Sale is here. Get 40% off all items. Shop now: {Link}',
                'senderId' => '1',
                'rcsAgent' => '',
                'trackableLink' => true,
                'optOut' => true
            ],
            'TPL-23456789' => [
                'id' => 2,
                'name' => 'Appointment Confirmation',
                'templateId' => 'TPL-23456789',
                'trigger' => 'api',
                'channel' => 'sms',
                'content' => 'Hi {FirstName}, your appointment is confirmed for {AppointmentDate} at {AppointmentTime}.',
                'senderId' => '2',
                'rcsAgent' => '',
                'trackableLink' => false,
                'optOut' => false
            ],
            'TPL-34567890' => [
                'id' => 3,
                'name' => 'Delivery Update',
                'templateId' => 'TPL-34567890',
                'trigger' => 'api',
                'channel' => 'basic_rcs',
                'content' => 'Your order #{OrderId} is on its way! Track here: {TrackingLink}',
                'senderId' => '1',
                'rcsAgent' => '1',
                'trackableLink' => true,
                'optOut' => false
            ]
        ];

        return $mockTemplates[$templateId] ?? [
            'id' => 999,
            'name' => 'Template ' . $templateId,
            'templateId' => $templateId,
            'trigger' => 'api',
            'channel' => 'sms',
            'content' => 'Template content for ' . $templateId,
            'senderId' => '1',
            'rcsAgent' => '',
            'trackableLink' => false,
            'optOut' => false
        ];
    }

    private function getAccountName($accountId)
    {
        // TODO: Replace with API call
        $accounts = [
            'ACC-001' => 'Acme Corporation',
            'ACC-002' => 'TechStart Ltd',
            'ACC-003' => 'GlobalRetail Inc'
        ];

        return $accounts[$accountId] ?? 'Account ' . $accountId;
    }

    public function adminTemplateEditStep1($accountId, $templateId)
    {
        // TODO: Replace with API call - adminTemplatesService.getTemplate(accountId, templateId)
        $template = $this->getAdminMockTemplate($accountId, $templateId);
        $accountName = $this->getAccountName($accountId);

        return view('quicksms.management.templates.create-step1', [
            'page_title' => 'Edit Template - Metadata',
            'isEditMode' => true,
            'isAdminMode' => true,
            'templateId' => $templateId,
            'accountId' => $accountId,
            'account' => ['id' => $accountId, 'name' => $accountName],
            'template' => $template
        ]);
    }

    public function adminTemplateEditStep2($accountId, $templateId)
    {
        $sender_ids = [
            ['id' => 1, 'name' => 'QuickSMS', 'type' => 'alphanumeric'],
            ['id' => 2, 'name' => 'ALERTS', 'type' => 'alphanumeric'],
            ['id' => 3, 'name' => '+447700900100', 'type' => 'numeric'],
        ];

        $rcs_agents = [
            ['id' => 1, 'name' => 'QuickSMS Brand', 'logo' => '/images/rcs-agents/quicksms-brand.svg', 'tagline' => 'Fast messaging for everyone', 'brand_color' => '#886CC0', 'status' => 'approved'],
            ['id' => 2, 'name' => 'Promotions Agent', 'logo' => '/images/rcs-agents/promotions-agent.svg', 'tagline' => 'Exclusive deals & offers', 'brand_color' => '#E91E63', 'status' => 'approved'],
        ];

        // TODO: Replace with API call - optOutService.getLists(accountId)
        $opt_out_lists = [
            ['id' => 1, 'name' => 'Marketing Opt-outs', 'count' => 1250],
            ['id' => 2, 'name' => 'Transactional Opt-outs', 'count' => 89],
        ];

        // TODO: Replace with API call - numbersService.getVirtualNumbers(accountId)
        $virtual_numbers = [
            ['id' => 1, 'number' => '+447700900200', 'label' => 'Customer Support'],
            ['id' => 2, 'number' => '+447700900201', 'label' => 'Sales'],
        ];

        // TODO: Replace with API call - optOutService.getDomains(accountId)
        $optout_domains = [
            ['id' => 1, 'domain' => 'optout.quicksms.co.uk', 'is_default' => true],
            ['id' => 2, 'domain' => 'stop.quicksms.co.uk', 'is_default' => false],
        ];

        // TODO: Replace with API call - adminTemplatesService.getTemplate(accountId, templateId)
        $template = $this->getAdminMockTemplate($accountId, $templateId);
        $accountName = $this->getAccountName($accountId);

        return view('quicksms.management.templates.create-step2', [
            'page_title' => 'Edit Template - Content',
            'isEditMode' => true,
            'isAdminMode' => true,
            'templateId' => $templateId,
            'accountId' => $accountId,
            'account' => ['id' => $accountId, 'name' => $accountName],
            'template' => $template,
            'sender_ids' => $sender_ids,
            'rcs_agents' => $rcs_agents,
            'opt_out_lists' => $opt_out_lists,
            'virtual_numbers' => $virtual_numbers,
            'optout_domains' => $optout_domains
        ]);
    }

    public function adminTemplateEditStep3($accountId, $templateId)
    {
        // TODO: Replace with API call - adminTemplatesService.getTemplate(accountId, templateId)
        $template = $this->getAdminMockTemplate($accountId, $templateId);
        $accountName = $this->getAccountName($accountId);

        return view('quicksms.management.templates.create-step3', [
            'page_title' => 'Edit Template - Settings',
            'isEditMode' => true,
            'isAdminMode' => true,
            'templateId' => $templateId,
            'accountId' => $accountId,
            'account' => ['id' => $accountId, 'name' => $accountName],
            'template' => $template
        ]);
    }

    public function adminTemplateEditReview($accountId, $templateId)
    {
        // TODO: Replace with API call - adminTemplatesService.getTemplate(accountId, templateId)
        $template = $this->getAdminMockTemplate($accountId, $templateId);
        $accountName = $this->getAccountName($accountId);

        return view('quicksms.management.templates.create-review', [
            'page_title' => 'Edit Template - Review',
            'isEditMode' => true,
            'isAdminMode' => true,
            'templateId' => $templateId,
            'accountId' => $accountId,
            'account' => ['id' => $accountId, 'name' => $accountName],
            'template' => $template
        ]);
    }
}
