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

    public function accountsDetails()
    {
        return view('admin.accounts.details', [
            'page_title' => 'Account Details'
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

    public function assetsRcsAgents()
    {
        return view('admin.assets.rcs-agents', [
            'page_title' => 'RCS Agent Registration'
        ]);
    }

    public function assetsTemplates()
    {
        return view('admin.assets.templates', [
            'page_title' => 'Templates'
        ]);
    }

    public function assetsNumbers()
    {
        return view('admin.assets.numbers', [
            'page_title' => 'Numbers'
        ]);
    }

    public function assetsEmailToSms()
    {
        return view('admin.assets.email-to-sms', [
            'page_title' => 'Email-to-SMS'
        ]);
    }

    public function apiConnections()
    {
        return view('admin.api.connections', [
            'page_title' => 'API Connections'
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
}
