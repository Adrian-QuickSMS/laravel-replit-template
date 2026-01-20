<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuickSMSController;
use App\Http\Controllers\Api\RcsAssetController;

// Public auth routes (no authentication required)
Route::controller(QuickSMSController::class)->group(function () {
    Route::get('/login', 'login')->name('auth.login');
    Route::get('/signup', 'signup')->name('auth.signup');
    Route::get('/signup/verify', 'verifyEmail')->name('auth.verify-email');
    Route::get('/signup/security', 'signupSecurity')->name('auth.security');
});

Route::controller(QuickSMSController::class)->group(function () {
    Route::get('/', 'dashboard')->name('dashboard');
    
    Route::get('/messages', 'messages')->name('messages');
    Route::get('/messages/send', 'sendMessage')->name('messages.send');
    Route::get('/messages/confirm', 'confirmCampaign')->name('messages.confirm');
    Route::get('/messages/inbox', 'inbox')->name('messages.inbox');
    Route::get('/messages/campaign-history', 'campaignHistory')->name('messages.campaign-history');
    Route::get('/messages/campaign-approvals', 'campaignApprovals')->name('messages.campaign-approvals');
    
    Route::get('/contacts', 'contacts')->name('contacts');
    Route::get('/contacts/all', 'allContacts')->name('contacts.all');
    Route::get('/contacts/lists', 'lists')->name('contacts.lists');
    Route::get('/contacts/tags', 'tags')->name('contacts.tags');
    Route::get('/contacts/opt-out', 'optOutLists')->name('contacts.opt-out');
    
    Route::get('/reporting', 'reporting')->name('reporting');
    Route::get('/reporting/dashboard', 'reportingDashboard')->name('reporting.dashboard');
    Route::get('/reporting/message-log', 'messageLog')->name('reporting.message-log');
    Route::get('/reporting/finance-data', 'financeData')->name('reporting.finance-data');
    Route::get('/reporting/invoices', 'invoices')->name('reporting.invoices');
    Route::get('/reporting/download-area', 'downloadArea')->name('reporting.download-area');
    
    Route::get('/purchase', 'purchase')->name('purchase');
    Route::get('/purchase/messages', 'purchaseMessages')->name('purchase.messages');
    Route::get('/purchase/numbers', 'purchaseNumbers')->name('purchase.numbers');
    
    Route::get('/management', 'management')->name('management');
    Route::get('/management/rcs-agent', 'rcsAgentRegistrations')->name('management.rcs-agent');
    Route::get('/management/rcs-agent/create', 'rcsAgentCreate')->name('management.rcs-agent.create');
    Route::get('/management/sms-sender-id', 'smsSenderIdRegistration')->name('management.sms-sender-id');
    Route::get('/management/sms-sender-id/register', 'smsSenderIdRegister')->name('management.sms-sender-id.register');
    Route::get('/management/templates', 'templates')->name('management.templates');
    Route::get('/management/api-connections', 'apiConnections')->name('management.api-connections');
    Route::get('/management/api-connections/create', 'apiConnectionCreate')->name('management.api-connections.create');
    Route::get('/management/email-to-sms', 'emailToSms')->name('management.email-to-sms');
    Route::get('/management/email-to-sms/create-mapping', 'emailToSmsCreateMapping')->name('management.email-to-sms.create-mapping');
    Route::get('/management/email-to-sms/standard/create', 'emailToSmsStandardCreate')->name('management.email-to-sms.standard.create');
    Route::get('/management/email-to-sms/standard/{id}/edit', 'emailToSmsStandardEdit')->name('management.email-to-sms.standard.edit');
    Route::get('/management/numbers', 'numbers')->name('management.numbers');
    Route::get('/management/numbers/configure', 'numbersConfigure')->name('management.numbers.configure');
    
    Route::get('/account', 'account')->name('account');
    Route::get('/account/details', 'accountDetails')->name('account.details');
    Route::get('/account/activate', 'accountActivate')->name('account.activate');
    Route::get('/account/users', 'usersAndAccess')->name('account.users');
    Route::get('/account/sub-accounts', 'subAccounts')->name('account.sub-accounts');
    Route::get('/account/sub-accounts/{id}', 'subAccountDetail')->name('account.sub-accounts.detail');
    Route::get('/account/sub-accounts/{subId}/users/{userId}', 'userDetail')->name('account.users.detail');
    Route::get('/account/audit-logs', 'auditLogs')->name('account.audit-logs');
    Route::get('/account/security', 'securitySettings')->name('account.security');
    
    Route::get('/support', 'support')->name('support');
    Route::get('/support/dashboard', 'supportDashboard')->name('support.dashboard');
    Route::get('/support/create-ticket', 'createTicket')->name('support.create-ticket');
    Route::get('/support/knowledge-base', 'knowledgeBase')->name('support.knowledge-base');
    Route::get('/support/knowledge-base/test-mode', 'knowledgeBaseTestMode')->name('support.knowledge-base.test-mode');
    
    Route::get('/rcs/preview-demo', 'rcsPreviewDemo')->name('rcs.preview-demo');
});

Route::prefix('api/rcs/assets')->controller(RcsAssetController::class)->group(function () {
    Route::post('/process-url', 'processUrl')->name('api.rcs.assets.process-url');
    Route::post('/process-upload', 'processUpload')->name('api.rcs.assets.process-upload');
    Route::put('/{uuid}', 'updateAsset')->name('api.rcs.assets.update');
    Route::post('/{uuid}/finalize', 'finalizeAsset')->name('api.rcs.assets.finalize');
});

Route::prefix('api/purchase')->controller(QuickSMSController::class)->group(function () {
    Route::get('/numbers/pricing', 'getNumbersPricing')->name('api.purchase.numbers.pricing');
    Route::post('/numbers/lock', 'lockNumbersForPurchase')->name('api.purchase.numbers.lock');
    Route::post('/numbers/purchase', 'processNumbersPurchase')->name('api.purchase.numbers.purchase');
    Route::post('/numbers/release', 'releaseNumberLocks')->name('api.purchase.numbers.release');
});

Route::prefix('admin')->group(function () {
    Route::controller(\App\Http\Controllers\AdminAuthController::class)->group(function () {
        Route::get('/login', 'showLogin')->name('admin.login');
        Route::post('/login', 'login')->name('admin.login.submit');
        Route::get('/logout', 'logout')->name('admin.logout');
        Route::get('/mfa/setup', 'showMfaSetup')->name('admin.mfa.setup');
        Route::post('/mfa/setup', 'completeMfaSetup')->name('admin.mfa.setup.complete');
        Route::post('/mfa/setup/skip', 'skipMfaSetup')->name('admin.mfa.setup.skip');
        Route::get('/mfa/verify', 'showMfaVerify')->name('admin.mfa.verify');
        Route::post('/mfa/verify', 'verifyMfa')->name('admin.mfa.verify.submit');
    });
    
    Route::middleware([\App\Http\Middleware\AdminIpAllowlist::class, \App\Http\Middleware\AdminAuthenticate::class])
        ->controller(\App\Http\Controllers\AdminController::class)
        ->group(function () {
            Route::get('/', 'dashboard')->name('admin.dashboard');
            Route::get('/approval-queue', 'approvalQueue')->name('admin.approval-queue');
            
            Route::get('/accounts/overview', 'accountsOverview')->name('admin.accounts.overview');
            Route::get('/accounts/sub-accounts', 'accountsSubAccounts')->name('admin.accounts.sub-accounts');
            Route::get('/accounts/balances', 'accountsBalances')->name('admin.accounts.balances');
            Route::get('/accounts/details/{accountId}', 'accountsDetails')->name('admin.accounts.details');
            
            Route::get('/reporting/message-log', 'reportingMessageLog')->name('admin.reporting.message-log');
            Route::get('/reporting/client', 'reportingClient')->name('admin.reporting.client');
            Route::get('/reporting/supplier', 'reportingSupplier')->name('admin.reporting.supplier');
            Route::get('/reporting/finance', 'reportingFinance')->name('admin.reporting.finance');
            
            Route::get('/campaigns/active', 'campaignsActive')->name('admin.campaigns.active');
            Route::get('/campaigns/approvals', 'campaignsApprovals')->name('admin.campaigns.approvals');
            Route::get('/campaigns/blocked', 'campaignsBlocked')->name('admin.campaigns.blocked');
            
            Route::get('/assets/sender-ids', 'assetsSenderIds')->name('admin.assets.sender-ids');
            Route::get('/assets/sender-ids/{id}', 'assetsSenderIdDetail')->name('admin.assets.sender-id.detail');
            Route::get('/assets/rcs-agents', 'assetsRcsAgents')->name('admin.assets.rcs-agents');
            Route::get('/assets/rcs-agents/{id}', 'assetsRcsAgentDetail')->name('admin.assets.rcs-agent.detail');
            Route::get('/assets/templates', 'assetsTemplates')->name('admin.assets.templates');
            Route::get('/assets/numbers', 'assetsNumbers')->name('admin.assets.numbers');
            Route::get('/assets/numbers/{id}/configure', 'assetsNumberConfigure')->name('admin.assets.number.configure');
            Route::get('/assets/email-to-sms', 'assetsEmailToSms')->name('admin.assets.email-to-sms');
            
            Route::get('/api/connections', 'apiConnections')->name('admin.api.connections');
            Route::get('/api/callbacks', 'apiCallbacks')->name('admin.api.callbacks');
            Route::get('/api/health', 'apiHealth')->name('admin.api.health');
            
            Route::get('/billing/invoices', 'billingInvoices')->name('admin.billing.invoices');
            Route::get('/billing/payments', 'billingPayments')->name('admin.billing.payments');
            Route::get('/billing/credits', 'billingCredits')->name('admin.billing.credits');
            
            Route::get('/security/audit-logs', 'securityAuditLogs')->name('admin.security.audit-logs');
            Route::get('/security/country-controls', 'securityCountryControls')->name('admin.security.country-controls');
            Route::get('/security/anti-spam', 'securityAntiSpam')->name('admin.security.anti-spam');
            Route::get('/security/ip-allowlists', 'securityIpAllowlists')->name('admin.security.ip-allowlists');
            
            Route::get('/system/pricing', 'systemPricing')->name('admin.system.pricing');
            Route::get('/system/routing', 'systemRouting')->name('admin.system.routing');
            Route::get('/system/flags', 'systemFlags')->name('admin.system.flags');
        });
});
