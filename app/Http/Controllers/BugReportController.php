<?php

namespace App\Http\Controllers;

use App\Services\GitHubIssueService;
use App\Services\HubSpotTicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BugReportController extends Controller
{
    private const AUTO_FIX_CATEGORIES = ['portal_bug', 'ui_layout'];

    private const VALID_CATEGORIES = [
        'portal_bug',
        'ui_layout',
        'performance',
        'sms_issue',
        'rcs_issue',
        'whatsapp_issue',
        'api_webhook',
        'reporting_billing',
        'login_permissions',
        'feature_request',
        'other',
    ];

    private const VALID_SEVERITIES = ['critical', 'high', 'medium', 'low'];

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category'    => 'required|in:' . implode(',', self::VALID_CATEGORIES),
            'severity'    => 'required|in:' . implode(',', self::VALID_SEVERITIES),
            'title'       => 'required|string|min:5|max:200',
            'description' => 'required|string|min:20|max:5000',
            'screenshot'  => 'nullable|file|max:5120|mimes:png,jpg,jpeg,gif,webp',
            'annotated_screenshot' => 'nullable|file|max:5120|mimes:png,jpg,jpeg,gif,webp',
            'console_logs' => 'nullable|string|max:50000',
            'metadata'    => 'required|json',
        ]);

        // Sanitise description
        $validated['description'] = strip_tags($validated['description']);

        // Parse and validate metadata
        $metadata = json_decode($validated['metadata'], true);
        if (!is_array($metadata)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid metadata format',
            ], 422);
        }

        // Enrich metadata from authenticated session (server-side, never trust client)
        $user = $request->user();
        if ($user) {
            $metadata['reporter_name'] = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
            $metadata['reporter_email'] = $user->email ?? '';
            $account = $user->account ?? null;
            if ($account) {
                $metadata['account_id'] = $account->id ?? '';
                $metadata['account_name'] = $account->company_name ?? $account->trading_name ?? '';
            }
        }

        // Parse console logs
        $consoleLogs = null;
        if (!empty($validated['console_logs'])) {
            $consoleLogs = json_decode($validated['console_logs'], true);
            if (is_array($consoleLogs)) {
                $consoleLogs = $this->sanitiseConsoleLogs($consoleLogs);
            } else {
                $consoleLogs = null;
            }
        }

        // Derive product area from page URL
        $productArea = $this->deriveProductArea($metadata['page_url'] ?? '');

        // Build data payload for services
        $ticketService = new HubSpotTicketService();
        $reference = $ticketService->generateReference();

        $data = [
            'category'     => $validated['category'],
            'severity'     => $validated['severity'],
            'title'        => $validated['title'],
            'description'  => $validated['description'],
            'product_area' => $productArea,
            'metadata'     => $metadata,
            'reference'    => $reference,
            'has_screenshot' => $request->hasFile('screenshot') || $request->hasFile('annotated_screenshot'),
            'has_console_logs' => !empty($consoleLogs),
            'console_logs' => $consoleLogs,
        ];

        // Create HubSpot ticket
        $ticketResult = $ticketService->createTicket($data);

        if (!$ticketResult['success']) {
            Log::error('Bug report: HubSpot ticket creation failed', [
                'reference' => $reference,
                'error' => $ticketResult['error'] ?? 'Unknown',
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit bug report. Please try again.',
                'reference' => $reference,
            ], 500);
        }

        $ticketId = $ticketResult['ticket_id'];

        // Handle screenshot uploads
        $this->handleScreenshots($ticketService, $ticketId, $request);

        // Handle console logs as attachment
        if ($consoleLogs && $ticketId) {
            $this->attachConsoleLogs($ticketService, $ticketId, $consoleLogs, $reference);
        }

        // Create GitHub issue for auto-fixable categories
        $issueResult = null;
        if (GitHubIssueService::isAutoFixable($validated['category'])) {
            try {
                $issueService = new GitHubIssueService();
                $issueResult = $issueService->createIssue($data);

                if ($issueResult['success'] ?? false) {
                    Log::info('Bug report: GitHub issue created for auto-fix', [
                        'reference' => $reference,
                        'issue_number' => $issueResult['issue_number'] ?? null,
                    ]);
                }
            } catch (\Throwable $e) {
                // GitHub issue creation should never block the bug report
                Log::warning('Bug report: GitHub issue creation failed (non-blocking)', [
                    'reference' => $reference,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Bug report submitted successfully',
            'reference' => $reference,
            'ticket_id' => $ticketId,
            'auto_fix' => $issueResult['success'] ?? false,
        ]);
    }

    private function handleScreenshots(HubSpotTicketService $service, ?string $ticketId, Request $request): void
    {
        if (!$ticketId) {
            return;
        }

        $files = [
            'screenshot' => 'Original screenshot',
            'annotated_screenshot' => 'Annotated screenshot',
        ];

        foreach ($files as $field => $label) {
            if (!$request->hasFile($field)) {
                continue;
            }

            $file = $request->file($field);
            $tempPath = null;

            try {
                $tempPath = $file->store('temp/bug-reports', 'local');
                $fullPath = Storage::disk('local')->path($tempPath);
                $fileName = $field . '_' . now()->format('Ymd_His') . '.' . $file->getClientOriginalExtension();

                $fileId = $service->uploadFile($fullPath, $fileName);
                if ($fileId) {
                    $service->attachFileToTicket($ticketId, $fileId, $label);
                }
            } catch (\Throwable $e) {
                Log::warning("Bug report: {$field} upload failed (non-blocking)", [
                    'error' => $e->getMessage(),
                ]);
            } finally {
                if ($tempPath) {
                    Storage::disk('local')->delete($tempPath);
                }
            }
        }
    }

    private function attachConsoleLogs(HubSpotTicketService $service, string $ticketId, array $logs, string $reference): void
    {
        try {
            $content = json_encode($logs, JSON_PRETTY_PRINT);
            $tempPath = "temp/bug-reports/console_{$reference}.json";
            Storage::disk('local')->put($tempPath, $content);
            $fullPath = Storage::disk('local')->path($tempPath);

            $fileId = $service->uploadFile($fullPath, "console_logs_{$reference}.json");
            if ($fileId) {
                $service->attachFileToTicket($ticketId, $fileId, 'Browser console logs');
            }

            Storage::disk('local')->delete($tempPath);
        } catch (\Throwable $e) {
            Log::warning('Bug report: console log attachment failed (non-blocking)', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Strip sensitive data from console log entries.
     */
    private function sanitiseConsoleLogs(array $logs): array
    {
        $sensitivePatterns = [
            '/Bearer\s+[A-Za-z0-9\-._~+\/]+=*/i',
            '/api[_-]?key["\s:=]+["\']?[A-Za-z0-9\-._]+/i',
            '/token["\s:=]+["\']?[A-Za-z0-9\-._]+/i',
            '/password["\s:=]+["\']?[^\s"\']+/i',
            '/cookie["\s:=]+["\']?[^\s"\']+/i',
        ];

        $sanitised = [];
        foreach (array_slice($logs, -50) as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $message = $entry['message'] ?? ($entry['args'] ?? '');
            if (is_array($message)) {
                $message = json_encode($message);
            }

            // Redact sensitive patterns
            foreach ($sensitivePatterns as $pattern) {
                $message = preg_replace($pattern, '[REDACTED]', $message);
            }

            $sanitised[] = [
                'level' => $entry['level'] ?? 'log',
                'message' => mb_substr((string) $message, 0, 1000),
                'ts' => $entry['ts'] ?? null,
            ];
        }

        return $sanitised;
    }

    private function deriveProductArea(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?? '/';

        $routeMap = [
            '/messages/send'       => 'Send Message',
            '/messages/inbox'      => 'Inbox',
            '/messages/campaign'   => 'Send Message',
            '/messages'            => 'Messages',
            '/contacts'            => 'Contact Book',
            '/reporting'           => 'Reporting',
            '/purchase'            => 'Billing',
            '/management/template' => 'Templates',
            '/management/api'      => 'API Connections',
            '/management/rcs'      => 'Send Message',
            '/management/numbers'  => 'Send Message',
            '/management'          => 'Management',
            '/account'             => 'Account',
            '/support'             => 'Support',
            '/admin'               => 'Admin Console',
            '/flows'               => 'Flow Builder',
        ];

        // Longest prefix match
        $bestMatch = 'Dashboard';
        $bestLen = 0;

        foreach ($routeMap as $prefix => $area) {
            if (str_starts_with($path, $prefix) && strlen($prefix) > $bestLen) {
                $bestMatch = $area;
                $bestLen = strlen($prefix);
            }
        }

        return $bestMatch;
    }
}
