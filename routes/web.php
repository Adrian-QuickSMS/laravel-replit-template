<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuickSMSController;
use App\Http\Controllers\Api\RcsAssetController;

// Public auth routes (no authentication required)
Route::controller(QuickSMSController::class)->group(function () {
    Route::get('/login', 'login')->name('auth.login');
    Route::post('/login', 'handleLogin')->name('auth.login.submit');
    Route::get('/logout', 'logout')->name('auth.logout');
    Route::get('/signup', 'signup')->name('auth.signup');
    Route::get('/signup/verify', 'verifyEmail')->name('auth.verify-email');
    Route::get('/signup/security', 'signupSecurity')->name('auth.security');
});

Route::controller(QuickSMSController::class)->group(function () {
    Route::get('/', 'dashboard')->name('dashboard');
    
    Route::get('/messages', 'messages')->name('messages');
    Route::get('/messages/send', 'sendMessage')->name('messages.send');
    Route::post('/messages/store-campaign-config', 'storeCampaignConfig')->name('messages.store-campaign-config');
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
    Route::get('/management/templates/create', 'templateCreateStep1')->name('management.templates.create');
    Route::get('/management/templates/create/step1', 'templateCreateStep1')->name('management.templates.create.step1');
    Route::get('/management/templates/create/step2', 'templateCreateStep2')->name('management.templates.create.step2');
    Route::get('/management/templates/create/step3', 'templateCreateStep3')->name('management.templates.create.step3');
    Route::get('/management/templates/create/review', 'templateCreateReview')->name('management.templates.create.review');
    Route::get('/management/templates/{templateId}/edit', 'templateEditStep1')->name('management.templates.edit');
    Route::get('/management/templates/{templateId}/edit/step1', 'templateEditStep1')->name('management.templates.edit.step1');
    Route::get('/management/templates/{templateId}/edit/step2', 'templateEditStep2')->name('management.templates.edit.step2');
    Route::get('/management/templates/{templateId}/edit/step3', 'templateEditStep3')->name('management.templates.edit.step3');
    Route::get('/management/templates/{templateId}/edit/review', 'templateEditReview')->name('management.templates.edit.review');
    Route::get('/management/api-connections', 'apiConnections')->name('management.api-connections');
    Route::get('/management/api-connections/create', 'apiConnectionCreate')->name('management.api-connections.create');
    Route::get('/management/email-to-sms', 'emailToSms')->name('management.email-to-sms');
    Route::get('/management/email-to-sms/create-mapping', 'emailToSmsCreateMapping')->name('management.email-to-sms.create-mapping');
    Route::get('/management/email-to-sms/standard/create', 'emailToSmsStandardCreate')->name('management.email-to-sms.standard.create');
    Route::get('/management/email-to-sms/standard/{id}/edit', 'emailToSmsStandardEdit')->name('management.email-to-sms.standard.edit');
    Route::get('/management/email-to-sms/contact-list/{id}/edit', 'emailToSmsContactListEdit')->name('management.email-to-sms.contact-list.edit');
    Route::get('/management/numbers', 'numbers')->name('management.numbers');
    Route::get('/management/numbers/configure', 'numbersConfigure')->name('management.numbers.configure');
    
    Route::get('/my-profile', 'myProfile')->name('my-profile');
    
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
    Route::post('/proxy-image', 'proxyImage')->name('api.rcs.assets.proxy-image');
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
            Route::get('/accounts/{accountId}/billing', 'accountsBilling')->name('admin.accounts.billing');
            
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
            
            Route::get('/management/templates', 'managementTemplates')->name('admin.management.templates');
            Route::get('/management/templates/{accountId}/{templateId}/edit', function($accountId, $templateId) {
                return redirect()->route('admin.management.templates.edit.step1', ['accountId' => $accountId, 'templateId' => $templateId]);
            })->name('admin.management.templates.edit');
            Route::get('/management/templates/{accountId}/{templateId}/edit/step1', 'adminTemplateEditStep1')->name('admin.management.templates.edit.step1');
            Route::get('/management/templates/{accountId}/{templateId}/edit/step2', 'adminTemplateEditStep2')->name('admin.management.templates.edit.step2');
            Route::get('/management/templates/{accountId}/{templateId}/edit/step3', 'adminTemplateEditStep3')->name('admin.management.templates.edit.step3');
            Route::get('/management/templates/{accountId}/{templateId}/edit/review', 'adminTemplateEditReview')->name('admin.management.templates.edit.review');
            Route::get('/assets/campaigns', 'assetsCampaigns')->name('admin.assets.campaigns');
            Route::get('/assets/numbers', 'assetsNumbers')->name('admin.assets.numbers');
            Route::get('/assets/numbers/{id}/configure', 'assetsNumberConfigure')->name('admin.assets.number.configure');
            Route::get('/assets/email-to-sms', 'assetsEmailToSms')->name('admin.assets.email-to-sms');
            Route::get('/assets/email-to-sms/standard/{id}/edit', 'assetsEmailToSmsStandardEdit')->name('admin.assets.email-to-sms.standard.edit');
            Route::get('/assets/email-to-sms/contact-list/{id}/edit', 'assetsEmailToSmsContactListEdit')->name('admin.assets.email-to-sms.contact-list.edit');
            
            Route::get('/api/connections', 'apiConnections')->name('admin.api.connections');
            Route::get('/api/connections/create', 'apiConnectionCreate')->name('admin.api.connections.create');
            Route::get('/api/callbacks', 'apiCallbacks')->name('admin.api.callbacks');
            Route::get('/api/health', 'apiHealth')->name('admin.api.health');
            
            Route::get('/billing/invoices', 'billingInvoices')->name('admin.billing.invoices');
            Route::get('/billing/payments', 'billingPayments')->name('admin.billing.payments');
            Route::get('/billing/credits', 'billingCredits')->name('admin.billing.credits');
            
            Route::get('/security/audit-logs', 'securityAuditLogs')->name('admin.security.audit-logs');
            Route::get('/security/country-controls', 'securityCountryControls')->name('admin.security.country-controls');
            Route::get('/security/security-compliance-controls', 'securityComplianceControls')->name('admin.security.security-compliance-controls');
            Route::get('/security/anti-spam', 'securityAntiSpam')->name('admin.security.anti-spam');
            Route::get('/security/ip-allowlists', 'securityIpAllowlists')->name('admin.security.ip-allowlists');
            Route::get('/security/admin-users', 'securityAdminUsers')->name('admin.security.admin-users');
            
            Route::post('/api/impersonation/start', 'startImpersonation')->name('admin.api.impersonation.start');
            Route::post('/api/impersonation/end', 'endImpersonation')->name('admin.api.impersonation.end');
            Route::get('/api/impersonation/status', 'getImpersonationStatus')->name('admin.api.impersonation.status');
            Route::post('/api/login-policy/validate', 'validateLoginPolicy')->name('admin.api.login-policy.validate');
            Route::post('/api/admin-users/audit', 'logAdminUserEvent')->name('admin.api.admin-users.audit');
            
            Route::post('/enforcement/test', 'testEnforcement')->name('admin.enforcement.test');
            Route::post('/enforcement/normalise', 'normaliseInput')->name('admin.enforcement.normalise');
            Route::post('/enforcement/reload', 'reloadEnforcementRules')->name('admin.enforcement.reload');
            
            Route::prefix('api/country-controls')->controller(\App\Http\Controllers\Admin\CountryControlController::class)->group(function () {
                Route::get('/', 'index')->name('admin.api.country-controls.index');
                Route::post('/update-status', 'updateStatus')->name('admin.api.country-controls.update-status');
                Route::post('/update-risk', 'updateRisk')->name('admin.api.country-controls.update-risk');
                Route::post('/bulk-update-status', 'bulkUpdateStatus')->name('admin.api.country-controls.bulk-update');
                Route::get('/{countryId}/overrides', 'getOverrides')->name('admin.api.country-controls.overrides');
                Route::post('/overrides', 'addOverride')->name('admin.api.country-controls.add-override');
                Route::delete('/overrides/{override}', 'deleteOverride')->name('admin.api.country-controls.delete-override');
            });

            Route::prefix('api/uk-network-controls')->controller(\App\Http\Controllers\Admin\UkNetworkControlController::class)->group(function () {
                Route::get('/', 'index')->name('admin.api.uk-network-controls.index');
                Route::post('/update-status', 'updateStatus')->name('admin.api.uk-network-controls.update-status');
                Route::post('/bulk-update-status', 'bulkUpdateStatus')->name('admin.api.uk-network-controls.bulk-update');
                Route::get('/{mccMncId}/overrides', 'getOverrides')->name('admin.api.uk-network-controls.overrides');
                Route::post('/overrides', 'addOverride')->name('admin.api.uk-network-controls.add-override');
                Route::put('/overrides/{override}', 'updateOverride')->name('admin.api.uk-network-controls.update-override');
                Route::delete('/overrides/{override}', 'deleteOverride')->name('admin.api.uk-network-controls.delete-override');
            });
            
            Route::prefix('api/governance')->controller(\App\Http\Controllers\Admin\ApprovalQueueController::class)->group(function () {
                Route::get('/queue-counts', 'getQueueCounts')->name('admin.api.governance.queue-counts');
                Route::get('/senderid-requests', 'getSenderIdRequests')->name('admin.api.governance.senderid-requests');
                Route::get('/rcs-agent-requests', 'getRcsAgentRequests')->name('admin.api.governance.rcs-agent-requests');
                Route::get('/country-requests', 'getCountryRequests')->name('admin.api.governance.country-requests');
                Route::post('/requests/{type}/{id}/status', 'updateRequestStatus')->name('admin.api.governance.update-request-status');
                Route::post('/entity/lock', 'applyEntityLock')->name('admin.api.governance.apply-lock');
                Route::post('/entity/unlock', 'removeEntityLock')->name('admin.api.governance.remove-lock');
                Route::get('/locked-entities', 'getLockedEntities')->name('admin.api.governance.locked-entities');
            });
            
            Route::get('/system/pricing', 'systemPricing')->name('admin.system.pricing');
            Route::get('/system/routing', 'systemRouting')->name('admin.system.routing');
            Route::get('/system/flags', 'systemFlags')->name('admin.system.flags');

            Route::post('/system/routing/add-gateway', 'routingAddGateway')->name('admin.routing.add-gateway');
            Route::post('/system/routing/change-weight', 'routingChangeWeight')->name('admin.routing.change-weight');
            Route::post('/system/routing/set-primary', 'routingSetPrimary')->name('admin.routing.set-primary');
            Route::post('/system/routing/toggle-block', 'routingToggleBlock')->name('admin.routing.toggle-block');
            Route::post('/system/routing/remove-gateway', 'routingRemoveGateway')->name('admin.routing.remove-gateway');
            Route::post('/system/routing/create-override', 'routingCreateOverride')->name('admin.routing.create-override');
            Route::post('/system/routing/cancel-override', 'routingCancelOverride')->name('admin.routing.cancel-override');
        });
});

require __DIR__ . '/../package/routes/supplier-management.php';
