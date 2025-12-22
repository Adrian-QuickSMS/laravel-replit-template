<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuickSMSController;

Route::controller(QuickSMSController::class)->group(function () {
    Route::get('/', 'dashboard')->name('dashboard');
    
    Route::get('/messages', 'messages')->name('messages');
    Route::get('/messages/send', 'sendMessage')->name('messages.send');
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
    
    Route::get('/management', 'management')->name('management');
    Route::get('/management/rcs-agent', 'rcsAgentRegistrations')->name('management.rcs-agent');
    Route::get('/management/sms-sender-id', 'smsSenderIdRegistration')->name('management.sms-sender-id');
    Route::get('/management/templates', 'templates')->name('management.templates');
    Route::get('/management/api-connections', 'apiConnections')->name('management.api-connections');
    Route::get('/management/email-to-sms', 'emailToSms')->name('management.email-to-sms');
    Route::get('/management/numbers', 'numbers')->name('management.numbers');
    
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
});
