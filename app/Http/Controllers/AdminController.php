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
}
