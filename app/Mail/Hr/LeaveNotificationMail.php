<?php

namespace App\Mail\Hr;

use App\Models\Hr\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LeaveNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $employeeName;
    public string $action;
    public string $startDate;
    public string $endDate;
    public string $durationDays;
    public string $leaveType;
    public bool $showLeaveType;
    public ?string $comment;

    public function __construct(
        LeaveRequest $request,
        string $action,
        bool $showLeaveType = false
    ) {
        $this->employeeName = $request->employee->full_name;
        $this->action = $action;
        $this->startDate = $request->start_date->format('d M Y');
        $this->endDate = $request->end_date->format('d M Y');
        $this->durationDays = $request->duration_days_display;
        $this->leaveType = $request->leave_type_label;
        $this->showLeaveType = $showLeaveType;
        $this->comment = $request->approval_comment;
    }

    public function build(): self
    {
        // Privacy: Default subject hides leave type details
        $subject = $this->employeeName . ' - On Leave (' . $this->startDate . ' - ' . $this->endDate . ')';

        return $this->subject($subject)
            ->view('emails.hr.leave-notification')
            ->with([
                'subject' => $subject,
            ]);
    }
}
