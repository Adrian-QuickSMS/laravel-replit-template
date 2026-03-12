<?php

namespace App\Services\Hr;

use App\Models\Hr\BankHoliday;
use App\Models\Hr\EmployeeHrProfile;
use App\Models\Hr\HrSettings;
use App\Models\Hr\LeaveAuditLog;
use App\Models\Hr\LeaveEntitlement;
use App\Models\Hr\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeaveRequestService
{
    public function __construct(
        private LeaveCalculationService $calculator
    ) {}

    public function submitRequest(
        EmployeeHrProfile $employee,
        string $leaveType,
        Carbon $startDate,
        Carbon $endDate,
        string $dayPortion = 'full',
        ?string $note = null
    ): LeaveRequest {
        if (!in_array($leaveType, array_keys(LeaveRequest::LEAVE_TYPES))) {
            throw ValidationException::withMessages(['leave_type' => 'Invalid leave type.']);
        }

        if ($endDate->lt($startDate)) {
            throw ValidationException::withMessages(['end_date' => 'End date cannot be before start date.']);
        }

        if ($dayPortion !== 'full' && !$startDate->isSameDay($endDate)) {
            throw ValidationException::withMessages(['day_portion' => 'Partial day requests must be for a single day.']);
        }

        if ($leaveType === LeaveRequest::TYPE_BIRTHDAY) {
            return $this->submitBirthdayRequest($employee, $startDate, $note);
        }

        $settings = HrSettings::instance();
        $weekendDays = $settings->getWeekendDayNumbers();

        $bankHolidayDates = BankHoliday::datesBetween(
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d')
        );

        $workingDays = $this->calculator->countWorkingDays($startDate, $endDate, $weekendDays, $bankHolidayDates);

        if ($workingDays === 0) {
            throw ValidationException::withMessages(['start_date' => 'No working days in the selected date range.']);
        }

        $duration = $this->calculator->calculateDurationUnits($dayPortion, $workingDays);

        if ($this->calculator->hasOverlappingRequest($employee->id, $startDate, $endDate)) {
            throw ValidationException::withMessages(['start_date' => 'You already have a leave request for these dates.']);
        }

        if ($leaveType === LeaveRequest::TYPE_ANNUAL) {
            $year = $startDate->year;
            $balance = $this->calculator->getBalanceSummary($employee, $year);
            $remainingUnits = $balance['annual_leave']['remaining_units'];

            if ($duration['units'] > $remainingUnits) {
                throw ValidationException::withMessages([
                    'duration' => sprintf(
                        'Insufficient annual leave balance. Requesting %.2f days but only %.2f days remaining.',
                        $duration['display_days'],
                        $remainingUnits / 4
                    ),
                ]);
            }
        }

        return DB::transaction(function () use ($employee, $leaveType, $startDate, $endDate, $dayPortion, $note, $duration) {
            $request = LeaveRequest::create([
                'employee_id' => $employee->id,
                'leave_type' => $leaveType,
                'status' => LeaveRequest::STATUS_PENDING,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'duration_units' => $duration['units'],
                'duration_days_display' => $duration['display_days'],
                'day_portion' => $dayPortion,
                'employee_note' => $note,
                'submitted_at' => now(),
            ]);

            try {
                LeaveAuditLog::record(
                    $employee->id,
                    LeaveAuditLog::ACTION_REQUEST_SUBMITTED,
                    $employee->id,
                    $request->id,
                    null,
                    json_encode([
                        'type' => $leaveType,
                        'start' => $startDate->format('Y-m-d'),
                        'end' => $endDate->format('Y-m-d'),
                        'days' => $duration['display_days'],
                    ]),
                    $note
                );
            } catch (\Throwable $e) {
            }

            return $request;
        });
    }

    private function submitBirthdayRequest(EmployeeHrProfile $employee, Carbon $date, ?string $note): LeaveRequest
    {
        $settings = HrSettings::instance();
        if (!$settings->birthday_leave_enabled) {
            throw ValidationException::withMessages(['leave_type' => 'Birthday leave is not enabled.']);
        }

        $birthday = $employee->adminUser?->birthday;
        if (!$birthday) {
            throw ValidationException::withMessages(['leave_type' => 'No birthday on file. Please ask HR to update your profile.']);
        }

        $birthdayThisYear = Carbon::create($date->year, $birthday->month, $birthday->day);
        if (!$date->isSameDay($birthdayThisYear)) {
            throw ValidationException::withMessages(['start_date' => 'Birthday leave can only be taken on your birthday (' . $birthdayThisYear->format('j M') . ').']);
        }

        if ($date->isWeekend()) {
            throw ValidationException::withMessages(['start_date' => 'Your birthday falls on a weekend this year — birthday leave is not applicable.']);
        }

        $isBankHoliday = BankHoliday::where('holiday_date', $date->toDateString())->exists();
        if ($isBankHoliday) {
            throw ValidationException::withMessages(['start_date' => 'Your birthday falls on a bank holiday this year — birthday leave is not applicable.']);
        }

        $existingBirthday = LeaveRequest::where('employee_id', $employee->id)
            ->forYear($date->year)
            ->ofType(LeaveRequest::TYPE_BIRTHDAY)
            ->whereIn('status', [LeaveRequest::STATUS_PENDING, LeaveRequest::STATUS_APPROVED])
            ->exists();

        if ($existingBirthday) {
            throw ValidationException::withMessages(['leave_type' => 'You have already requested birthday leave for this year.']);
        }

        return DB::transaction(function () use ($employee, $date, $note) {
            $request = LeaveRequest::create([
                'employee_id' => $employee->id,
                'leave_type' => LeaveRequest::TYPE_BIRTHDAY,
                'status' => LeaveRequest::STATUS_APPROVED,
                'start_date' => $date,
                'end_date' => $date,
                'duration_units' => 4,
                'duration_days_display' => 1.00,
                'day_portion' => 'full',
                'employee_note' => $note ?? 'Birthday leave',
                'submitted_at' => now(),
                'approved_at' => now(),
            ]);

            try {
                LeaveAuditLog::record(
                    $employee->id,
                    LeaveAuditLog::ACTION_REQUEST_SUBMITTED,
                    $employee->id,
                    $request->id,
                    null,
                    json_encode(['type' => 'birthday', 'date' => $date->format('Y-m-d')]),
                    'Birthday leave (auto-approved)'
                );
            } catch (\Throwable $e) {
            }

            return $request;
        });
    }

    public function approveRequest(LeaveRequest $request, EmployeeHrProfile $approver, ?string $comment = null): LeaveRequest
    {
        if (!$request->isPending()) {
            throw ValidationException::withMessages(['status' => 'Only pending requests can be approved.']);
        }

        return DB::transaction(function () use ($request, $approver, $comment) {
            $request->update([
                'status' => LeaveRequest::STATUS_APPROVED,
                'approver_id' => $approver->id,
                'approval_comment' => $comment,
                'approved_at' => now(),
            ]);

            try {
                LeaveAuditLog::record(
                    $approver->id,
                    LeaveAuditLog::ACTION_REQUEST_APPROVED,
                    $request->employee_id,
                    $request->id,
                    null,
                    json_encode(['status' => 'approved']),
                    $comment
                );
            } catch (\Throwable $e) {
            }

            return $request->fresh();
        });
    }

    public function rejectRequest(LeaveRequest $request, EmployeeHrProfile $approver, ?string $comment = null): LeaveRequest
    {
        if (!$request->isPending()) {
            throw ValidationException::withMessages(['status' => 'Only pending requests can be rejected.']);
        }

        return DB::transaction(function () use ($request, $approver, $comment) {
            $request->update([
                'status' => LeaveRequest::STATUS_REJECTED,
                'approver_id' => $approver->id,
                'approval_comment' => $comment,
                'rejected_at' => now(),
            ]);

            try {
                LeaveAuditLog::record(
                    $approver->id,
                    LeaveAuditLog::ACTION_REQUEST_REJECTED,
                    $request->employee_id,
                    $request->id,
                    null,
                    json_encode(['status' => 'rejected']),
                    $comment
                );
            } catch (\Throwable $e) {
            }

            return $request->fresh();
        });
    }

    public function cancelRequest(LeaveRequest $request, EmployeeHrProfile $actor): LeaveRequest
    {
        if (!in_array($request->status, [LeaveRequest::STATUS_PENDING, LeaveRequest::STATUS_APPROVED])) {
            throw ValidationException::withMessages(['status' => 'Only pending or approved requests can be cancelled.']);
        }

        return DB::transaction(function () use ($request, $actor) {
            $request->update([
                'status' => LeaveRequest::STATUS_CANCELLED,
                'cancelled_at' => now(),
            ]);

            try {
                LeaveAuditLog::record(
                    $actor->id,
                    LeaveAuditLog::ACTION_REQUEST_CANCELLED,
                    $request->employee_id,
                    $request->id,
                    json_encode(['status' => $request->getOriginal('status')]),
                    json_encode(['status' => 'cancelled']),
                    null
                );
            } catch (\Throwable $e) {
            }

            return $request->fresh();
        });
    }

    public function ensureEntitlement(EmployeeHrProfile $employee, int $year): LeaveEntitlement
    {
        $existing = $employee->entitlementForYear($year);
        if ($existing) {
            return $existing;
        }

        $settings = HrSettings::instance();
        $fullEntitlementUnits = $settings->default_annual_entitlement_units;

        $proration = $this->calculator->calculateProratedEntitlement(
            $fullEntitlementUnits,
            $employee->start_date,
            $year
        );

        return LeaveEntitlement::create([
            'employee_id' => $employee->id,
            'year' => $year,
            'total_entitlement_units' => $proration['units'],
            'is_prorated' => $proration['is_prorated'],
            'prorate_note' => $proration['note'],
        ]);
    }

    public function updateEntitlement(
        EmployeeHrProfile $employee,
        int $year,
        int $newTotalUnits,
        EmployeeHrProfile $actor,
        ?string $note = null
    ): LeaveEntitlement {
        return DB::transaction(function () use ($employee, $year, $newTotalUnits, $actor, $note) {
            $entitlement = $this->ensureEntitlement($employee, $year);
            $oldUnits = $entitlement->total_entitlement_units;

            $entitlement->update([
                'total_entitlement_units' => $newTotalUnits,
            ]);

            try {
                LeaveAuditLog::record(
                    $actor->id,
                    LeaveAuditLog::ACTION_ENTITLEMENT_CHANGED,
                    $employee->id,
                    null,
                    json_encode(['units' => $oldUnits, 'days' => $oldUnits / 4]),
                    json_encode(['units' => $newTotalUnits, 'days' => $newTotalUnits / 4]),
                    $note
                );
            } catch (\Throwable $e) {
            }

            return $entitlement->fresh();
        });
    }
}
