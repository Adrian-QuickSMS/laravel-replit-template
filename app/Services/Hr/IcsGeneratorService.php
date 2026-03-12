<?php

namespace App\Services\Hr;

use App\Models\Hr\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Support\Str;

class IcsGeneratorService
{
    /**
     * Generate an ICS calendar event for an approved leave request.
     *
     * Privacy: Uses generic "[Name] - On Leave" subject for annual leave.
     * Sickness and medical use the same generic subject by default.
     */
    public function generateIcs(LeaveRequest $request, bool $showLeaveType = false): string
    {
        $employee = $request->employee;
        $name = $employee->full_name;

        // Privacy: Default subject hides leave type
        $summary = "{$name} - On Leave";
        if ($showLeaveType && $request->leave_type === LeaveRequest::TYPE_ANNUAL) {
            $summary = "{$name} - Annual Leave";
        }

        $uid = Str::uuid()->toString();
        $now = Carbon::now('UTC')->format('Ymd\THis\Z');
        $startDate = $request->start_date->format('Ymd');

        // For all-day events, DTEND should be the day AFTER the last day
        $endDate = $request->end_date->copy()->addDay()->format('Ymd');

        $description = sprintf(
            'Leave request for %s\\nType: %s\\nDuration: %s days\\nStatus: %s',
            $name,
            $request->leave_type_label,
            $request->duration_days_display,
            ucfirst($request->status)
        );

        if ($request->employee_note) {
            $description .= '\\nNote: ' . str_replace(["\r\n", "\n", "\r"], '\\n', $request->employee_note);
        }

        $ics = "BEGIN:VCALENDAR\r\n";
        $ics .= "VERSION:2.0\r\n";
        $ics .= "PRODID:-//QuickSMS//HR Leave Management//EN\r\n";
        $ics .= "CALSCALE:GREGORIAN\r\n";
        $ics .= "METHOD:PUBLISH\r\n";
        $ics .= "BEGIN:VEVENT\r\n";
        $ics .= "UID:{$uid}\r\n";
        $ics .= "DTSTAMP:{$now}\r\n";
        $ics .= "DTSTART;VALUE=DATE:{$startDate}\r\n";
        $ics .= "DTEND;VALUE=DATE:{$endDate}\r\n";
        $ics .= "SUMMARY:{$summary}\r\n";
        $ics .= "DESCRIPTION:{$description}\r\n";
        $ics .= "TRANSP:OPAQUE\r\n";
        $ics .= "STATUS:CONFIRMED\r\n";
        $ics .= "END:VEVENT\r\n";
        $ics .= "END:VCALENDAR\r\n";

        return $ics;
    }

    /**
     * Generate ICS content and return as a downloadable response.
     */
    public function downloadResponse(LeaveRequest $request, bool $showLeaveType = false)
    {
        $ics = $this->generateIcs($request, $showLeaveType);
        $filename = sprintf(
            'leave-%s-%s.ics',
            Str::slug($request->employee->full_name),
            $request->start_date->format('Y-m-d')
        );

        return response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
