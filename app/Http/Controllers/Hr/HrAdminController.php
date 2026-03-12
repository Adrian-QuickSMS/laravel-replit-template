<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\CompanyHrSettings;
use App\Models\Hr\EmployeeHrProfile;
use App\Models\Hr\LeaveAuditLog;
use App\Models\Hr\LeaveRequest;
use App\Services\Hr\IcsGeneratorService;
use App\Services\Hr\LeaveCalculationService;
use App\Services\Hr\LeaveRequestService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class HrAdminController extends Controller
{
    public function __construct(
        private LeaveCalculationService $calculator,
        private LeaveRequestService $leaveService,
        private IcsGeneratorService $icsService
    ) {}

    /**
     * Admin/Manager page - employee list, approval queue, entitlement editor.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $profile = $this->getManagerProfile($user);
        $year = (int) $request->get('year', date('Y'));

        $employees = EmployeeHrProfile::active()
            ->with('user')
            ->orderBy('created_at')
            ->get()
            ->map(function ($emp) use ($year) {
                $this->leaveService->ensureEntitlement($emp, $year);
                $emp->balance = $this->calculator->getBalanceSummary($emp, $year);
                return $emp;
            });

        $pendingRequests = LeaveRequest::pending()
            ->with('employee.user')
            ->orderBy('submitted_at')
            ->get();

        $settings = CompanyHrSettings::forTenant($user->tenant_id);

        return view('quicksms.hr.admin', compact('profile', 'employees', 'pendingRequests', 'year', 'settings'));
    }

    /**
     * Get employee list as JSON.
     */
    public function employees(Request $request)
    {
        $year = (int) $request->get('year', date('Y'));

        $employees = EmployeeHrProfile::active()
            ->with('user')
            ->orderBy('created_at')
            ->get()
            ->map(function ($emp) use ($year) {
                $this->leaveService->ensureEntitlement($emp, $year);
                $balance = $this->calculator->getBalanceSummary($emp, $year);
                return [
                    'id' => $emp->id,
                    'name' => $emp->full_name,
                    'email' => $emp->user->email ?? '',
                    'department' => $emp->department,
                    'start_date' => $emp->start_date->format('Y-m-d'),
                    'hr_role' => $emp->hr_role,
                    'balance' => $balance,
                ];
            });

        return response()->json(['status' => 'success', 'data' => $employees]);
    }

    /**
     * Create or update an employee HR profile.
     */
    public function storeEmployee(Request $request)
    {
        $user = $request->user();
        $actorProfile = $this->getManagerProfile($user);

        $validated = $request->validate([
            'user_id' => 'required|uuid|exists:users,id',
            'start_date' => 'required|date',
            'department' => 'nullable|string|max:100',
            'job_title' => 'nullable|string|max:150',
            'hr_role' => 'required|in:employee,manager,hr_admin',
            'manager_id' => 'nullable|uuid',
            'annual_entitlement_days' => 'nullable|numeric|min:0|max:365',
        ]);

        $profile = EmployeeHrProfile::where('user_id', $validated['user_id'])->first();

        if ($profile) {
            $profile->update([
                'start_date' => $validated['start_date'],
                'department' => $validated['department'] ?? null,
                'job_title' => $validated['job_title'] ?? null,
                'hr_role' => $validated['hr_role'],
                'manager_id' => $validated['manager_id'] ?? null,
            ]);

            LeaveAuditLog::record(
                $user->tenant_id,
                $actorProfile->id,
                LeaveAuditLog::ACTION_PROFILE_UPDATED,
                $profile->id,
            );
        } else {
            $profile = EmployeeHrProfile::create([
                'tenant_id' => $user->tenant_id,
                'user_id' => $validated['user_id'],
                'start_date' => $validated['start_date'],
                'department' => $validated['department'] ?? null,
                'job_title' => $validated['job_title'] ?? null,
                'hr_role' => $validated['hr_role'],
                'manager_id' => $validated['manager_id'] ?? null,
            ]);

            LeaveAuditLog::record(
                $user->tenant_id,
                $actorProfile->id,
                LeaveAuditLog::ACTION_PROFILE_CREATED,
                $profile->id,
            );
        }

        // Handle custom entitlement
        if (isset($validated['annual_entitlement_days']) && $validated['annual_entitlement_days'] !== null) {
            $units = (int) ($validated['annual_entitlement_days'] * 4);
            $this->leaveService->updateEntitlement($profile, (int) date('Y'), $units, $actorProfile);
        } else {
            $this->leaveService->ensureEntitlement($profile, (int) date('Y'));
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Employee HR profile saved.',
            'data' => ['id' => $profile->id],
        ]);
    }

    /**
     * Update annual leave entitlement.
     */
    public function updateEntitlement(Request $request, string $employeeId)
    {
        $user = $request->user();
        $actorProfile = $this->getManagerProfile($user);

        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:2100',
            'entitlement_days' => 'required|numeric|min:0|max:365',
            'note' => 'nullable|string|max:500',
        ]);

        $employee = EmployeeHrProfile::findOrFail($employeeId);
        $units = (int) ($validated['entitlement_days'] * 4);

        $entitlement = $this->leaveService->updateEntitlement(
            $employee,
            $validated['year'],
            $units,
            $actorProfile,
            $validated['note'] ?? null
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Entitlement updated.',
            'data' => [
                'units' => $entitlement->total_entitlement_units,
                'days' => $entitlement->total_entitlement_units / 4,
            ],
        ]);
    }

    /**
     * Approve a pending leave request.
     */
    public function approveRequest(Request $request, string $requestId)
    {
        $user = $request->user();
        $actorProfile = $this->getManagerProfile($user);

        $validated = $request->validate([
            'comment' => 'nullable|string|max:500',
        ]);

        $leaveRequest = LeaveRequest::findOrFail($requestId);

        try {
            $this->leaveService->approveRequest($leaveRequest, $actorProfile, $validated['comment'] ?? null);

            return response()->json([
                'status' => 'success',
                'message' => 'Leave request approved.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => collect($e->errors())->flatten()->first(),
            ], 422);
        }
    }

    /**
     * Reject a pending leave request.
     */
    public function rejectRequest(Request $request, string $requestId)
    {
        $user = $request->user();
        $actorProfile = $this->getManagerProfile($user);

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $leaveRequest = LeaveRequest::findOrFail($requestId);

        try {
            $this->leaveService->rejectRequest($leaveRequest, $actorProfile, $validated['reason'] ?? null);

            return response()->json([
                'status' => 'success',
                'message' => 'Leave request rejected.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => collect($e->errors())->flatten()->first(),
            ], 422);
        }
    }

    /**
     * Download ICS file for an approved leave request.
     */
    public function downloadIcs(Request $request, string $requestId)
    {
        $leaveRequest = LeaveRequest::with('employee.user')->findOrFail($requestId);

        if (!$leaveRequest->isApproved()) {
            abort(400, 'ICS can only be generated for approved leave requests.');
        }

        $settings = CompanyHrSettings::forTenant($request->user()->tenant_id);

        return $this->icsService->downloadResponse($leaveRequest, $settings->show_leave_type_in_notifications);
    }

    /**
     * Get audit log entries.
     */
    public function auditLog(Request $request)
    {
        $entries = LeaveAuditLog::orderByDesc('created_at')
            ->limit(100)
            ->get();

        return response()->json(['status' => 'success', 'data' => $entries]);
    }

    /**
     * Update company HR settings.
     */
    public function updateSettings(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'default_annual_entitlement_days' => 'required|numeric|min:0|max:365',
            'email_notifications_enabled' => 'boolean',
            'ics_generation_enabled' => 'boolean',
            'team_notification_email' => 'nullable|email|max:255',
            'show_leave_type_in_notifications' => 'boolean',
        ]);

        $settings = CompanyHrSettings::forTenant($user->tenant_id);
        $settings->update([
            'default_annual_entitlement_units' => (int) ($validated['default_annual_entitlement_days'] * 4),
            'email_notifications_enabled' => $validated['email_notifications_enabled'] ?? false,
            'ics_generation_enabled' => $validated['ics_generation_enabled'] ?? true,
            'team_notification_email' => $validated['team_notification_email'] ?? null,
            'show_leave_type_in_notifications' => $validated['show_leave_type_in_notifications'] ?? false,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'HR settings updated.',
        ]);
    }

    /**
     * Get the HR profile for the current user, asserting manager/admin role.
     */
    private function getManagerProfile($user): EmployeeHrProfile
    {
        $profile = EmployeeHrProfile::where('user_id', $user->id)->first();

        if (!$profile) {
            // Auto-create an hr_admin profile for account owners/admins
            if ($user->isAdmin() || $user->isOwner()) {
                $profile = EmployeeHrProfile::create([
                    'tenant_id' => $user->tenant_id,
                    'user_id' => $user->id,
                    'start_date' => $user->created_at->toDateString(),
                    'hr_role' => EmployeeHrProfile::ROLE_HR_ADMIN,
                ]);
            } else {
                abort(403, 'You do not have an HR profile. Please contact your administrator.');
            }
        }

        // Allow QuickSMS admins/owners to act as HR admins even if their HR role is 'employee'
        if ($user->isAdmin() || $user->isOwner()) {
            return $profile;
        }

        if (!$profile->isManager()) {
            abort(403, 'You do not have manager or admin access to HR.');
        }

        return $profile;
    }
}
