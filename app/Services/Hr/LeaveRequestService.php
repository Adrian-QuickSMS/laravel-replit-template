<?php

namespace App\Services\Hr;

use App\Models\Hr\CompanyHrSettings;
use App\Models\Hr\EmployeeHrProfile;
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

    /**
     * Submit a new leave request.
     */
    public function submitRequest(
        EmployeeHrProfile $employee,
        string $leaveType,
        Carbon $startDate,
        Carbon $endDate,
        string $dayPortion = 'full',
        ?string $note = null
    ): LeaveRequest {
        // Validation
        if (!in_array($leaveType, array_keys(LeaveRequest::LEAVE_TYPES))) {
            throw ValidationException::withMessages(['leave_type' => 'Invalid leave type.']);
        }

        if ($endDate->lt($startDate)) {
            throw ValidationException::withMessages(['end_date' => 'End date cannot be before start date.']);
        }

        // Partial day requests must be single-day
        if ($dayPortion !== 'full' && !$startDate->isSameDay($endDate)) {
            throw ValidationException::withMessages(['day_portion' => 'Partial day requests must be for a single day.']);
        }

        // Get weekend days from company settings
        $settings = CompanyHrSettings::forTenant($employee->tenant_id);
        $weekendDays = $settings->getWeekendDayNumbers();

        // Get bank holidays for the date range
        $bankHolidayDates = DB::table('bank_holidays')
            ->where(function ($q) use ($employee) {
                $q->whereNull('tenant_id')
                  ->orWhere('tenant_id', $employee->tenant_id);
            })
            ->whereBetween('holiday_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->pluck('holiday_date')
            ->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        $workingDays = $this->calculator->countWorkingDays($startDate, $endDate, $weekendDays, $bankHolidayDates);

        if ($workingDays === 0) {
            throw ValidationException::withMessages(['start_date' => 'No working days in the selected date range.']);
        }

        $duration = $this->calculator->calculateDurationUnits($dayPortion, $workingDays);

        // Check for overlapping requests
        if ($this->calculator->hasOverlappingRequest($employee->id, $startDate, $endDate)) {
            throw ValidationException::withMessages(['start_date' => 'You already have a leave request for these dates.']);
        }

        // For annual leave, check balance
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
                'tenant_id' => $employee->tenant_id,
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

            LeaveAuditLog::record(
                $employee->tenant_id,
                $employee->id,
                LeaveAuditLog::ACTION_REQUEST_SUBMITTED,
                $employee->id,
                $request->id,
                null,
                json_encode([
                    'type' => $leaveType,
                    'start' => $startDate->format('Y-m-d'),
                    'end' => $endDate->format('Y-m-d'),
                    'units' => $duration['units'],
                    'portion' => $dayPortion,
                ])
            );

            return $request;
        });
    }

    /**
     * Approve a leave request.
     */
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

            LeaveAuditLog::record(
                $request->tenant_id,
                $approver->id,
                LeaveAuditLog::ACTION_REQUEST_APPROVED,
                $request->employee_id,
                $request->id,
                'pending',
                'approved',
                $comment
            );

            return $request->fresh();
        });
    }

    /**
     * Reject a leave request.
     */
    public function rejectRequest(LeaveRequest $request, EmployeeHrProfile $approver, ?string $reason = null): LeaveRequest
    {
        if (!$request->isPending()) {
            throw ValidationException::withMessages(['status' => 'Only pending requests can be rejected.']);
        }

        return DB::transaction(function () use ($request, $approver, $reason) {
            $request->update([
                'status' => LeaveRequest::STATUS_REJECTED,
                'approver_id' => $approver->id,
                'approval_comment' => $reason,
                'rejected_at' => now(),
            ]);

            LeaveAuditLog::record(
                $request->tenant_id,
                $approver->id,
                LeaveAuditLog::ACTION_REQUEST_REJECTED,
                $request->employee_id,
                $request->id,
                'pending',
                'rejected',
                $reason
            );

            return $request->fresh();
        });
    }

    /**
     * Cancel a leave request. Restores balance if previously approved annual leave.
     */
    public function cancelRequest(LeaveRequest $request, EmployeeHrProfile $actor): LeaveRequest
    {
        if (!in_array($request->status, [LeaveRequest::STATUS_PENDING, LeaveRequest::STATUS_APPROVED])) {
            throw ValidationException::withMessages(['status' => 'Only pending or approved requests can be cancelled.']);
        }

        // Don't allow cancelling past leave
        if ($request->start_date->lt(today())) {
            throw ValidationException::withMessages(['start_date' => 'Cannot cancel leave that has already started.']);
        }

        $previousStatus = $request->status;

        return DB::transaction(function () use ($request, $actor, $previousStatus) {
            $request->update([
                'status' => LeaveRequest::STATUS_CANCELLED,
                'cancelled_at' => now(),
            ]);

            // Balance is automatically restored because we only count approved requests
            // when calculating balance - no explicit restoration needed

            LeaveAuditLog::record(
                $request->tenant_id,
                $actor->id,
                LeaveAuditLog::ACTION_REQUEST_CANCELLED,
                $request->employee_id,
                $request->id,
                $previousStatus,
                'cancelled'
            );

            return $request->fresh();
        });
    }

    /**
     * Ensure an employee has an entitlement record for the given year.
     * Creates one with prorated calculation if it doesn't exist.
     */
    public function ensureEntitlement(EmployeeHrProfile $employee, int $year): LeaveEntitlement
    {
        $existing = $employee->entitlementForYear($year);
        if ($existing) {
            return $existing;
        }

        $settings = CompanyHrSettings::forTenant($employee->tenant_id);
        $fullEntitlementUnits = $settings->default_annual_entitlement_units;

        $proration = $this->calculator->calculateProratedEntitlement(
            $fullEntitlementUnits,
            $employee->start_date,
            $year
        );

        return LeaveEntitlement::create([
            'tenant_id' => $employee->tenant_id,
            'employee_id' => $employee->id,
            'year' => $year,
            'total_entitlement_units' => $proration['units'],
            'is_prorated' => $proration['is_prorated'],
            'prorate_note' => $proration['note'],
        ]);
    }

    /**
     * Update entitlement for an employee (admin action).
     */
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

            LeaveAuditLog::record(
                $employee->tenant_id,
                $actor->id,
                LeaveAuditLog::ACTION_ENTITLEMENT_CHANGED,
                $employee->id,
                null,
                json_encode(['units' => $oldUnits, 'days' => $oldUnits / 4]),
                json_encode(['units' => $newTotalUnits, 'days' => $newTotalUnits / 4]),
                $note
            );

            return $entitlement->fresh();
        });
    }
}
