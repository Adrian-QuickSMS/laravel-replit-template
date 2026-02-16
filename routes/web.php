<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuickSMSController;
use App\Http\Controllers\SenderIdController;
use App\Http\Controllers\Api\RcsAssetController;

// Public auth routes (no authentication required)
Route::controller(QuickSMSController::class)->group(function () {
    Route::get('/login', 'login')->name('auth.login');
    Route::post('/login', 'handleLogin')->name('auth.login.submit');
    Route::post('/login/verify-mfa', 'verifyMfa')->name('auth.mfa.verify');
    Route::post('/login/resend-mfa', 'resendMfa')->name('auth.mfa.resend');
    Route::match(['get', 'post'], '/logout', 'logout')->name('auth.logout');
    Route::get('/signup', 'signup')->name('auth.signup');
    Route::get('/signup/verify', 'verifyEmail')->name('auth.verify-email');
    Route::get('/signup/security', 'signupSecurity')->name('auth.security');
});

Route::middleware('customer.auth')->controller(QuickSMSController::class)->group(function () {
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
    Route::get('/management/sms-sender-id', [SenderIdController::class, 'index'])->name('management.sms-sender-id');
    Route::get('/management/sms-sender-id/register', [SenderIdController::class, 'create'])->name('management.sms-sender-id.register');
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
    Route::post('/my-profile/save', 'saveProfile')->name('my-profile.save');
    Route::post('/my-profile/change-password', 'changePassword')->name('my-profile.change-password');
    
    Route::get('/account', 'account')->name('account');
    Route::get('/account/details', 'accountDetails')->name('account.details');
    Route::get('/account/activate', 'accountActivate')->name('account.activate');
    Route::post('/account/activate', 'saveActivation')->name('account.activate.save');
    Route::post('/account/details/signup', 'saveSignUpDetails')->name('account.details.signup');
    Route::post('/account/details/company', 'saveCompanyInfo')->name('account.details.company');
    Route::post('/account/details/support', 'saveSupportOps')->name('account.details.support');
    Route::post('/account/details/signatory', 'saveSignatory')->name('account.details.signatory');
    Route::post('/account/details/vat', 'saveVatInfo')->name('account.details.vat');
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

Route::middleware('customer.auth')->prefix('api/sender-ids')->controller(SenderIdController::class)->group(function () {
    Route::get('/approved', 'approved')->name('api.sender-ids.approved');
    Route::post('/validate', 'validateSenderId')->name('api.sender-ids.validate');
    Route::post('/', 'store')->name('api.sender-ids.store');
    Route::get('/{uuid}', 'show')->name('api.sender-ids.show');
    Route::put('/{uuid}', 'update')->name('api.sender-ids.update');
    Route::delete('/{uuid}', 'destroy')->name('api.sender-ids.destroy');
    Route::post('/{uuid}/submit', 'submit')->name('api.sender-ids.submit');
    Route::post('/{uuid}/provide-info', 'provideInfo')->name('api.sender-ids.provide-info');
    Route::post('/{uuid}/resubmit', 'resubmit')->name('api.sender-ids.resubmit');
});

Route::middleware('customer.auth')->post('/api/sub-accounts/users', [SenderIdController::class, 'subAccountUsers'])->name('api.sub-accounts.users');

Route::middleware('customer.auth')->prefix('api/notifications')->controller(\App\Http\Controllers\NotificationController::class)->group(function () {
    Route::get('/', 'index')->name('api.notifications.index');
    Route::post('/{uuid}/read', 'markRead')->name('api.notifications.read');
    Route::post('/{uuid}/dismiss', 'dismiss')->name('api.notifications.dismiss');
});

Route::middleware('customer.auth')->prefix('api/contacts')->controller(\App\Http\Controllers\Api\ContactBookApiController::class)->group(function () {
    Route::get('/', 'contactsIndex')->name('api.contacts.index');
    Route::post('/', 'contactsStore')->name('api.contacts.store');
    Route::get('/{id}', 'contactsShow')->name('api.contacts.show');
    Route::put('/{id}', 'contactsUpdate')->name('api.contacts.update');
    Route::delete('/{id}', 'contactsDestroy')->name('api.contacts.destroy');
    Route::post('/bulk/add-to-list', 'bulkAddToList')->name('api.contacts.bulk.add-to-list');
    Route::post('/bulk/remove-from-list', 'bulkRemoveFromList')->name('api.contacts.bulk.remove-from-list');
    Route::post('/bulk/add-tags', 'bulkAddTags')->name('api.contacts.bulk.add-tags');
    Route::post('/bulk/remove-tags', 'bulkRemoveTags')->name('api.contacts.bulk.remove-tags');
    Route::post('/bulk/delete', 'bulkDelete')->name('api.contacts.bulk.delete');
    Route::post('/bulk/export', 'bulkExport')->name('api.contacts.bulk.export');
    Route::get('/{id}/timeline', 'timeline')->name('api.contacts.timeline');
    Route::post('/{id}/reveal-msisdn', 'revealMsisdn')->name('api.contacts.reveal-msisdn');
});

Route::middleware('customer.auth')->prefix('api/tags')->controller(\App\Http\Controllers\Api\ContactBookApiController::class)->group(function () {
    Route::get('/', 'tagsIndex')->name('api.tags.index');
    Route::post('/', 'tagsStore')->name('api.tags.store');
    Route::put('/{id}', 'tagsUpdate')->name('api.tags.update');
    Route::delete('/{id}', 'tagsDestroy')->name('api.tags.destroy');
});

Route::middleware('customer.auth')->prefix('api/contact-lists')->controller(\App\Http\Controllers\Api\ContactBookApiController::class)->group(function () {
    Route::get('/', 'listsIndex')->name('api.contact-lists.index');
    Route::post('/', 'listsStore')->name('api.contact-lists.store');
    Route::put('/{id}', 'listsUpdate')->name('api.contact-lists.update');
    Route::delete('/{id}', 'listsDestroy')->name('api.contact-lists.destroy');
    Route::post('/{id}/members', 'listsAddMembers')->name('api.contact-lists.add-members');
    Route::delete('/{id}/members', 'listsRemoveMembers')->name('api.contact-lists.remove-members');
});

Route::middleware('customer.auth')->prefix('api/opt-out-lists')->controller(\App\Http\Controllers\Api\ContactBookApiController::class)->group(function () {
    Route::get('/', 'optOutListsIndex')->name('api.opt-out-lists.index');
    Route::post('/', 'optOutListsStore')->name('api.opt-out-lists.store');
    Route::put('/{id}', 'optOutListsUpdate')->name('api.opt-out-lists.update');
    Route::delete('/{id}', 'optOutListsDestroy')->name('api.opt-out-lists.destroy');
    Route::get('/{id}/records', 'optOutRecordsIndex')->name('api.opt-out-lists.records.index');
    Route::post('/{id}/records', 'optOutRecordsStore')->name('api.opt-out-lists.records.store');
});

Route::middleware('customer.auth')->delete('api/opt-out-records/{id}', [\App\Http\Controllers\Api\ContactBookApiController::class, 'optOutRecordsDestroy'])->name('api.opt-out-records.destroy');

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
        Route::post('/mfa/sms/send', 'sendSmsMfa')->name('admin.mfa.sms.send');
        Route::post('/mfa/sms/verify', 'verifySmsMfa')->name('admin.mfa.sms.verify');
        Route::get('/password/change', 'showPasswordChange')->name('admin.password.change');
        Route::post('/password/change', 'changePassword')->name('admin.password.change.submit');
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

            Route::prefix('api/admin-users')->controller(\App\Http\Controllers\Admin\AdminUserController::class)->group(function () {
                Route::get('/', 'index')->name('admin.api.admin-users.index');
                Route::post('/', 'store')->name('admin.api.admin-users.store');
                Route::get('/{id}', 'show')->name('admin.api.admin-users.show');
                Route::put('/{id}', 'update')->name('admin.api.admin-users.update');
                Route::post('/{id}/suspend', 'suspend')->name('admin.api.admin-users.suspend');
                Route::post('/{id}/activate', 'activate')->name('admin.api.admin-users.activate');
                Route::post('/{id}/unlock', 'unlock')->name('admin.api.admin-users.unlock');
                Route::post('/{id}/reset-mfa', 'resetMfa')->name('admin.api.admin-users.reset-mfa');
                Route::post('/{id}/resend-invite', 'resendInvite')->name('admin.api.admin-users.resend-invite');
                Route::post('/{id}/reset-password', 'resetPassword')->name('admin.api.admin-users.reset-password');
                Route::post('/{id}/force-logout', 'forceLogout')->name('admin.api.admin-users.force-logout');
                Route::post('/{id}/update-mfa', 'updateMfa')->name('admin.api.admin-users.update-mfa');
                Route::post('/{id}/update-email', 'updateEmail')->name('admin.api.admin-users.update-email');
                Route::delete('/{id}', 'destroy')->name('admin.api.admin-users.destroy');
            });

            Route::post('/enforcement/test', 'testEnforcement')->name('admin.enforcement.test');
            Route::post('/enforcement/normalise', 'normaliseInput')->name('admin.enforcement.normalise');
            Route::post('/enforcement/reload', 'reloadEnforcementRules')->name('admin.enforcement.reload');

            Route::prefix('api/enforcement')->controller(\App\Http\Controllers\Admin\EnforcementController::class)->group(function () {
                Route::get('/senderid-rules', 'senderidRulesIndex');
                Route::post('/senderid-rules', 'senderidRulesStore');
                Route::put('/senderid-rules/{id}', 'senderidRulesUpdate');
                Route::delete('/senderid-rules/{id}', 'senderidRulesDestroy');
                Route::patch('/senderid-rules/{id}/toggle', 'senderidRulesToggle');

                Route::get('/content-rules', 'contentRulesIndex');
                Route::post('/content-rules', 'contentRulesStore');
                Route::put('/content-rules/{id}', 'contentRulesUpdate');
                Route::delete('/content-rules/{id}', 'contentRulesDestroy');
                Route::patch('/content-rules/{id}/toggle', 'contentRulesToggle');

                Route::get('/url-rules', 'urlRulesIndex');
                Route::post('/url-rules', 'urlRulesStore');
                Route::put('/url-rules/{id}', 'urlRulesUpdate');
                Route::delete('/url-rules/{id}', 'urlRulesDestroy');
                Route::patch('/url-rules/{id}/toggle', 'urlRulesToggle');

                Route::get('/normalisation', 'normalisationIndex');
                Route::put('/normalisation/{id}', 'normalisationUpdate');
                Route::patch('/normalisation/{id}/toggle', 'normalisationToggle');

                Route::get('/exemptions', 'exemptionsIndex');
                Route::post('/exemptions', 'exemptionsStore');
                Route::put('/exemptions/{id}', 'exemptionsUpdate');
                Route::delete('/exemptions/{id}', 'exemptionsDestroy');
                Route::patch('/exemptions/{id}/toggle', 'exemptionsToggle');

                Route::get('/quarantine', 'quarantineIndex');
                Route::get('/quarantine/{id}', 'quarantineShow');
                Route::post('/quarantine/{id}/release', 'quarantineRelease');
                Route::post('/quarantine/{id}/block', 'quarantineBlock');

                Route::get('/settings', 'settingsIndex');
                Route::put('/settings/{key}', 'settingsUpdate')->where('key', '[a-zA-Z0-9_.]+');

                Route::get('/domain-age-cache', 'domainAgeCacheIndex');
                Route::delete('/domain-age-cache/{id}', 'domainAgeCacheDestroy');
            });
            
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
            
            Route::prefix('api/notifications')->controller(\App\Http\Controllers\Admin\AdminNotificationController::class)->group(function () {
                Route::get('/', 'index')->name('admin.api.notifications.index');
                Route::post('/mark-all-read', 'markAllRead')->name('admin.api.notifications.markAllRead');
                Route::post('/{uuid}/read', 'markRead')->name('admin.api.notifications.read');
            });

            Route::prefix('api/sender-ids')->controller(\App\Http\Controllers\Admin\SenderIdApprovalController::class)->group(function () {
                Route::get('/', 'index')->name('admin.api.sender-ids.index');
                Route::get('/{uuid}', 'show')->name('admin.api.sender-ids.show');
                Route::post('/{uuid}/review', 'startReview')->name('admin.api.sender-ids.review');
                Route::post('/{uuid}/approve', 'approve')->name('admin.api.sender-ids.approve');
                Route::post('/{uuid}/reject', 'reject')->name('admin.api.sender-ids.reject');
                Route::post('/{uuid}/request-info', 'requestInfo')->name('admin.api.sender-ids.request-info');
                Route::post('/{uuid}/suspend', 'suspend')->name('admin.api.sender-ids.suspend');
                Route::post('/{uuid}/reactivate', 'reactivate')->name('admin.api.sender-ids.reactivate');
                Route::post('/{uuid}/revoke', 'revoke')->name('admin.api.sender-ids.revoke');
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
