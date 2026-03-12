<?php

namespace App\Services\Hr;

use App\Models\Hr\CompanyHrSettings;
use App\Models\Hr\EmployeeHrProfile;
use App\Models\Hr\LeaveEntitlement;
use App\Models\Hr\LeaveRequest;
use Carbon\Carbon;

class LeaveCalculationService
{
    /**
     * Calculate prorated annual leave entitlement for an employee.
     *
     * Uses calendar year (Jan 1 - Dec 31).
     * Rounds UP to nearest quarter day.
     *
     * @param int $fullEntitlementUnits Full-year entitlement in quarter-day units
     * @param Carbon $startDate Employee start date
     * @param int $year The holiday year
     * @return array{units: int, is_prorated: bool, note: string}
     */
    public function calculateProratedEntitlement(int $fullEntitlementUnits, Carbon $startDate, int $year): array
    {
        $yearStart = Carbon::create($year, 1, 1);
        $yearEnd = Carbon::create($year, 12, 31);

        // If employee started before or on Jan 1 of the year, full entitlement
        if ($startDate->lte($yearStart)) {
            return [
                'units' => $fullEntitlementUnits,
                'is_prorated' => false,
                'note' => 'Full year entitlement',
            ];
        }

        // If employee started after Dec 31, zero entitlement
        if ($startDate->gt($yearEnd)) {
            return [
                'units' => 0,
                'is_prorated' => true,
                'note' => 'Employee starts after end of holiday year',
            ];
        }

        // Calculate remaining calendar days from start date to Dec 31
        $totalDaysInYear = $yearStart->diffInDays($yearEnd) + 1; // 365 or 366
        $remainingDays = $startDate->diffInDays($yearEnd) + 1; // inclusive

        // Prorate: (remaining / total) * full entitlement
        $proratedExact = ($remainingDays / $totalDaysInYear) * $fullEntitlementUnits;

        // Round UP to nearest quarter-day unit (1 unit)
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

    /**
     * Count working days between two dates, excluding weekends and bank holidays.
     */
    public function countWorkingDays(Carbon $startDate, Carbon $endDate, array $weekendDays = [6, 0], array $bankHolidayDates = []): int
    {
        $workingDays = 0;
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            $dayOfWeek = $current->dayOfWeek; // 0=Sunday, 6=Saturday
            $isWeekend = in_array($dayOfWeek, $weekendDays);
            $isBankHoliday = in_array($current->format('Y-m-d'), $bankHolidayDates);

            if (!$isWeekend && !$isBankHoliday) {
                $workingDays++;
            }

            $current->addDay();
        }

        return $workingDays;
    }

    /**
     * Calculate duration units based on day portion and working days.
     *
     * @param string $dayPortion full, half_am, half_pm, quarter
     * @param int $workingDays Number of working days in range
     * @return array{units: int, display_days: float}
     */
    public function calculateDurationUnits(string $dayPortion, int $workingDays): array
    {
        $unitsPerDay = match ($dayPortion) {
            'full' => 4,
            'half_am', 'half_pm' => 2,
            'quarter' => 1,
            default => 4,
        };

        // For multi-day requests with partial days, only the first/last day can be partial
        // For Phase 1, partial only applies to single-day requests
        if ($workingDays > 1 && $dayPortion !== 'full') {
            // Multi-day partial: first day is partial, rest are full
            $units = $unitsPerDay + (($workingDays - 1) * 4);
        } else {
            $units = $workingDays * $unitsPerDay;
        }

        return [
            'units' => $units,
            'display_days' => $units / 4,
        ];
    }

    /**
     * Get leave balance summary for an employee in a given year.
     */
    public function getBalanceSummary(EmployeeHrProfile $employee, int $year): array
    {
        $entitlement = $employee->entitlementForYear($year);

        $totalEntitlementUnits = $entitlement ? $entitlement->total_available_units : 0;

        // Only APPROVED annual leave deducts from balance
        $usedAnnualUnits = LeaveRequest::where('employee_id', $employee->id)
            ->where('leave_type', LeaveRequest::TYPE_ANNUAL)
            ->where('status', LeaveRequest::STATUS_APPROVED)
            ->whereYear('start_date', $year)
            ->sum('duration_units');

        // Pending annual leave (visible but not deducted)
        $pendingAnnualUnits = LeaveRequest::where('employee_id', $employee->id)
            ->where('leave_type', LeaveRequest::TYPE_ANNUAL)
            ->where('status', LeaveRequest::STATUS_PENDING)
            ->whereYear('start_date', $year)
            ->sum('duration_units');

        // Sickness totals (all approved)
        $sicknessUnits = LeaveRequest::where('employee_id', $employee->id)
            ->where('leave_type', LeaveRequest::TYPE_SICKNESS)
            ->where('status', LeaveRequest::STATUS_APPROVED)
            ->whereYear('start_date', $year)
            ->sum('duration_units');

        // Medical totals (all approved)
        $medicalUnits = LeaveRequest::where('employee_id', $employee->id)
            ->where('leave_type', LeaveRequest::TYPE_MEDICAL)
            ->where('status', LeaveRequest::STATUS_APPROVED)
            ->whereYear('start_date', $year)
            ->sum('duration_units');

        $remainingUnits = $totalEntitlementUnits - $usedAnnualUnits;

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
        ];
    }

    /**
     * Check for overlapping leave requests.
     */
    public function hasOverlappingRequest(string $employeeId, Carbon $startDate, Carbon $endDate, ?string $excludeRequestId = null): bool
    {
        $query = LeaveRequest::where('employee_id', $employeeId)
            ->whereIn('status', [LeaveRequest::STATUS_PENDING, LeaveRequest::STATUS_APPROVED])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->where(function ($inner) use ($startDate, $endDate) {
                    $inner->where('start_date', '<=', $endDate)
                          ->where('end_date', '>=', $startDate);
                });
            });

        if ($excludeRequestId) {
            $query->where('id', '!=', $excludeRequestId);
        }

        return $query->exists();
    }
}
