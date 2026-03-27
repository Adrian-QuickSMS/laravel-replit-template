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

    private const MIME_TO_EXTENSION = [
        'image/png'  => 'png',
        'image/jpeg' => 'jpg',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
    ];

    private HubSpotTicketService $ticketService;
    private GitHubIssueService $issueService;

    public function __construct(HubSpotTicketService $ticketService, GitHubIssueService $issueService)
    {
        $this->ticketService = $ticketService;
        $this->issueService = $issueService;
    }

    /**
     * Handle bug report submission from both customer portal and admin console.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category'    => 'required|in:' . implode(',', self::VALID_CATEGORIES),
            'severity'    => 'required|in:' . implode(',', self::VALID_SEVERITIES),
            'title'       => 'required|string|min:5|max:200',
            'description' => 'required|string|min:20|max:5000',
            'screenshot'  => 'nullable|image|max:5120|mimes:png,jpg,jpeg,gif,webp',
            'annotated_screenshot' => 'nullable|image|max:5120|mimes:png,jpg,jpeg,gif,webp',
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

        // Enrich metadata server-side — supports both customer and admin context
        $metadata = $this->enrichMetadata($metadata, $request);

        // Parse and sanitise console logs
        $consoleLogs = $this->parseConsoleLogs($validated['console_logs'] ?? null);

        // Derive product area from page URL server-side
        $productArea = $this->deriveProductArea($metadata['page_url'] ?? '');

        $reference = $this->ticketService->generateReference();

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
        $ticketResult = $this->ticketService->createTicket($data);

        // Log locally for audit trail regardless of HubSpot success
        Log::channel('single')->info('BUG_REPORT_SUBMITTED', [
            'reference'    => $reference,
            'category'     => $validated['category'],
            'severity'     => $validated['severity'],
            'title'        => $validated['title'],
            'reporter'     => $metadata['reporter_email'] ?? 'unknown',
            'account_id'   => $metadata['account_id'] ?? 'unknown',
            'product_area' => $productArea,
            'page_url'     => $metadata['page_url'] ?? '',
            'hubspot_success' => $ticketResult['success'] ?? false,
            'hubspot_ticket_id' => $ticketResult['ticket_id'] ?? null,
        ]);

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

        // Handle screenshot uploads (non-blocking)
        $this->handleScreenshots($ticketId, $request);

        // Handle console logs as attachment (non-blocking)
        if ($consoleLogs && $ticketId) {
            $this->attachConsoleLogs($ticketId, $consoleLogs, $reference);
        }

        // Create GitHub issue for auto-fixable categories (non-blocking)
        $issueResult = null;
        if (GitHubIssueService::isAutoFixable($validated['category'])) {
            try {
                $issueResult = $this->issueService->createIssue($data);
            } catch (\Throwable $e) {
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

    /**
     * Enrich metadata from authenticated session — supports both customer and admin context.
     * Server-side values always override client-sent values to prevent spoofing.
     */
    private function enrichMetadata(array $metadata, Request $request): array
    {
        $user = $request->user();

        if ($user) {
            // Customer portal context
            $metadata['reporter_name'] = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
            $metadata['reporter_email'] = $user->email ?? '';
            try {
                $account = $user->account;
                if ($account) {
                    $metadata['account_id'] = $account->id ?? '';
                    $metadata['account_name'] = $account->company_name ?? $account->trading_name ?? '';
                }
            } catch (\Throwable $e) {
                // Account relation may not exist
            }
            $metadata['context'] = 'portal';
        } else {
            // Admin console context — read from admin session
            $adminSession = session('admin_auth');
            if ($adminSession && ($adminSession['authenticated'] ?? false)) {
                $metadata['reporter_name'] = $adminSession['name'] ?? 'Admin User';
                $metadata['reporter_email'] = $adminSession['email'] ?? '';
                $metadata['account_id'] = 'admin:' . ($adminSession['admin_id'] ?? '');
                $metadata['account_name'] = 'QuickSMS Admin (' . ($adminSession['role'] ?? 'unknown') . ')';
                $metadata['context'] = 'admin';
            }
        }

        // Sanitise page_url — strip query params that might contain tokens
        if (isset($metadata['page_url'])) {
            $parsed = parse_url($metadata['page_url']);
            $port = isset($parsed['port']) ? ':' . $parsed['port'] : '';
            $metadata['page_url'] = ($parsed['scheme'] ?? 'https') . '://'
                . ($parsed['host'] ?? '') . $port . ($parsed['path'] ?? '/');
        }

        return $metadata;
    }

    private function parseConsoleLogs(?string $raw): ?array
    {
        if (empty($raw)) {
            return null;
        }

        $logs = json_decode($raw, true);
        if (!is_array($logs)) {
            return null;
        }

        return $this->sanitiseConsoleLogs($logs);
    }

    private function handleScreenshots(?string $ticketId, Request $request): void
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

                // Derive extension from actual mime type, not client-supplied filename
                $ext = self::MIME_TO_EXTENSION[$file->getMimeType()] ?? 'png';
                $fileName = $field . '_' . now()->format('Ymd_His') . '.' . $ext;

                $fileId = $this->ticketService->uploadFile($fullPath, $fileName);
                if ($fileId) {
                    $this->ticketService->attachFileToTicket($ticketId, $fileId, $label);
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

    private function attachConsoleLogs(string $ticketId, array $logs, string $reference): void
    {
        try {
            $content = json_encode($logs, JSON_PRETTY_PRINT);
            $tempPath = "temp/bug-reports/console_{$reference}.json";
            Storage::disk('local')->put($tempPath, $content);
            $fullPath = Storage::disk('local')->path($tempPath);

            $fileId = $this->ticketService->uploadFile($fullPath, "console_logs_{$reference}.json");
            if ($fileId) {
                $this->ticketService->attachFileToTicket($ticketId, $fileId, 'Browser console logs');
            }

            Storage::disk('local')->delete($tempPath);
        } catch (\Throwable $e) {
            Log::warning('Bug report: console log attachment failed (non-blocking)', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Sanitise console log entries — deny-by-default approach.
     * Only keeps level + truncated message + timestamp. Strips all JSON objects and redacts secrets.
     */
    private function sanitiseConsoleLogs(array $logs): array
    {
        $sensitivePatterns = [
            '/Bearer\s+[A-Za-z0-9\-._~+\/]+=*/i',
            '/api[_-]?key["\s:=]+["\']?[A-Za-z0-9\-._]+/i',
            '/token["\s:=]+["\']?[A-Za-z0-9\-._]{8,}/i',
            '/password["\s:=]+["\']?[^\s"\']+/i',
            '/cookie["\s:=]+["\']?[^\s"\']+/i',
            '/secret["\s:=]+["\']?[^\s"\']+/i',
            '/authorization["\s:=]+["\']?[^\s"\']+/i',
            '/eyJ[A-Za-z0-9\-_]+\.[A-Za-z0-9\-_]+/i', // JWT tokens
            '/AKIA[A-Z0-9]{16}/i', // AWS access keys
            '/[a-f0-9]{32,}/i', // Long hex strings (session IDs, hashes)
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
            $message = (string) $message;

            // Redact sensitive patterns
            foreach ($sensitivePatterns as $pattern) {
                $message = preg_replace($pattern, '[REDACTED]', $message) ?? $message;
            }

            $sanitised[] = [
                'level' => in_array($entry['level'] ?? '', ['log', 'warn', 'error', 'info']) ? $entry['level'] : 'log',
                'message' => mb_substr($message, 0, 500),
                'ts' => is_numeric($entry['ts'] ?? null) ? (int) $entry['ts'] : null,
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
