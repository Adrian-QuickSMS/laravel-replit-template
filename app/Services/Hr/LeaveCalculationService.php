<?php

namespace App\Services\Hr;

use App\Models\Hr\BankHoliday;
use App\Models\Hr\EmployeeHrProfile;
use App\Models\Hr\HrSettings;
use App\Models\Hr\LeaveEntitlement;
use App\Models\Hr\LeaveRequest;
use Carbon\Carbon;

class LeaveCalculationService
{
    public function calculateProratedEntitlement(int $fullEntitlementUnits, Carbon $startDate, int $year): array
    {
        $yearStart = Carbon::create($year, 1, 1);
        $yearEnd = Carbon::create($year, 12, 31);

        if ($startDate->lte($yearStart)) {
            return [
                'units' => $fullEntitlementUnits,
                'is_prorated' => false,
                'note' => 'Full year entitlement',
            ];
        }

        if ($startDate->gt($yearEnd)) {
            return [
                'units' => 0,
                'is_prorated' => true,
                'note' => 'Employee starts after end of holiday year',
            ];
        }

        $totalDaysInYear = $yearStart->diffInDays($yearEnd) + 1;
        $remainingDays = $startDate->diffInDays($yearEnd) + 1;

        $proratedExact = ($remainingDays / $totalDaysInYear) * $fullEntitlementUnits;
        $proratedUnits = (int) ceil($proratedExact);

        return [
            'units' => $proratedUnits,
            'is_prorated' => true,
            'note' => sprintf(
                'Prorated: %d/%d days in year = %.2f days (rounded up to %.2f days)',
                $remainingDays,
                $totalDaysInYear,
                $proratedExact / 4,
                $proratedUnits / 4
            ),
        ];
    }

    public function countWorkingDays(Carbon $startDate, Carbon $endDate, array $weekendDays = [6, 0], array $bankHolidayDates = []): int
    {
        $workingDays = 0;
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            $dayOfWeek = $current->dayOfWeek;
            $isWeekend = in_array($dayOfWeek, $weekendDays);
            $isBankHoliday = in_array($current->format('Y-m-d'), $bankHolidayDates);

            if (!$isWeekend && !$isBankHoliday) {
                $workingDays++;
            }

            $current->addDay();
        }

        return $workingDays;
    }

    public function calculateDurationUnits(string $dayPortion, int $workingDays): array
    {
        $unitsPerDay = match ($dayPortion) {
            'full' => 4,
            'half_am', 'half_pm' => 2,
            'quarter' => 1,
            default => 4,
        };

        if ($workingDays > 1 && $dayPortion !== 'full') {
            $units = $unitsPerDay + (($workingDays - 1) * 4);
        } else {
            $units = $unitsPerDay * $workingDays;
        }

        $displayDays = $units / 4;

        return [
            'units' => $units,
            'display_days' => $displayDays,
        ];
    }

    public function getBalanceSummary(EmployeeHrProfile $employee, int $year): array
    {
        $entitlement = $employee->entitlementForYear($year);

        $totalEntitlementUnits = $entitlement
            ? $entitlement->total_available_units
            : 0;

        $usedAnnualUnits = LeaveRequest::where('employee_id', $employee->id)
            ->forYear($year)
            ->approved()
            ->ofType(LeaveRequest::TYPE_ANNUAL)
            ->sum('duration_units');

        $pendingAnnualUnits = LeaveRequest::where('employee_id', $employee->id)
            ->forYear($year)
            ->pending()
            ->ofType(LeaveRequest::TYPE_ANNUAL)
            ->sum('duration_units');

        $remainingUnits = $totalEntitlementUnits - (int) $usedAnnualUnits - (int) $pendingAnnualUnits;

        $sicknessUnits = LeaveRequest::where('employee_id', $employee->id)
            ->forYear($year)
            ->approved()
            ->ofType(LeaveRequest::TYPE_SICKNESS)
            ->sum('duration_units');

        $medicalUnits = LeaveRequest::where('employee_id', $employee->id)
            ->forYear($year)
            ->approved()
            ->ofType(LeaveRequest::TYPE_MEDICAL)
            ->sum('duration_units');

        $birthdayUsed = LeaveRequest::where('employee_id', $employee->id)
            ->forYear($year)
            ->whereIn('status', [LeaveRequest::STATUS_APPROVED, LeaveRequest::STATUS_PENDING])
            ->ofType(LeaveRequest::TYPE_BIRTHDAY)
            ->exists();

        $settings = HrSettings::instance();
        $birthdayEligible = false;
        if ($settings->birthday_leave_enabled && $employee->adminUser?->birthday) {
            $bday = $employee->adminUser->birthday;
            $birthdayThisYear = Carbon::create($year, $bday->month, $bday->day);
            $weekendDays = $settings->getWeekendDayNumbers();
            $bankHolidayDates = BankHoliday::datesForYear($year);
            $isWorkingDay = !in_array($birthdayThisYear->dayOfWeek, $weekendDays)
                && !in_array($birthdayThisYear->format('Y-m-d'), $bankHolidayDates);
            $birthdayEligible = $isWorkingDay;
        }

        return [
            'year' => $year,
            'entitlement' => [
                'total_units' => $totalEntitlementUnits,
                'total_days' => $totalEntitlementUnits / 4,
                'base_units' => $entitlement?->total_entitlement_units ?? 0,
                'carried_over_units' => $entitlement?->carried_over_units ?? 0,
                'adjustment_units' => $entitlement?->adjustment_units ?? 0,
                'is_prorated' => $entitlement?->is_prorated ?? false,
            ],
            'annual_leave' => [
                'used_units' => (int) $usedAnnualUnits,
                'used_days' => $usedAnnualUnits / 4,
                'pending_units' => (int) $pendingAnnualUnits,
                'pending_days' => $pendingAnnualUnits / 4,
                'remaining_units' => $remainingUnits,
                'remaining_days' => $remainingUnits / 4,
            ],
            'sickness' => [
                'total_units' => (int) $sicknessUnits,
                'total_days' => $sicknessUnits / 4,
            ],
            'medical' => [
                'total_units' => (int) $medicalUnits,
                'total_days' => $medicalUnits / 4,
            ],
            'birthday' => [
                'eligible' => $birthdayEligible,
                'used' => $birthdayUsed,
            ],
        ];
    }

    public function hasOverlappingRequest(string $employeeId, Carbon $startDate, Carbon $endDate, ?string $excludeRequestId = null): bool
    {
        $query = LeaveRequest::where('employee_id', $employeeId)
            ->whereIn('status', [LeaveRequest::STATUS_PENDING, LeaveRequest::STATUS_APPROVED])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->where('start_date', '<=', $endDate)
                  ->where('end_date', '>=', $startDate);
            });

        if ($excludeRequestId) {
            $query->where('id', '!=', $excludeRequestId);
        }

        return $query->exists();
    }
}
