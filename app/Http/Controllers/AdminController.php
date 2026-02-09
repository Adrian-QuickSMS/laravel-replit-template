<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Admin\ImpersonationService;
use App\Services\Admin\AdminLoginPolicyService;
use App\Services\Admin\AdminAuditService;
use App\Services\Admin\MessageEnforcementService;
use Illuminate\Support\Facades\Log;
use App\Models\MccMnc;
use App\Models\Gateway;
use App\Models\Supplier;
use App\Models\RateCard;
use App\Models\RoutingRule;
use App\Models\RoutingGatewayWeight;
use App\Models\RoutingCustomerOverride;

class AdminController extends Controller
{
    protected ImpersonationService $impersonationService;
    protected AdminLoginPolicyService $loginPolicyService;
    protected MessageEnforcementService $enforcementService;
    
    public function __construct(
        ImpersonationService $impersonationService, 
        AdminLoginPolicyService $loginPolicyService,
        MessageEnforcementService $enforcementService
    ) {
        $this->impersonationService = $impersonationService;
        $this->loginPolicyService = $loginPolicyService;
        $this->enforcementService = $enforcementService;
    }
    
    public function startImpersonation(Request $request)
    {
        $request->validate([
            'target_user_id' => 'required|string',
            'duration_minutes' => 'required|integer|in:15,30,60,120',
            'reason' => 'required|string|min:10',
        ]);
        
        $adminEmail = session('admin_email', 'admin@quicksms.co.uk');
        
        if (!$this->impersonationService->canImpersonate($adminEmail)) {
            Log::warning('[AdminController] Unauthorized impersonation attempt', [
                'admin_email' => $adminEmail,
                'target_user_id' => $request->input('target_user_id'),
            ]);
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        try {
            $result = $this->impersonationService->startSession(
                $adminEmail,
                $request->input('target_user_id'),
                $request->input('duration_minutes'),
                $request->input('reason')
            );
            
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }
    
    public function endImpersonation(Request $request)
    {
        $sessionId = $request->input('session_id');
        
        if (!$sessionId) {
            $session = $this->impersonationService->getCurrentSession();
            $sessionId = $session ? $session['session_id'] : null;
        }
        
        if (!$sessionId) {
            return response()->json(['error' => 'No active session'], 400);
        }
        
        $result = $this->impersonationService->endSession($sessionId, 'manual');
        return response()->json($result);
    }
    
    public function getImpersonationStatus()
    {
        $session = $this->impersonationService->getCurrentSession();
        
        if (!$session) {
            return response()->json([
                'active' => false,
            ]);
        }
        
        return response()->json([
            'active' => true,
            'session' => $session,
            'remaining_seconds' => $this->impersonationService->getRemainingTime(),
            'pii_masked' => $this->impersonationService->isPiiMasked(),
        ]);
    }
    
    public function validateLoginPolicy(Request $request)
    {
        $email = $request->input('email', '');
        $ipAddress = $request->ip();
        
        $result = $this->loginPolicyService->validateLoginPolicy($email, $ipAddress);
        
        if (!$result['allowed']) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        return response()->json([
            'allowed' => true,
            'mfa_required' => $result['mfa_required'],
            'allowed_mfa_methods' => $result['allowed_mfa_methods'],
        ]);
    }
    
    public function logAdminUserEvent(Request $request)
    {
        $request->validate([
            'event_type' => 'required|string',
            'target_admin_email' => 'required|string',
        ]);
        
        $actorAdmin = session('admin_email', 'admin@quicksms.co.uk');
        $eventType = $request->input('event_type');
        $targetEmail = $request->input('target_admin_email');
        $beforeValues = $request->input('before_values');
        $afterValues = $request->input('after_values');
        $reason = $request->input('reason');
        
        switch ($eventType) {
            case 'ADMIN_USER_INVITED':
                $role = $afterValues['role'] ?? 'Internal Support';
                AdminAuditService::logUserInvited($actorAdmin, $targetEmail, $role);
                break;
                
            case 'ADMIN_USER_INVITE_RESENT':
                AdminAuditService::logInviteResent($actorAdmin, $targetEmail);
                break;
                
            case 'ADMIN_USER_ACTIVATED':
                AdminAuditService::logUserActivated($actorAdmin, $targetEmail);
                break;
                
            case 'ADMIN_USER_SUSPENDED':
                $previousStatus = $beforeValues['status'] ?? 'Active';
                AdminAuditService::logUserSuspended($actorAdmin, $targetEmail, $previousStatus, $reason ?? 'No reason provided');
                break;
                
            case 'ADMIN_USER_REACTIVATED':
                AdminAuditService::logUserReactivated($actorAdmin, $targetEmail, $reason ?? 'No reason provided');
                break;
                
            case 'ADMIN_USER_ARCHIVED':
                $previousStatus = $beforeValues['status'] ?? 'Unknown';
                AdminAuditService::logUserArchived($actorAdmin, $targetEmail, $previousStatus, $reason ?? 'No reason provided');
                break;
                
            case 'ADMIN_USER_PASSWORD_RESET':
                AdminAuditService::logPasswordReset($actorAdmin, $targetEmail);
                break;
                
            case 'ADMIN_USER_MFA_RESET':
                $tempDisable = $afterValues['temporary_disable'] ?? false;
                $disableHours = $afterValues['disable_hours'] ?? null;
                AdminAuditService::logMfaReset($actorAdmin, $targetEmail, $tempDisable, $disableHours);
                break;
                
            case 'ADMIN_USER_MFA_UPDATED':
                $prevMethod = $beforeValues['mfa_method'] ?? 'Unknown';
                $newMethod = $afterValues['mfa_method'] ?? 'Unknown';
                $newPhone = $afterValues['mfa_phone'] ?? null;
                AdminAuditService::logMfaUpdated($actorAdmin, $targetEmail, $prevMethod, $newMethod, $newPhone);
                break;
                
            case 'ADMIN_USER_EMAIL_UPDATED':
                $prevEmail = $beforeValues['email'] ?? '';
                $newEmail = $afterValues['email'] ?? '';
                AdminAuditService::logEmailUpdated($actorAdmin, $targetEmail, $prevEmail, $newEmail, $reason ?? 'No reason provided');
                break;
                
            case 'ADMIN_USER_SESSIONS_REVOKED':
                $sessionsRevoked = $afterValues['sessions_revoked'] ?? 1;
                AdminAuditService::logSessionsRevoked($actorAdmin, $targetEmail, $sessionsRevoked);
                break;
                
            default:
                AdminAuditService::logAdminUserEvent($eventType, $actorAdmin, $targetEmail, $beforeValues, $afterValues, $reason);
        }
        
        return response()->json(['success' => true, 'event_logged' => $eventType]);
    }

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

    public function securityComplianceControls()
    {
        return view('admin.security.security-compliance-controls', [
            'page_title' => 'Security & Compliance Controls'
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

    public function systemRouting(Request $request)
    {
        $tab = $request->get('tab', 'uk');

        $viewMap = [
            'uk' => 'admin.routing-rules.uk-routes',
            'international' => 'admin.routing-rules.international-routes',
            'overrides' => 'admin.routing-rules.customer-overrides',
        ];

        $view = $viewMap[$tab] ?? $viewMap['uk'];
        $data = ['page_title' => 'Routing Rules'];

        $gateways = Gateway::active()->with('supplier')->get();
        $productTypes = RateCard::active()->distinct()->pluck('product_type')->filter()->values();

        if ($tab === 'uk' || !isset($viewMap[$tab])) {
            $data = array_merge($data, $this->getUkRoutingData($gateways, $productTypes));
        } elseif ($tab === 'international') {
            $data = array_merge($data, $this->getInternationalRoutingData($gateways, $productTypes));
        } elseif ($tab === 'overrides') {
            $data = array_merge($data, $this->getOverridesData($gateways));
        }

        return view($view, $data);
    }

    private function getUkRoutingData($gateways, $productTypes)
    {
        $ukMccMnc = MccMnc::where('country_iso', 'GB')
            ->where('active', true)
            ->orderBy('network_name')
            ->get();

        $ukRateCards = RateCard::active()
            ->whereIn('mcc', ['234', '235'])
            ->where('country_iso', 'GB')
            ->with(['gateway.supplier'])
            ->get();

        $ratesByNetwork = $ukRateCards->groupBy(function ($rc) {
            return $rc->mcc . '-' . $rc->mnc;
        });

        $ukRoutingRules = RoutingRule::where('country_iso', 'GB')
            ->whereNull('deleted_at')
            ->with('gatewayWeights')
            ->get()
            ->keyBy(function ($rule) {
                return $rule->mcc . '-' . $rule->mnc;
            });

        $ukNetworks = $ukMccMnc->map(function ($network) use ($ratesByNetwork, $ukRoutingRules, $gateways) {
            $key = $network->mcc . '-' . $network->mnc;
            $rates = $ratesByNetwork->get($key, collect());
            $routingRule = $ukRoutingRules->get($key);

            $networkGateways = $rates->groupBy('gateway_id')->map(function ($gwRates) use ($routingRule) {
                $rate = $gwRates->sortBy('gbp_rate')->first();
                $gateway = $rate->gateway;
                $supplier = $gateway ? $gateway->supplier : null;

                $weight = null;
                $isPrimary = false;
                $gwStatus = 'active';

                if ($routingRule) {
                    $weightEntry = $routingRule->gatewayWeights
                        ->where('gateway_id', $rate->gateway_id)
                        ->first();
                    if ($weightEntry) {
                        $weight = $weightEntry->weight;
                        $isPrimary = $weightEntry->priority_order === 1 && !$weightEntry->is_fallback;
                        $gwStatus = $weightEntry->status ?? 'active';
                    }
                }

                return [
                    'name' => $gateway ? $gateway->name : 'Unknown',
                    'supplier' => $supplier ? $supplier->name : 'Unknown',
                    'code' => $gateway ? $gateway->gateway_code : '',
                    'weight' => $weight,
                    'primary' => $isPrimary,
                    'status' => $gwStatus === 'active' ? 'online' : $gwStatus,
                    'rate' => number_format($rate->gbp_rate, 4),
                    'billing' => ucfirst($rate->billing_method ?? 'delivered'),
                ];
            })->values();

            if ($networkGateways->isNotEmpty() && !$networkGateways->contains('primary', true)) {
                $networkGateways = $networkGateways->map(function ($gw, $index) {
                    if ($index === 0) {
                        $gw['primary'] = true;
                    }
                    return $gw;
                });
            }

            $primaryGw = $networkGateways->firstWhere('primary', true);
            $cheapestRate = $rates->min('gbp_rate');

            return [
                'id' => $network->id,
                'network' => $network->network_name,
                'prefix' => $network->country_prefix ? '+' . $network->country_prefix : $network->mcc . '/' . $network->mnc,
                'mcc' => $network->mcc,
                'mnc' => $network->mnc,
                'gateway_count' => $networkGateways->count(),
                'primary_gw' => $primaryGw ? $primaryGw['name'] : '—',
                'billing' => $primaryGw ? $primaryGw['billing'] : '—',
                'rate' => $cheapestRate !== null ? number_format($cheapestRate, 4) : '—',
                'status' => $routingRule ? $routingRule->status : 'active',
                'gateways' => $networkGateways->toArray(),
            ];
        });

        return [
            'ukNetworks' => $ukNetworks,
            'gateways' => $gateways,
            'productTypes' => $productTypes,
        ];
    }

    private function getInternationalRoutingData($gateways, $productTypes)
    {
        $intlRates = RateCard::active()
            ->where('country_iso', '!=', 'GB')
            ->with(['gateway.supplier'])
            ->get();

        $byCountry = $intlRates->groupBy('country_iso');

        $intlRoutingRules = RoutingRule::where('country_iso', '!=', 'GB')
            ->whereNotNull('country_iso')
            ->whereNull('deleted_at')
            ->with('gatewayWeights')
            ->get()
            ->keyBy('country_iso');

        $countries = $byCountry->map(function ($rates, $iso) use ($intlRoutingRules) {
            $countryName = $rates->first()->country_name;
            $routingRule = $intlRoutingRules->get($iso);

            $countryGateways = $rates->groupBy('gateway_id')->map(function ($gwRates) use ($routingRule) {
                $rate = $gwRates->sortBy('gbp_rate')->first();
                $gateway = $rate->gateway;
                $supplier = $gateway ? $gateway->supplier : null;

                $weight = null;
                $isPrimary = false;
                $gwStatus = 'active';

                if ($routingRule) {
                    $weightEntry = $routingRule->gatewayWeights
                        ->where('gateway_id', $rate->gateway_id)
                        ->first();
                    if ($weightEntry) {
                        $weight = $weightEntry->weight;
                        $isPrimary = $weightEntry->priority_order === 1 && !$weightEntry->is_fallback;
                        $gwStatus = $weightEntry->status ?? 'active';
                    }
                }

                return [
                    'name' => $gateway ? $gateway->name : 'Unknown',
                    'supplier' => $supplier ? $supplier->name : 'Unknown',
                    'code' => $gateway ? $gateway->gateway_code : '',
                    'weight' => $weight,
                    'primary' => $isPrimary,
                    'status' => $gwStatus === 'active' ? 'online' : $gwStatus,
                    'rate' => number_format($rate->gbp_rate, 4),
                    'billing' => ucfirst($rate->billing_method ?? 'delivered'),
                ];
            })->values();

            if ($countryGateways->isNotEmpty() && !$countryGateways->contains('primary', true)) {
                $countryGateways = $countryGateways->map(function ($gw, $index) {
                    if ($index === 0) {
                        $gw['primary'] = true;
                    }
                    return $gw;
                });
            }

            $primaryGw = $countryGateways->firstWhere('primary', true);
            $cheapestRate = $rates->min('gbp_rate');

            return [
                'country' => $countryName,
                'iso' => $iso,
                'letter' => strtoupper(substr($countryName, 0, 1)),
                'gateway_count' => $countryGateways->count(),
                'primary_gw' => $primaryGw ? $primaryGw['name'] : '—',
                'billing' => $primaryGw ? $primaryGw['billing'] : '—',
                'rate' => $cheapestRate !== null ? number_format($cheapestRate, 4) : '—',
                'status' => $routingRule ? $routingRule->status : 'active',
                'gateways' => $countryGateways->toArray(),
            ];
        })->sortBy('country')->values();

        $activeLetters = $countries->pluck('letter')->unique()->sort()->values();

        return [
            'countries' => $countries,
            'activeLetters' => $activeLetters,
            'gateways' => $gateways,
            'productTypes' => $productTypes,
        ];
    }

    private function getOverridesData($gateways)
    {
        $overrides = RoutingCustomerOverride::whereNull('deleted_at')
            ->with(['forcedGateway.supplier', 'blockedGateway.supplier', 'routingRule'])
            ->orderByDesc('created_at')
            ->get();

        $ukNetworks = MccMnc::where('country_iso', 'GB')
            ->where('active', true)
            ->orderBy('network_name')
            ->get();

        $countries = RateCard::active()
            ->where('country_iso', '!=', 'GB')
            ->select('country_iso', 'country_name')
            ->distinct()
            ->orderBy('country_name')
            ->get();

        return [
            'overrides' => $overrides,
            'gateways' => $gateways,
            'ukNetworks' => $ukNetworks,
            'countries' => $countries,
        ];
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
    
    /**
     * Test enforcement rules against input
     * 
     * POST /admin/enforcement/test
     * 
     * This endpoint uses the SAME shared enforcement logic as production.
     * It normalises the input and evaluates it against the selected engine's rules.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function testEnforcement(Request $request)
    {
        $request->validate([
            'engine' => 'required|string|in:senderid,content,url',
            'input' => 'required|string|max:1000',
        ]);
        
        $engine = $request->input('engine');
        $input = $request->input('input');
        
        try {
            $result = $this->enforcementService->testEnforcement($engine, $input);
            
            Log::info('[AdminController] Enforcement test executed', [
                'engine' => $engine,
                'input' => $input,
                'result' => $result['result'],
                'admin_email' => session('admin_email', 'unknown'),
            ]);
            
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('[AdminController] Enforcement test failed', [
                'engine' => $engine,
                'input' => $input,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'error' => 'Enforcement test failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Normalise input only (without rule evaluation)
     * 
     * POST /admin/enforcement/normalise
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function normaliseInput(Request $request)
    {
        $request->validate([
            'input' => 'required|string|max:1000',
        ]);
        
        $input = $request->input('input');
        
        try {
            $result = $this->enforcementService->normalise($input);
            
            return response()->json([
                'normalised' => $result['normalised'],
                'mappingHits' => $result['mappingHits'],
            ]);
        } catch (\Exception $e) {
            Log::error('[AdminController] Normalisation failed', [
                'input' => $input,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'error' => 'Normalisation failed',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Hot reload enforcement rules
     * 
     * POST /admin/enforcement/reload
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reloadEnforcementRules(Request $request)
    {
        try {
            $this->enforcementService->hotReloadRules();
            
            Log::info('[AdminController] Enforcement rules reloaded', [
                'admin_email' => session('admin_email', 'unknown'),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Enforcement rules reloaded successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to reload rules',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
