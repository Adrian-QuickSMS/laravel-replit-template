<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\Hr\BankHoliday;
use App\Models\Hr\EmployeeHrProfile;
use App\Models\Hr\HrSettings;
use App\Models\Hr\LeaveAuditLog;
use App\Models\Hr\LeaveRequest;
use App\Services\Hr\LeaveCalculationService;
use App\Services\Hr\LeaveRequestService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class HrController extends Controller
{
    public function __construct(
        private LeaveCalculationService $calculator,
        private LeaveRequestService $leaveService
    ) {}

    public function dashboard(Request $request)
    {
        $adminUser = $this->getAdminUser();
        if (!$adminUser->hasHrAccess()) {
            abort(403, 'You do not have access to the HR module.');
        }
        $year = (int) $request->get('year', date('Y'));
        $profile = $adminUser->hrProfile;

        $balance = null;
        $myPendingRequests = collect();
        $myRecentRequests = collect();

        if ($profile) {
            $this->leaveService->ensureEntitlement($profile, $year);
            $balance = $this->calculator->getBalanceSummary($profile, $year);

            $myPendingRequests = LeaveRequest::where('employee_id', $profile->id)
                ->pending()
                ->orderBy('start_date')
                ->limit(5)
                ->get();

            $myRecentRequests = LeaveRequest::where('employee_id', $profile->id)
                ->orderByDesc('submitted_at')
                ->limit(10)
                ->get();
        }

        $teamPendingRequests = collect();
        $isManagerOrAdmin = $adminUser->isHrManager();

        if ($isManagerOrAdmin) {
            $teamPendingRequests = LeaveRequest::pending()
                ->when($profile, fn($q) => $q->where('employee_id', '!=', $profile->id))
                ->with('employee.adminUser')
                ->orderBy('submitted_at')
                ->limit(20)
                ->get();
        }

        $upcomingAbsences = LeaveRequest::approved()
            ->where('end_date', '>=', today())
            ->with('employee.adminUser')
            ->orderBy('start_date')
            ->limit(10)
            ->get();

        return view('admin.hr.dashboard', compact(
            'profile',
            'balance',
            'year',
            'myPendingRequests',
            'myRecentRequests',
            'teamPendingRequests',
            'isManagerOrAdmin',
            'upcomingAbsences'
        ));
    }

    public function myLeave(Request $request)
    {
        $adminUser = $this->getAdminUser();
        if (!$adminUser->hasHrAccess()) {
            abort(403, 'You do not have access to the HR module.');
        }
        $year = (int) $request->get('year', date('Y'));
        $profile = $adminUser->hrProfile;

        if (!$profile) {
            return view('admin.hr.my-leave', [
                'profile' => null,
                'balance' => null,
                'requests' => collect(),
                'year' => $year,
            ]);
        }

        $this->leaveService->ensureEntitlement($profile, $year);
        $balance = $this->calculator->getBalanceSummary($profile, $year);

        $requests = LeaveRequest::where('employee_id', $profile->id)
            ->forYear($year)
            ->orderByDesc('submitted_at')
            ->get();

        return view('admin.hr.my-leave', compact('profile', 'balance', 'requests', 'year'));
    }

    public function storeLeaveRequest(Request $request)
    {
        $adminUser = $this->getAdminUser();
        if (!$adminUser->hasHrAccess()) {
            return response()->json(['status' => 'error', 'message' => 'Not authorised.'], 403);
        }
        $profile = $adminUser->hrProfile;

        if (!$profile) {
            return response()->json(['status' => 'error', 'message' => 'No HR profile found.'], 403);
        }

        $validated = $request->validate([
            'leave_type' => 'required|in:annual_leave,sickness,medical,birthday',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'day_portion' => 'required|in:full,half_am,half_pm,quarter',
            'note' => 'nullable|string|max:1000',
        ]);

        try {
            $leaveRequest = $this->leaveService->submitRequest(
                $profile,
                $validated['leave_type'],
                Carbon::parse($validated['start_date']),
                Carbon::parse($validated['end_date']),
                $validated['day_portion'],
                $validated['note'] ?? null
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Leave request submitted successfully.',
                'data' => [
                    'id' => $leaveRequest->id,
                    'duration_days' => $leaveRequest->duration_days_display,
                    'status' => $leaveRequest->status,
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => collect($e->errors())->flatten()->first(),
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function cancelLeaveRequest(Request $request, string $id)
    {
        $adminUser = $this->getAdminUser();
        if (!$adminUser->hasHrAccess()) {
            return response()->json(['status' => 'error', 'message' => 'Not authorised.'], 403);
        }
        $profile = $adminUser->hrProfile;

        if (!$profile) {
            return response()->json(['status' => 'error', 'message' => 'No HR profile found.'], 403);
        }

        $leaveRequest = LeaveRequest::where('id', $id)
            ->where('employee_id', $profile->id)
            ->firstOrFail();

        try {
            $this->leaveService->cancelRequest($leaveRequest, $profile);
            return response()->json(['status' => 'success', 'message' => 'Leave request cancelled.']);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => collect($e->errors())->flatten()->first(),
            ], 422);
        }
    }

    public function teamCalendar(Request $request)
    {
        $adminUser = $this->getAdminUser();
        if (!$adminUser->hasHrAccess()) {
            abort(403, 'You do not have access to the HR module.');
        }
        $year = (int) $request->get('year', date('Y'));
        $month = (int) $request->get('month', date('n'));

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $absences = LeaveRequest::whereIn('status', [LeaveRequest::STATUS_APPROVED, LeaveRequest::STATUS_PENDING])
            ->where('start_date', '<=', $endOfMonth)
            ->where('end_date', '>=', $startOfMonth)
            ->with('employee.adminUser')
            ->orderBy('start_date')
            ->get();

        $bankHolidays = BankHoliday::whereBetween('holiday_date', [$startOfMonth, $endOfMonth])->get();

        return view('admin.hr.team-calendar', compact('absences', 'bankHolidays', 'year', 'month'));
    }

    public function teamCalendarApi(Request $request)
    {
        $adminUser = $this->getAdminUser();
        if (!$adminUser->hasHrAccess()) {
            return response()->json(['status' => 'error', 'message' => 'Not authorised.'], 403);
        }
        $year = (int) $request->get('year', date('Y'));
        $month = (int) $request->get('month', date('n'));

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $absences = LeaveRequest::whereIn('status', [LeaveRequest::STATUS_APPROVED, LeaveRequest::STATUS_PENDING])
            ->where('start_date', '<=', $endOfMonth)
            ->where('end_date', '>=', $startOfMonth)
            ->with('employee.adminUser')
            ->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'employee_name' => $r->employee?->full_name ?? 'Unknown',
                'leave_type' => 'leave',
                'leave_type_label' => 'Leave',
                'status' => $r->status,
                'start_date' => $r->start_date->format('Y-m-d'),
                'end_date' => $r->end_date->format('Y-m-d'),
                'duration_days' => $r->duration_days_display,
            ]);

        $bankHolidays = BankHoliday::whereBetween('holiday_date', [$startOfMonth, $endOfMonth])
            ->get()
            ->map(fn($h) => [
                'date' => $h->holiday_date->format('Y-m-d'),
                'name' => $h->name,
            ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'absences' => $absences,
                'bank_holidays' => $bankHolidays,
            ],
        ]);
    }

    public function approveRequest(Request $request, string $id)
    {
        $adminUser = $this->getAdminUser();
        if (!$adminUser->isHrManager()) {
            return response()->json(['status' => 'error', 'message' => 'Not authorised.'], 403);
        }

        $profile = $this->getOrCreateHrProfile($adminUser);
        $leaveRequest = LeaveRequest::findOrFail($id);

        $comment = $request->input('comment');

        try {
            $this->leaveService->approveRequest($leaveRequest, $profile, $comment);
            return response()->json(['status' => 'success', 'message' => 'Leave request approved.']);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => collect($e->errors())->flatten()->first(),
            ], 422);
        }
    }

    public function rejectRequest(Request $request, string $id)
    {
        $adminUser = $this->getAdminUser();
        if (!$adminUser->isHrManager()) {
            return response()->json(['status' => 'error', 'message' => 'Not authorised.'], 403);
        }

        $profile = $this->getOrCreateHrProfile($adminUser);
        $leaveRequest = LeaveRequest::findOrFail($id);

        $validated = $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        try {
            $this->leaveService->rejectRequest($leaveRequest, $profile, $validated['comment']);
            return response()->json(['status' => 'success', 'message' => 'Leave request rejected.']);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => collect($e->errors())->flatten()->first(),
            ], 422);
        }
    }

    public function settings(Request $request)
    {
        $adminUser = $this->getAdminUser();
        if (!$adminUser->isHrAdmin()) {
            abort(403, 'HR Admin access required.');
        }

        $year = (int) $request->get('year', date('Y'));
        $settings = HrSettings::instance();

        $employees = EmployeeHrProfile::active()
            ->with('adminUser')
            ->orderBy('created_at')
            ->get()
            ->map(function ($emp) use ($year) {
                $this->leaveService->ensureEntitlement($emp, $year);
                $emp->balance = $this->calculator->getBalanceSummary($emp, $year);
                return $emp;
            });

        $pendingRequests = LeaveRequest::pending()
            ->with('employee.adminUser')
            ->orderBy('submitted_at')
            ->get();

        $adminUsers = AdminUser::active()
            ->whereDoesntHave('hrProfile')
            ->orderBy('first_name')
            ->get();

        $auditLog = LeaveAuditLog::orderByDesc('created_at')->limit(50)->get();

        $bankHolidays = BankHoliday::orderBy('holiday_date')->get();

        return view('admin.hr.settings', compact(
            'settings', 'employees', 'pendingRequests', 'year', 'adminUsers', 'auditLog', 'bankHolidays'
        ));
    }

    public function storeEmployee(Request $request)
    {
        $adminUser = $this->getAdminUser();
        if (!$adminUser->isHrAdmin()) {
            return response()->json(['status' => 'error', 'message' => 'HR Admin access required.'], 403);
        }

        $validated = $request->validate([
            'admin_user_id' => 'required|uuid|exists:admin_users,id',
            'start_date' => 'required|date',
            'department' => 'nullable|string|max:100',
            'job_title' => 'nullable|string|max:150',
            'hr_role' => 'required|in:employee,manager,hr_admin',
            'birthday' => 'nullable|date',
            'annual_entitlement_days' => 'nullable|numeric|min:0|max:365',
        ]);

        $existing = EmployeeHrProfile::where('admin_user_id', $validated['admin_user_id'])->first();
        if ($existing) {
            return response()->json(['status' => 'error', 'message' => 'This user already has an HR profile.'], 422);
        }

        $profile = EmployeeHrProfile::create([
            'admin_user_id' => $validated['admin_user_id'],
            'start_date' => $validated['start_date'],
            'department' => $validated['department'] ?? null,
            'job_title' => $validated['job_title'] ?? null,
            'hr_role' => $validated['hr_role'],
        ]);

        $targetUser = AdminUser::find($validated['admin_user_id']);
        $targetUser->update([
            'hr_role' => $validated['hr_role'],
            'birthday' => $validated['birthday'] ?? null,
        ]);

        if (!empty($validated['annual_entitlement_days'])) {
            $entitlementUnits = (int) ($validated['annual_entitlement_days'] * 4);
        } else {
            $entitlementUnits = HrSettings::instance()->default_annual_entitlement_units;
        }

        $this->leaveService->ensureEntitlement($profile, (int) date('Y'));
        $actorProfile = $this->getOrCreateHrProfile($adminUser);

        if (!empty($validated['annual_entitlement_days'])) {
            $this->leaveService->updateEntitlement(
                $profile,
                (int) date('Y'),
                $entitlementUnits,
                $actorProfile,
                'Set during profile creation'
            );
        }

        try {
            LeaveAuditLog::record(
                $actorProfile->id,
                LeaveAuditLog::ACTION_PROFILE_CREATED,
                $profile->id,
                null,
                null,
                json_encode([
                    'admin_user_id' => $validated['admin_user_id'],
                    'hr_role' => $validated['hr_role'],
                    'start_date' => $validated['start_date'],
                ]),
                null
            );
        } catch (\Throwable $e) {
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Employee HR profile created.',
            'data' => ['id' => $profile->id],
        ]);
    }

    public function updateEmployee(Request $request, string $id)
    {
        $adminUser = $this->getAdminUser();
        if (!$adminUser->isHrAdmin()) {
            return response()->json(['status' => 'error', 'message' => 'HR Admin access required.'], 403);
        }

        $profile = EmployeeHrProfile::findOrFail($id);

        $validated = $request->validate([
            'department' => 'nullable|string|max:100',
            'job_title' => 'nullable|string|max:150',
            'hr_role' => 'required|in:employee,manager,hr_admin',
            'birthday' => 'nullable|date',
            'is_active' => 'required|boolean',
        ]);

        $profile->update([
            'department' => $validated['department'] ?? null,
            'job_title' => $validated['job_title'] ?? null,
            'hr_role' => $validated['hr_role'],
            'is_active' => $validated['is_active'],
        ]);

        $profile->adminUser->update([
            'hr_role' => $validated['hr_role'],
            'birthday' => $validated['birthday'] ?? null,
        ]);

        try {
            $actorProfile = $this->getOrCreateHrProfile($adminUser);
            LeaveAuditLog::record(
                $actorProfile->id,
                LeaveAuditLog::ACTION_PROFILE_UPDATED,
                $profile->id,
                null,
                null,
                json_encode($validated),
                null
            );
        } catch (\Throwable $e) {
        }

        return response()->json(['status' => 'success', 'message' => 'Employee profile updated.']);
    }

    public function updateSettings(Request $request)
    {
        $adminUser = $this->getAdminUser();
        if (!$adminUser->isHrAdmin()) {
            return response()->json(['status' => 'error', 'message' => 'HR Admin access required.'], 403);
        }

        $validated = $request->validate([
            'default_annual_entitlement_days' => 'required|numeric|min:0|max:365',
            'birthday_leave_enabled' => 'nullable|boolean',
            'email_notifications_enabled' => 'nullable|boolean',
            'team_notification_email' => 'nullable|email|max:255',
            'show_leave_type_in_notifications' => 'nullable|boolean',
        ]);

        $settings = HrSettings::instance();
        $settings->update([
            'default_annual_entitlement_units' => (int) ($validated['default_annual_entitlement_days'] * 4),
            'birthday_leave_enabled' => $validated['birthday_leave_enabled'] ?? false,
            'email_notifications_enabled' => $validated['email_notifications_enabled'] ?? false,
            'team_notification_email' => $validated['team_notification_email'] ?? null,
            'show_leave_type_in_notifications' => $validated['show_leave_type_in_notifications'] ?? false,
        ]);

        return response()->json(['status' => 'success', 'message' => 'HR settings updated.']);
    }

    public function updateEntitlement(Request $request, string $employeeId)
    {
        $adminUser = $this->getAdminUser();
        if (!$adminUser->isHrAdmin()) {
            return response()->json(['status' => 'error', 'message' => 'HR Admin access required.'], 403);
        }

        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:2099',
            'entitlement_days' => 'required|numeric|min:0|max:365',
            'note' => 'nullable|string|max:500',
        ]);

        $employee = EmployeeHrProfile::findOrFail($employeeId);
        $actorProfile = $this->getOrCreateHrProfile($adminUser);

        $this->leaveService->updateEntitlement(
            $employee,
            $validated['year'],
            (int) ($validated['entitlement_days'] * 4),
            $actorProfile,
            $validated['note'] ?? null
        );

        return response()->json(['status' => 'success', 'message' => 'Entitlement updated.']);
    }

    private function getAdminUser(): AdminUser
    {
        $adminId = session('admin_auth.admin_id');
        if (!$adminId) {
            abort(401, 'Not authenticated.');
        }
        return AdminUser::findOrFail($adminId);
    }

    private function getOrCreateHrProfile(AdminUser $adminUser): EmployeeHrProfile
    {
        $profile = $adminUser->hrProfile;
        if ($profile) {
            return $profile;
        }

        return EmployeeHrProfile::create([
            'admin_user_id' => $adminUser->id,
            'start_date' => $adminUser->created_at?->toDateString() ?? now()->toDateString(),
            'hr_role' => $adminUser->hr_role !== 'none' ? $adminUser->hr_role : EmployeeHrProfile::ROLE_HR_ADMIN,
        ]);
    }
}
