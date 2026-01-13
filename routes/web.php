<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuickSMSController;
use App\Http\Controllers\Api\RcsAssetController;

Route::controller(QuickSMSController::class)->group(function () {
    Route::get('/', 'dashboard')->name('dashboard');
    
    Route::get('/messages', 'messages')->name('messages');
    Route::get('/messages/send', 'sendMessage')->name('messages.send');
    Route::get('/messages/confirm', 'confirmCampaign')->name('messages.confirm');
    Route::get('/messages/inbox', 'inbox')->name('messages.inbox');
    Route::get('/messages/campaign-history', 'campaignHistory')->name('messages.campaign-history');
    
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
    Route::get('/account/users', 'usersAndAccess')->name('account.users');
    Route::get('/account/sub-accounts', 'subAccounts')->name('account.sub-accounts');
    Route::get('/account/audit-logs', 'auditLogs')->name('account.audit-logs');
    Route::get('/account/security', 'securitySettings')->name('account.security');
    
    Route::get('/support', 'support')->name('support');
    Route::get('/support/dashboard', 'supportDashboard')->name('support.dashboard');
    Route::get('/support/create-ticket', 'createTicket')->name('support.create-ticket');
    Route::get('/support/knowledge-base', 'knowledgeBase')->name('support.knowledge-base');
    
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
