<?php

namespace App\Services\Hr;

use App\Models\Hr\EmployeeHrProfile;
use App\Models\Hr\HolidayAdjustmentRequest;
use App\Models\Hr\HrSettings;
use App\Models\Hr\LeaveAuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HolidayAdjustmentService
{
    public function __construct(
        private LeaveCalculationService $calculator,
        private LeaveRequestService $leaveService,
        private HrNotificationService $notificationService
    ) {}

    public function requestPurchase(EmployeeHrProfile $employee, int $units, int $year, ?string $reason = null): HolidayAdjustmentRequest
    {
        $settings = HrSettings::instance();
        if (!$settings->allow_purchase) {
            throw ValidationException::withMessages(['type' => 'Purchasing extra holiday is not currently enabled.']);
        }

        if ($units < 1) {
            throw ValidationException::withMessages(['units' => 'Must purchase at least 1 unit (0.25 days).']);
        }

        $pool = $this->calculator->validateAdditionalPool($employee, $year, $units);
        if (!$pool['allowed']) {
            throw ValidationException::withMessages([
                'units' => sprintf(
                    'This would exceed the additional holiday cap of %.1f days. You have %.1f days of room remaining.',
                    $pool['max_days'],
                    $pool['remaining_room_days']
                ),
            ]);
        }

        return DB::transaction(function () use ($employee, $units, $year, $reason) {
            $request = HolidayAdjustmentRequest::create([
                'employee_id' => $employee->id,
                'type' => HolidayAdjustmentRequest::TYPE_PURCHASE,
                'status' => HolidayAdjustmentRequest::STATUS_PENDING,
                'units' => $units,
                'year' => $year,
                'requested_by' => $employee->id,
                'reason' => $reason,
            ]);

            try {
                LeaveAuditLog::record(
                    $employee->id,
                    'purchase_requested',
                    $employee->id,
                    null,
                    null,
                    json_encode(['units' => $units, 'days' => $units / 4, 'year' => $year]),
                    $reason
                );
            } catch (\Throwable $e) {}

            try {
                $this->notificationService->notifyAdjustment(HrNotificationService::EVENT_PURCHASE_REQUESTED, $employee->full_name, $units / 4, $year, $reason);
            } catch (\Throwable $e) {}

            return $request;
        });
    }

    public function approvePurchase(HolidayAdjustmentRequest $request, EmployeeHrProfile $approver, ?string $note = null): HolidayAdjustmentRequest
    {
        if ($request->status !== HolidayAdjustmentRequest::STATUS_PENDING) {
            throw ValidationException::withMessages(['status' => 'Only pending requests can be approved.']);
        }

        $pool = $this->calculator->validateAdditionalPool($request->employee, $request->year, 0, $request->id);
        if (!$pool['allowed']) {
            throw ValidationException::withMessages([
                'units' => sprintf(
                    'Approving would exceed the %.1f day additional holiday cap.',
                    $pool['max_days']
                ),
            ]);
        }

        return DB::transaction(function () use ($request, $approver, $note) {
            $request->update([
                'status' => HolidayAdjustmentRequest::STATUS_APPROVED,
                'approved_by' => $approver->id,
                'admin_note' => $note,
                'approved_at' => now(),
            ]);

            $entitlement = $this->leaveService->ensureEntitlement($request->employee, $request->year);
            $entitlement->increment('purchased_units', $request->units);

            try {
                LeaveAuditLog::record(
                    $approver->id,
                    'purchase_approved',
                    $request->employee_id,
                    null,
                    null,
                    json_encode(['units' => $request->units, 'days' => $request->units / 4]),
                    $note
                );
            } catch (\Throwable $e) {}

            try {
                $this->notificationService->notifyAdjustment(HrNotificationService::EVENT_PURCHASE_APPROVED, $request->employee->full_name, $request->units / 4, $request->year);
            } catch (\Throwable $e) {}

            return $request->fresh();
        });
    }

    public function rejectPurchase(HolidayAdjustmentRequest $request, EmployeeHrProfile $approver, ?string $note = null): HolidayAdjustmentRequest
    {
        if ($request->status !== HolidayAdjustmentRequest::STATUS_PENDING) {
            throw ValidationException::withMessages(['status' => 'Only pending requests can be rejected.']);
        }

        return DB::transaction(function () use ($request, $approver, $note) {
            $request->update([
                'status' => HolidayAdjustmentRequest::STATUS_REJECTED,
                'approved_by' => $approver->id,
                'admin_note' => $note,
                'rejected_at' => now(),
            ]);

            try {
                LeaveAuditLog::record(
                    $approver->id,
                    'purchase_rejected',
                    $request->employee_id,
                    null,
                    null,
                    json_encode(['units' => $request->units, 'days' => $request->units / 4]),
                    $note
                );
            } catch (\Throwable $e) {}

            return $request->fresh();
        });
    }

    public function grantToil(EmployeeHrProfile $employee, int $units, int $year, EmployeeHrProfile $manager, ?string $reason = null): HolidayAdjustmentRequest
    {
        $settings = HrSettings::instance();
        if (!$settings->allow_toil) {
            throw ValidationException::withMessages(['type' => 'TOIL is not currently enabled.']);
        }

        if ($units < 1) {
            throw ValidationException::withMessages(['units' => 'Must grant at least 1 unit (0.25 days).']);
        }

        $pool = $this->calculator->validateAdditionalPool($employee, $year, $units);
        if (!$pool['allowed']) {
            throw ValidationException::withMessages([
                'units' => sprintf(
                    'This would exceed the additional holiday cap of %.1f days. Employee has %.1f days of room remaining.',
                    $pool['max_days'],
                    $pool['remaining_room_days']
                ),
            ]);
        }

        return DB::transaction(function () use ($employee, $units, $year, $manager, $reason) {
            $entitlement = $this->leaveService->ensureEntitlement($employee, $year);
            $entitlement->increment('gifted_units', $units);

            $record = HolidayAdjustmentRequest::create([
                'employee_id' => $employee->id,
                'type' => HolidayAdjustmentRequest::TYPE_TOIL,
                'status' => HolidayAdjustmentRequest::STATUS_APPROVED,
                'units' => $units,
                'year' => $year,
                'requested_by' => $manager->id,
                'approved_by' => $manager->id,
                'reason' => $reason,
                'approved_at' => now(),
            ]);

            try {
                LeaveAuditLog::record(
                    $manager->id,
                    'toil_granted',
                    $employee->id,
                    null,
                    null,
                    json_encode(['units' => $units, 'days' => $units / 4, 'year' => $year]),
                    $reason
                );
            } catch (\Throwable $e) {}

            try {
                $this->notificationService->notifyAdjustment(HrNotificationService::EVENT_TOIL_GRANTED, $employee->full_name, $units / 4, $year, $reason);
            } catch (\Throwable $e) {}

            return $record;
        });
    }

    public function grantGifted(EmployeeHrProfile $employee, int $units, int $year, EmployeeHrProfile $admin, ?string $reason = null): HolidayAdjustmentRequest
    {
        if ($units < 1) {
            throw ValidationException::withMessages(['units' => 'Must gift at least 1 unit (0.25 days).']);
        }

        $pool = $this->calculator->validateAdditionalPool($employee, $year, $units);
        if (!$pool['allowed']) {
            throw ValidationException::withMessages([
                'units' => sprintf(
                    'This would exceed the additional holiday cap of %.1f days. Employee has %.1f days of room remaining.',
                    $pool['max_days'],
                    $pool['remaining_room_days']
                ),
            ]);
        }

        return DB::transaction(function () use ($employee, $units, $year, $admin, $reason) {
            $entitlement = $this->leaveService->ensureEntitlement($employee, $year);
            $entitlement->increment('gifted_units', $units);

            $record = HolidayAdjustmentRequest::create([
                'employee_id' => $employee->id,
                'type' => HolidayAdjustmentRequest::TYPE_GIFTED,
                'status' => HolidayAdjustmentRequest::STATUS_APPROVED,
                'units' => $units,
                'year' => $year,
                'requested_by' => $admin->id,
                'approved_by' => $admin->id,
                'reason' => $reason,
                'approved_at' => now(),
            ]);

            try {
                LeaveAuditLog::record(
                    $admin->id,
                    'gifted_granted',
                    $employee->id,
                    null,
                    null,
                    json_encode(['units' => $units, 'days' => $units / 4, 'year' => $year]),
                    $reason
                );
            } catch (\Throwable $e) {}

            try {
                $this->notificationService->notifyAdjustment(HrNotificationService::EVENT_GIFTED_GRANTED, $employee->full_name, $units / 4, $year, $reason);
            } catch (\Throwable $e) {}

            return $record;
        });
    }
}
