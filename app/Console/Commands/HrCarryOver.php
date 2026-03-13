<?php

namespace App\Console\Commands;

use App\Models\Hr\EmployeeHrProfile;
use App\Models\Hr\HolidayAdjustmentRequest;
use App\Models\Hr\HrSettings;
use App\Models\Hr\LeaveAuditLog;
use App\Models\Hr\LeaveRequest;
use App\Services\Hr\LeaveCalculationService;
use App\Services\Hr\LeaveRequestService;
use Illuminate\Console\Command;

class HrCarryOver extends Command
{
    protected $signature = 'hr:carry-over
        {--year= : The closing year to carry over FROM (defaults to previous year)}
        {--dry-run : Show what would happen without making changes}';

    protected $description = 'Carry over unused annual leave from one year to the next, respecting the 5-day additional pool cap';

    public function handle(LeaveCalculationService $calculator, LeaveRequestService $leaveService): int
    {
        $settings = HrSettings::instance();
        if (!$settings->allow_carry_over) {
            $this->error('Carry-over is not enabled in HR settings.');
            return self::FAILURE;
        }

        $closingYear = (int) ($this->option('year') ?? (date('Y') - 1));
        $newYear = $closingYear + 1;
        $isDryRun = $this->option('dry-run');

        $this->info(($isDryRun ? '[DRY RUN] ' : '') . "Carrying over unused leave from {$closingYear} → {$newYear}");
        $this->newLine();

        $employees = EmployeeHrProfile::active()->with('adminUser')->get();
        $results = [];

        foreach ($employees as $employee) {
            $balance = $calculator->getBalanceSummary($employee, $closingYear);
            $remainingUnits = max(0, $balance['annual_leave']['remaining_units']);

            if ($remainingUnits <= 0) {
                $results[] = [
                    'name' => $employee->full_name,
                    'unused_days' => 0,
                    'carry_over_days' => 0,
                    'reason' => 'No unused leave',
                ];
                continue;
            }

            $newYearEntitlement = $employee->entitlementForYear($newYear);
            $existingAdditional = $newYearEntitlement
                ? $newYearEntitlement->additional_units_used
                : 0;

            $maxAdditional = $settings->max_additional_units;
            $roomInPool = max(0, $maxAdditional - $existingAdditional);

            $carryOverUnits = min($remainingUnits, $roomInPool);

            $alreadyCarried = HolidayAdjustmentRequest::where('employee_id', $employee->id)
                ->where('type', HolidayAdjustmentRequest::TYPE_CARRY_OVER)
                ->where('year', $newYear)
                ->where('status', HolidayAdjustmentRequest::STATUS_APPROVED)
                ->exists();

            if ($alreadyCarried) {
                $results[] = [
                    'name' => $employee->full_name,
                    'unused_days' => $remainingUnits / 4,
                    'carry_over_days' => 0,
                    'reason' => 'Already carried over for ' . $newYear,
                ];
                continue;
            }

            if ($carryOverUnits <= 0) {
                $results[] = [
                    'name' => $employee->full_name,
                    'unused_days' => $remainingUnits / 4,
                    'carry_over_days' => 0,
                    'reason' => 'Additional pool full',
                ];
                continue;
            }

            $results[] = [
                'name' => $employee->full_name,
                'unused_days' => $remainingUnits / 4,
                'carry_over_days' => $carryOverUnits / 4,
                'reason' => $carryOverUnits < $remainingUnits ? 'Capped by pool limit' : 'Full carry-over',
            ];

            if (!$isDryRun) {
                $entitlement = $leaveService->ensureEntitlement($employee, $newYear);
                $entitlement->increment('carried_over_units', $carryOverUnits);

                HolidayAdjustmentRequest::create([
                    'employee_id' => $employee->id,
                    'type' => HolidayAdjustmentRequest::TYPE_CARRY_OVER,
                    'status' => HolidayAdjustmentRequest::STATUS_APPROVED,
                    'units' => $carryOverUnits,
                    'year' => $newYear,
                    'reason' => "Carry-over from {$closingYear}: " . ($carryOverUnits / 4) . " days",
                    'approved_at' => now(),
                ]);

                try {
                    LeaveAuditLog::record(
                        $employee->id,
                        'carry_over',
                        $employee->id,
                        null,
                        json_encode(['year' => $closingYear, 'unused_units' => $remainingUnits]),
                        json_encode(['year' => $newYear, 'carried_units' => $carryOverUnits]),
                        "Carry-over from {$closingYear}"
                    );
                } catch (\Throwable $e) {}
            }
        }

        $this->table(
            ['Employee', 'Unused (days)', 'Carry Over (days)', 'Note'],
            array_map(fn($r) => [
                $r['name'],
                number_format($r['unused_days'], 1),
                number_format($r['carry_over_days'], 1),
                $r['reason'],
            ], $results)
        );

        $totalCarried = array_sum(array_column($results, 'carry_over_days'));
        $this->newLine();
        $this->info(($isDryRun ? '[DRY RUN] ' : '') . "Total carried over: " . number_format($totalCarried, 1) . " days across " . count($employees) . " employees");

        return self::SUCCESS;
    }
}
