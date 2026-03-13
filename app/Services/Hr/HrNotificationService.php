<?php

namespace App\Services\Hr;

use App\Models\Hr\HrSettings;
use App\Models\Hr\LeaveRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HrNotificationService
{
    const EVENT_SUBMITTED = 'request_submitted';
    const EVENT_APPROVED = 'request_approved';
    const EVENT_REJECTED = 'request_rejected';
    const EVENT_CANCELLED = 'request_cancelled';
    const EVENT_PURCHASE_REQUESTED = 'purchase_requested';
    const EVENT_PURCHASE_APPROVED = 'purchase_approved';
    const EVENT_PURCHASE_REJECTED = 'purchase_rejected';
    const EVENT_TOIL_GRANTED = 'toil_granted';
    const EVENT_GIFTED_GRANTED = 'gifted_granted';

    public function notify(string $event, LeaveRequest $leaveRequest, ?string $extraInfo = null): void
    {
        try {
            $settings = HrSettings::instance();

            if ($settings->slack_webhook_url) {
                $this->sendSlack($settings->slack_webhook_url, $event, $leaveRequest, $extraInfo);
            }

            if ($settings->teams_webhook_url) {
                $this->sendTeams($settings->teams_webhook_url, $event, $leaveRequest, $extraInfo);
            }
        } catch (\Throwable $e) {
            Log::warning('HR notification failed', ['event' => $event, 'error' => $e->getMessage()]);
        }
    }

    public function notifyAdjustment(string $event, string $employeeName, float $days, int $year, ?string $reason = null): void
    {
        try {
            $settings = HrSettings::instance();

            $text = $this->buildAdjustmentText($event, $employeeName, $days, $year, $reason);

            if ($settings->slack_webhook_url) {
                $this->sendSlackRaw($settings->slack_webhook_url, $text);
            }

            if ($settings->teams_webhook_url) {
                $this->sendTeamsRaw($settings->teams_webhook_url, $text, $event);
            }
        } catch (\Throwable $e) {
            Log::warning('HR adjustment notification failed', ['event' => $event, 'error' => $e->getMessage()]);
        }
    }

    private function sendSlack(string $url, string $event, LeaveRequest $request, ?string $extraInfo): void
    {
        $emoji = match ($event) {
            self::EVENT_SUBMITTED => ':inbox_tray:',
            self::EVENT_APPROVED => ':white_check_mark:',
            self::EVENT_REJECTED => ':x:',
            self::EVENT_CANCELLED => ':grey_question:',
            default => ':calendar:',
        };

        $employeeName = $request->employee?->full_name ?? 'Unknown';
        $dates = $request->start_date->format('d M');
        if (!$request->start_date->isSameDay($request->end_date)) {
            $dates .= ' – ' . $request->end_date->format('d M');
        }

        $title = match ($event) {
            self::EVENT_SUBMITTED => "New leave request submitted",
            self::EVENT_APPROVED => "Leave request approved",
            self::EVENT_REJECTED => "Leave request rejected",
            self::EVENT_CANCELLED => "Leave request cancelled",
            default => "Leave update",
        };

        $blocks = [
            [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => "{$emoji} *{$title}*",
                ],
            ],
            [
                'type' => 'section',
                'fields' => [
                    ['type' => 'mrkdwn', 'text' => "*Employee:*\n{$employeeName}"],
                    ['type' => 'mrkdwn', 'text' => "*Type:*\n{$request->leave_type_label}"],
                    ['type' => 'mrkdwn', 'text' => "*Dates:*\n{$dates}"],
                    ['type' => 'mrkdwn', 'text' => "*Duration:*\n" . number_format($request->duration_days_display, 1) . " days"],
                ],
            ],
        ];

        if ($extraInfo) {
            $blocks[] = [
                'type' => 'context',
                'elements' => [
                    ['type' => 'mrkdwn', 'text' => $extraInfo],
                ],
            ];
        }

        Http::timeout(5)->post($url, ['blocks' => $blocks]);
    }

    private function sendTeams(string $url, string $event, LeaveRequest $request, ?string $extraInfo): void
    {
        $employeeName = $request->employee?->full_name ?? 'Unknown';
        $dates = $request->start_date->format('d M');
        if (!$request->start_date->isSameDay($request->end_date)) {
            $dates .= ' – ' . $request->end_date->format('d M');
        }

        $title = match ($event) {
            self::EVENT_SUBMITTED => "New leave request submitted",
            self::EVENT_APPROVED => "Leave request approved",
            self::EVENT_REJECTED => "Leave request rejected",
            self::EVENT_CANCELLED => "Leave request cancelled",
            default => "Leave update",
        };

        $color = match ($event) {
            self::EVENT_SUBMITTED => '0078D7',
            self::EVENT_APPROVED => '28a745',
            self::EVENT_REJECTED => 'dc3545',
            self::EVENT_CANCELLED => '6c757d',
            default => '0078D7',
        };

        $card = [
            'type' => 'message',
            'attachments' => [[
                'contentType' => 'application/vnd.microsoft.card.adaptive',
                'content' => [
                    '$schema' => 'http://adaptivecards.io/schemas/adaptive-card.json',
                    'type' => 'AdaptiveCard',
                    'version' => '1.4',
                    'body' => [
                        [
                            'type' => 'TextBlock',
                            'text' => $title,
                            'weight' => 'Bolder',
                            'size' => 'Medium',
                            'color' => 'Default',
                        ],
                        [
                            'type' => 'FactSet',
                            'facts' => [
                                ['title' => 'Employee', 'value' => $employeeName],
                                ['title' => 'Type', 'value' => $request->leave_type_label],
                                ['title' => 'Dates', 'value' => $dates],
                                ['title' => 'Duration', 'value' => number_format($request->duration_days_display, 1) . ' days'],
                            ],
                        ],
                    ],
                ],
            ]],
        ];

        if ($extraInfo) {
            $card['attachments'][0]['content']['body'][] = [
                'type' => 'TextBlock',
                'text' => $extraInfo,
                'size' => 'Small',
                'isSubtle' => true,
            ];
        }

        Http::timeout(5)->post($url, $card);
    }

    private function buildAdjustmentText(string $event, string $employeeName, float $days, int $year, ?string $reason): string
    {
        $action = match ($event) {
            self::EVENT_PURCHASE_REQUESTED => "requested to purchase {$days} day(s) of holiday",
            self::EVENT_PURCHASE_APPROVED => "had {$days} day(s) purchased holiday approved",
            self::EVENT_PURCHASE_REJECTED => "had {$days} day(s) purchased holiday rejected",
            self::EVENT_TOIL_GRANTED => "was granted {$days} day(s) TOIL",
            self::EVENT_GIFTED_GRANTED => "was gifted {$days} day(s) of holiday",
            default => "had a holiday adjustment ({$days} days)",
        };

        $text = "{$employeeName} {$action} for {$year}";
        if ($reason) {
            $text .= " — {$reason}";
        }
        return $text;
    }

    private function sendSlackRaw(string $url, string $text): void
    {
        Http::timeout(5)->post($url, ['text' => $text]);
    }

    private function sendTeamsRaw(string $url, string $text, string $event): void
    {
        $card = [
            'type' => 'message',
            'attachments' => [[
                'contentType' => 'application/vnd.microsoft.card.adaptive',
                'content' => [
                    '$schema' => 'http://adaptivecards.io/schemas/adaptive-card.json',
                    'type' => 'AdaptiveCard',
                    'version' => '1.4',
                    'body' => [[
                        'type' => 'TextBlock',
                        'text' => $text,
                        'wrap' => true,
                    ]],
                ],
            ]],
        ];

        Http::timeout(5)->post($url, $card);
    }
}
