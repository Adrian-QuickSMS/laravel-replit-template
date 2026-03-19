<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\Hr\BankHoliday;
use App\Models\Hr\EmployeeHrProfile;
use App\Models\Hr\HolidayAdjustmentRequest;
use App\Models\Hr\HrSettings;
use App\Models\Hr\LeaveAuditLog;
use App\Models\Hr\LeaveRequest;
use App\Services\Hr\BradfordFactorService;
use App\Services\Hr\HolidayAdjustmentService;
use App\Services\Hr\LeaveCalculationService;
use App\Services\Hr\LeaveRequestService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class HrController extends Controller
{
    public function __construct(
        private LeaveCalculationService $calculator,
        private LeaveRequestService $leaveService,
        private HolidayAdjustmentService $adjustmentService,
        private BradfordFactorService $bradfordService
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

        if ($isManagerOrAdmin && $profile) {
            $query = LeaveRequest::pending()
                ->where('employee_id', '!=', $profile->id)
                ->with('employee.adminUser')
                ->orderBy('submitted_at')
                ->limit(20);

            if ($profile->isHrAdmin()) {
            } else {
                $directReportIds = $profile->directReports()->pluck('id')->toArray();
                $query->whereIn('employee_id', $directReportIds);
            }

            $teamPendingRequests = $query->get();
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

        if (!$profile->isHrAdmin()) {
            $directReportIds = $profile->directReports()->pluck('id')->toArray();
            if (!in_array($leaveRequest->employee_id, $directReportIds)) {
                return response()->json(['status' => 'error', 'message' => 'You can only approve requests from your direct reports.'], 403);
            }
        }

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

        if (!$profile->isHrAdmin()) {
            $directReportIds = $profile->directReports()->pluck('id')->toArray();
            if (!in_array($leaveRequest->employee_id, $directReportIds)) {
                return response()->json(['status' => 'error', 'message' => 'You can only reject requests from your direct reports.'], 403);
            }
        }

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
            'max_additional_days' => 'nullable|numeric|min:0|max:30',
            'allow_purchase' => 'nullable|boolean',
            'allow_toil' => 'nullable|boolean',
            'allow_carry_over' => 'nullable|boolean',
            'birthday_leave_enabled' => 'nullable|boolean',
            'email_notifications_enabled' => 'nullable|boolean',
            'team_notification_email' => 'nullable|email|max:255',
            'show_leave_type_in_notifications' => 'nullable|boolean',
            'slack_webhook_url' => 'nullable|url|max:500',
            'teams_webhook_url' => 'nullable|url|max:500',
        ]);

        $settings = HrSettings::instance();
        $settings->update([
            'default_annual_entitlement_units' => (int) ($validated['default_annual_entitlement_days'] * 4),
            'max_additional_units' => isset($validated['max_additional_days']) ? (int) ($validated['max_additional_days'] * 4) : $settings->max_additional_units,
            'allow_purchase' => $validated['allow_purchase'] ?? false,
            'allow_toil' => $validated['allow_toil'] ?? false,
            'allow_carry_over' => $validated['allow_carry_over'] ?? false,
            'birthday_leave_enabled' => $validated['birthday_leave_enabled'] ?? false,
            'email_notifications_enabled' => $validated['email_notifications_enabled'] ?? false,
            'team_notification_email' => $validated['team_notification_email'] ?? null,
            'show_leave_type_in_notifications' => $validated['show_leave_type_in_notifications'] ?? false,
            'slack_webhook_url' => $validated['slack_webhook_url'] ?? null,
            'teams_webhook_url' => $validated['teams_webhook_url'] ?? null,
        ]);

        return response()->json(['status' => 'success', 'message' => 'HR settings updated.']);
    }

    public function requestPurchase(Request $request)
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
            'days' => 'required|numeric|min:0.25|max:5',
            'reason' => 'nullable|string|max:500',
        ]);

        $units = (int) ($validated['days'] * 4);

        try {
            $this->adjustmentService->requestPurchase($profile, $units, (int) date('Y'), $validated['reason'] ?? null);
            return response()->json(['status' => 'success', 'message' => 'Purchase request submitted for HR Admin approval.']);
        } catch (ValidationException $e) {
            return response()->json(['status' => 'error', 'message' => collect($e->errors())->flatten()->first()], 422);
        }
    }

    public function approvePurchase(Request $request, string $id)
    {
        $adminUser = $this->getAdminUser();
        if (!$adminUser->isHrAdmin()) {
            return response()->json(['status' => 'error', 'message' => 'HR Admin access required.'], 403);
        }

        $profile = $this->getOrCreateHrProfile($adminUser);
        $adjustmentRequest = HolidayAdjustmentRequest::findOrFail($id);

        try {
            $this->adjustmentService->approvePurchase($adjustmentRequest, $profile, $request->input('note'));
            return response()->json(['status' => 'success', 'message' => 'Purchase approved.']);
        } catch (ValidationException $e) {
            return response()->json(['status' => 'error', 'message' => collect($e->errors())->flatten()->first()], 422);
        }
    }

    public function rejectPurchase(Request $request, string $id)
    {
        $adminUser = $this->getAdminUser();
        if (!$adminUser->isHrAdmin()) {
            return response()->json(['status' => 'error', 'message' => 'HR Admin access required.'], 403);
        }

        $profile = $this->getOrCreateHrProfile($adminUser);
        $adjustmentRequest = HolidayAdjustmentRequest::findOrFail($id);

        try {
            $this->adjustmentService->rejectPurchase($adjustmentRequest, $profile, $request->input('note'));
            return response()->json(['status' => 'success', 'message' => 'Purchase request rejected.']);
        } catch (ValidationException $e) {
            return response()->json(['status' => 'error', 'message' => collect($e->errors())->flatten()->first()], 422);
        }
    }

    public function grantToil(Request $request)
    {
        $adminUser = $this->getAdminUser();
        if (!$adminUser->isHrManager()) {
            return response()->json(['status' => 'error', 'message' => 'Manager access required.'], 403);
        }

        $validated = $request->validate([
            'employee_id' => 'required|uuid|exists:employee_hr_profiles,id',
            'days' => 'required|numeric|min:0.25|max:5',
            'reason' => 'nullable|string|max:500',
        ]);

        $employee = EmployeeHrProfile::findOrFail($validated['employee_id']);
        $manager = $this->getOrCreateHrProfile($adminUser);

        if (!$manager->isHrAdmin()) {
            $directReportIds = $manager->directReports()->pluck('id')->toArray();
            if (!in_array($employee->id, $directReportIds)) {
                return response()->json(['status' => 'error', 'message' => 'You can only grant TOIL to your direct reports.'], 403);
            }
        }

        try {
            $this->adjustmentService->grantToil($employee, (int) ($validated['days'] * 4), (int) date('Y'), $manager, $validated['reason'] ?? null);
            return response()->json(['status' => 'success', 'message' => 'TOIL granted successfully.']);
        } catch (ValidationException $e) {
            return response()->json(['status' => 'error', 'message' => collect($e->errors())->flatten()->first()], 422);
        }
    }

    public function grantGifted(Request $request)
    {
        $adminUser = $this->getAdminUser();
        if (!$adminUser->isHrAdmin()) {
            return response()->json(['status' => 'error', 'message' => 'HR Admin access required.'], 403);
        }

        $validated = $request->validate([
            'employee_id' => 'required|uuid|exists:employee_hr_profiles,id',
            'days' => 'required|numeric|min:0.25|max:5',
            'reason' => 'nullable|string|max:500',
        ]);

        $employee = EmployeeHrProfile::findOrFail($validated['employee_id']);
        $admin = $this->getOrCreateHrProfile($adminUser);

        try {
            $this->adjustmentService->grantGifted($employee, (int) ($validated['days'] * 4), (int) date('Y'), $admin, $validated['reason'] ?? null);
            return response()->json(['status' => 'success', 'message' => 'Gifted holiday granted successfully.']);
        } catch (ValidationException $e) {
            return response()->json(['status' => 'error', 'message' => collect($e->errors())->flatten()->first()], 422);
        }
    }

    public function runCarryOver(Request $request)
    {
        $adminUser = $this->getAdminUser();
        if (!$adminUser->isHrAdmin()) {
            return response()->json(['status' => 'error', 'message' => 'HR Admin access required.'], 403);
        }

        $dryRun = $request->boolean('dry_run', false);
        $year = $request->input('year', date('Y') - 1);

        $exitCode = Artisan::call('hr:carry-over', [
            '--year' => $year,
            '--dry-run' => $dryRun,
        ]);

        $output = Artisan::output();

        return response()->json([
            'status' => $exitCode === 0 ? 'success' : 'error',
            'message' => $exitCode === 0
                ? ($dryRun ? 'Dry run complete — no changes made.' : 'Carry-over completed successfully.')
                : 'Carry-over failed.',
            'output' => $output,
        ]);
    }

    public function storeBankHoliday(Request $request)
    {
        $adminUser = $this->getAdminUser();
        if (!$adminUser->isHrAdmin()) {
            return response()->json(['status' => 'error', 'message' => 'HR Admin access required.'], 403);
        }

        $validated = $request->validate([
            'holiday_date' => 'required|date',
            'name' => 'required|string|max:150',
            'region' => 'nullable|string|max:50',
        ]);

        $date = Carbon::parse($validated['holiday_date']);

        $existing = BankHoliday::where('holiday_date', $date->toDateString())->first();
        if ($existing) {
            return response()->json(['status' => 'error', 'message' => 'A bank holiday already exists on this date.'], 422);
        }

        BankHoliday::create([
            'holiday_date' => $date->toDateString(),
            'name' => $validated['name'],
            'region' => $validated['region'] ?? 'england-and-wales',
            'year' => $date->year,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Bank holiday added.']);
    }

    public function updateBankHoliday(Request $request, string $id)
    {
        $adminUser = $this->getAdminUser();
        if (!$adminUser->isHrAdmin()) {
            return response()->json(['status' => 'error', 'message' => 'HR Admin access required.'], 403);
        }

        $holiday = BankHoliday::findOrFail($id);

        $validated = $request->validate([
            'holiday_date' => 'required|date',
            'name' => 'required|string|max:150',
        ]);

        $date = Carbon::parse($validated['holiday_date']);
        $holiday->update([
            'holiday_date' => $date->toDateString(),
            'name' => $validated['name'],
            'year' => $date->year,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Bank holiday updated.']);
    }

    public function deleteBankHoliday(string $id)
    {
        $adminUser = $this->getAdminUser();
        if (!$adminUser->isHrAdmin()) {
            return response()->json(['status' => 'error', 'message' => 'HR Admin access required.'], 403);
        }

        BankHoliday::findOrFail($id)->delete();
        return response()->json(['status' => 'success', 'message' => 'Bank holiday deleted.']);
    }

    public function importBankHolidays()
    {
        $adminUser = $this->getAdminUser();
        if (!$adminUser->isHrAdmin()) {
            return response()->json(['status' => 'error', 'message' => 'HR Admin access required.'], 403);
        }

        try {
            $response = Http::timeout(10)->get('https://www.gov.uk/bank-holidays.json');

            if (!$response->successful()) {
                return response()->json(['status' => 'error', 'message' => 'Failed to fetch bank holidays from GOV.UK. Please try again later.'], 502);
            }

            $data = $response->json();
            $events = $data['england-and-wales']['events'] ?? [];

            if (empty($events)) {
                return response()->json(['status' => 'error', 'message' => 'No bank holiday data found in the GOV.UK response.'], 422);
            }

            $added = 0;
            $skipped = 0;

            foreach ($events as $event) {
                $date = $event['date'] ?? null;
                $name = $event['title'] ?? null;
                if (!$date || !$name) continue;

                $existing = BankHoliday::where('holiday_date', $date)->first();
                if ($existing) {
                    $skipped++;
                    continue;
                }

                BankHoliday::create([
                    'holiday_date' => $date,
                    'name' => $name,
                    'region' => 'england-and-wales',
                    'year' => Carbon::parse($date)->year,
                ]);
                $added++;
            }

            return response()->json([
                'status' => 'success',
                'message' => "Import complete: {$added} added, {$skipped} already existed.",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Could not connect to GOV.UK: ' . $e->getMessage(),
            ], 502);
        }
    }

    public function bradfordFactorApi(Request $request)
    {
        $adminUser = $this->getAdminUser();
        if (!$adminUser->isHrManager()) {
            return response()->json(['status' => 'error', 'message' => 'Not authorised.'], 403);
        }

        $results = $this->bradfordService->calculateForAll();

        return response()->json(['status' => 'success', 'data' => $results]);
    }

    public function calendarEventsApi(Request $request)
    {
        $adminUser = $this->getAdminUser();
        if (!$adminUser->hasHrAccess()) {
            return response()->json(['status' => 'error', 'message' => 'Not authorised.'], 403);
        }

        $start = $request->input('start');
        $end = $request->input('end');

        if (!$start || !$end) {
            return response()->json(['status' => 'error', 'message' => 'Start and end dates required.'], 422);
        }

        $startDate = Carbon::parse($start);
        $endDate = Carbon::parse($end);

        $department = $request->input('department');

        $query = LeaveRequest::whereIn('status', [LeaveRequest::STATUS_APPROVED, LeaveRequest::STATUS_PENDING])
            ->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->with('employee.adminUser');

        if ($department) {
            $query->whereHas('employee', fn($q) => $q->where('department', $department));
        }

        $absences = $query->get();

        $colors = [
            'annual_leave' => '#3699ff',
            'sickness' => '#dc3545',
            'medical' => '#fd7e14',
            'birthday' => '#28a745',
        ];

        $events = $absences->map(fn($r) => [
            'id' => $r->id,
            'title' => ($r->employee?->full_name ?? 'Unknown') . ' — ' . $r->leave_type_label,
            'start' => $r->start_date->format('Y-m-d'),
            'end' => $r->end_date->copy()->addDay()->format('Y-m-d'),
            'color' => $r->status === 'pending'
                ? '#ffc107'
                : ($colors[$r->leave_type] ?? '#6c757d'),
            'textColor' => $r->status === 'pending' ? '#333' : '#fff',
            'extendedProps' => [
                'employee' => $r->employee?->full_name,
                'leave_type' => $r->leave_type_label,
                'status' => $r->status,
                'duration' => $r->duration_days_display,
                'department' => $r->employee?->department,
            ],
        ]);

        $bankHolidays = BankHoliday::whereBetween('holiday_date', [$startDate, $endDate])
            ->get()
            ->map(fn($h) => [
                'id' => 'bh-' . $h->id,
                'title' => $h->name,
                'start' => $h->holiday_date->format('Y-m-d'),
                'end' => $h->holiday_date->copy()->addDay()->format('Y-m-d'),
                'color' => '#17a2b8',
                'textColor' => '#fff',
                'display' => 'background',
            ]);

        return response()->json(array_merge($events->toArray(), $bankHolidays->toArray()));
    }

    public function pendingAdjustmentsApi(Request $request)
    {
        $adminUser = $this->getAdminUser();
        if (!$adminUser->isHrAdmin()) {
            return response()->json(['status' => 'error', 'message' => 'HR Admin access required.'], 403);
        }

        $pending = HolidayAdjustmentRequest::pending()
            ->with('employee.adminUser', 'requester.adminUser')
            ->orderBy('created_at')
            ->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'employee_name' => $r->employee?->full_name ?? 'Unknown',
                'type' => $r->type,
                'type_label' => $r->type_label,
                'days' => $r->units / 4,
                'year' => $r->year,
                'reason' => $r->reason,
                'requested_by' => $r->requester?->full_name ?? 'Unknown',
                'created_at' => $r->created_at->format('d M Y H:i'),
            ]);

        return response()->json(['status' => 'success', 'data' => $pending]);
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

        $assignedRole = $adminUser->hr_role !== 'none' ? $adminUser->hr_role : EmployeeHrProfile::ROLE_HR_ADMIN;

        $profile = EmployeeHrProfile::create([
            'admin_user_id' => $adminUser->id,
            'start_date' => $adminUser->created_at?->toDateString() ?? now()->toDateString(),
            'hr_role' => $assignedRole,
        ]);

        if ($adminUser->hr_role === 'none') {
            $adminUser->update(['hr_role' => $assignedRole]);
        }

        return $profile;
    }
}
