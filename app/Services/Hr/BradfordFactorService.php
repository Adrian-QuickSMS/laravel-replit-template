<?php

namespace App\Services\Hr;

use App\Models\Hr\BankHoliday;
use App\Models\Hr\EmployeeHrProfile;
use App\Models\Hr\HrSettings;
use App\Models\Hr\LeaveRequest;
use Carbon\Carbon;

class BradfordFactorService
{
    const THRESHOLD_NO_CONCERN = 49;
    const THRESHOLD_LOW = 124;
    const THRESHOLD_MODERATE = 399;
    const THRESHOLD_HIGH = 649;

    const RATINGS = [
        'no_concern' => ['label' => 'No Concern', 'max' => 49, 'color' => '#28a745'],
        'low' => ['label' => 'Low', 'max' => 124, 'color' => '#17a2b8'],
        'moderate' => ['label' => 'Moderate', 'max' => 399, 'color' => '#ffc107'],
        'high' => ['label' => 'High', 'max' => 649, 'color' => '#fd7e14'],
        'critical' => ['label' => 'Critical', 'max' => PHP_INT_MAX, 'color' => '#dc3545'],
    ];

    public function calculateForEmployee(EmployeeHrProfile $employee, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $to = $to ?? Carbon::today();
        $from = $from ?? $to->copy()->subYear();

        $sicknessRequests = LeaveRequest::where('employee_id', $employee->id)
            ->approved()
            ->ofType(LeaveRequest::TYPE_SICKNESS)
            ->where('start_date', '>=', $from)
            ->where('end_date', '<=', $to)
            ->orderBy('start_date')
            ->get();

        $spells = $this->groupIntoSpells($sicknessRequests);

        $S = count($spells);
        $D = 0;
        foreach ($spells as &$spell) {
            $spellDays = 0;
            foreach ($spell['requests'] as $req) {
                $spellDays += (float) $req->duration_days_display;
            }
            $spell['days'] = $spellDays;
            $D += $spellDays;
        }
        unset($spell);

        $score = ($S * $S) * $D;
        $rating = $this->getRating($score);

        return [
            'score' => round($score),
            'spells' => $S,
            'total_days' => round($D, 1),
            'rating' => $rating,
            'rating_label' => self::RATINGS[$rating]['label'],
            'rating_color' => self::RATINGS[$rating]['color'],
            'spell_details' => array_map(function ($spell) {
                return [
                    'start' => $spell['start']->format('d M Y'),
                    'end' => $spell['end']->format('d M Y'),
                    'days' => $spell['days'],
                    'request_count' => count($spell['requests']),
                ];
            }, $spells),
            'period_from' => $from->format('d M Y'),
            'period_to' => $to->format('d M Y'),
            'formula' => "S² × D = {$S}² × " . round($D, 1) . " = " . round($score),
        ];
    }

    public function calculateForAll(?Carbon $from = null, ?Carbon $to = null): array
    {
        $employees = EmployeeHrProfile::active()->with('adminUser')->get();
        $results = [];

        foreach ($employees as $employee) {
            $result = $this->calculateForEmployee($employee, $from?->copy(), $to?->copy());
            $result['employee_id'] = $employee->id;
            $result['employee_name'] = $employee->full_name;
            $result['department'] = $employee->department;
            $results[] = $result;
        }

        usort($results, fn($a, $b) => $b['score'] <=> $a['score']);

        return $results;
    }

    private function groupIntoSpells($requests): array
    {
        if ($requests->isEmpty()) {
            return [];
        }

        $settings = HrSettings::instance();
        $weekendDays = $settings->getWeekendDayNumbers();

        $spells = [];
        $currentSpell = null;

        foreach ($requests as $request) {
            if ($currentSpell === null) {
                $currentSpell = [
                    'start' => $request->start_date,
                    'end' => $request->end_date,
                    'requests' => [$request],
                ];
                continue;
            }

            $nextWorkingDay = $currentSpell['end']->copy()->addDay();
            while (in_array($nextWorkingDay->dayOfWeek, $weekendDays)) {
                $nextWorkingDay->addDay();
            }

            if ($request->start_date->lte($nextWorkingDay)) {
                $currentSpell['end'] = $request->end_date->gt($currentSpell['end'])
                    ? $request->end_date
                    : $currentSpell['end'];
                $currentSpell['requests'][] = $request;
            } else {
                $spells[] = $currentSpell;
                $currentSpell = [
                    'start' => $request->start_date,
                    'end' => $request->end_date,
                    'requests' => [$request],
                ];
            }
        }

        if ($currentSpell) {
            $spells[] = $currentSpell;
        }

        return $spells;
    }

    private function getRating(float $score): string
    {
        if ($score <= self::THRESHOLD_NO_CONCERN) return 'no_concern';
        if ($score <= self::THRESHOLD_LOW) return 'low';
        if ($score <= self::THRESHOLD_MODERATE) return 'moderate';
        if ($score <= self::THRESHOLD_HIGH) return 'high';
        return 'critical';
    }
}
