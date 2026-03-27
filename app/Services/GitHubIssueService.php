<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GitHubIssueService
{
    private ?string $token;
    private string $repo;
    private int $timeout;

    private const AUTO_FIX_CATEGORIES = ['portal_bug', 'ui_layout'];

    private array $categoryLabels = [
        'portal_bug'       => 'Portal Bug',
        'ui_layout'        => 'UI/Layout Issue',
        'performance'      => 'Performance Issue',
        'sms_issue'        => 'SMS Issue',
        'rcs_issue'        => 'RCS Issue',
        'whatsapp_issue'   => 'WhatsApp Issue',
        'api_webhook'      => 'API/Webhook Issue',
        'reporting_billing' => 'Reporting/Billing Issue',
        'login_permissions' => 'Login/Permissions Issue',
        'feature_request'  => 'Feature Request',
        'other'            => 'Other',
    ];

    public function __construct()
    {
        $this->token = config('services.github.token');
        $this->repo = config('services.github.repo', 'adrian-quicksms/laravel-replit-template');
        $this->timeout = config('services.bug_report.http_timeout', 10);
    }

    public function isConfigured(): bool
    {
        return !empty($this->token);
    }

    /**
     * Check if a category is eligible for auto-fix.
     */
    public static function isAutoFixable(string $category): bool
    {
        return in_array($category, self::AUTO_FIX_CATEGORIES, true);
    }

    /**
     * Create a GitHub issue for a bug report.
     */
    public function createIssue(array $data): array
    {
        $reference = $data['reference'] ?? 'BUG-unknown';

        if (!$this->isConfigured()) {
            Log::warning('GitHub token not configured - mock issue created', [
                'reference' => $reference,
            ]);
            return $this->getMockIssueResponse($data, $reference);
        }

        try {
            $title = $this->formatIssueTitle($data);
            $body = $this->formatIssueBody($data, $reference);
            $labels = $this->getLabels($data);

            $response = Http::timeout($this->timeout)->withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Accept' => 'application/vnd.github+json',
                'X-GitHub-Api-Version' => '2022-11-28',
            ])->post("https://api.github.com/repos/{$this->repo}/issues", [
                'title' => $title,
                'body' => $body,
                'labels' => $labels,
            ]);

            if ($response->failed()) {
                Log::error('GitHub Issue API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'reference' => $reference,
                ]);
                return [
                    'success' => false,
                    'error' => 'Failed to create GitHub issue',
                    'reference' => $reference,
                ];
            }

            $issueData = $response->json();

            Log::info('GitHub issue created for bug report', [
                'issue_number' => $issueData['number'] ?? null,
                'reference' => $reference,
                'category' => $data['category'] ?? 'unknown',
            ]);

            return [
                'success' => true,
                'issue_number' => $issueData['number'] ?? null,
                'issue_url' => $issueData['html_url'] ?? null,
                'reference' => $reference,
            ];

        } catch (\Exception $e) {
            Log::error('GitHub Issue API exception', [
                'message' => $e->getMessage(),
                'reference' => $reference,
            ]);
            return [
                'success' => false,
                'error' => 'Error creating GitHub issue: ' . $e->getMessage(),
                'reference' => $reference,
            ];
        }
    }

    private function formatIssueTitle(array $data): string
    {
        $category = $this->categoryLabels[$data['category'] ?? 'other'] ?? 'Bug';
        $severity = ucfirst($data['severity'] ?? 'medium');
        $title = $data['title'] ?? 'Bug Report';

        return "[{$category}][{$severity}] {$title}";
    }

    private function formatIssueBody(array $data, string $reference): string
    {
        $metadata = $data['metadata'] ?? [];

        $body = "## Bug Report: {$reference}\n\n";
        $body .= "**Category:** " . ($this->categoryLabels[$data['category'] ?? 'other'] ?? 'Other') . "\n";
        $body .= "**Severity:** " . ucfirst($data['severity'] ?? 'N/A') . "\n";
        $body .= "**Product Area:** " . ($data['product_area'] ?? 'Unknown') . "\n\n";

        $body .= "### Description\n\n";
        $body .= ($data['description'] ?? 'No description provided.') . "\n\n";

        $body .= "### Environment\n\n";
        $body .= "| Field | Value |\n";
        $body .= "|-------|-------|\n";
        $body .= "| Page URL | " . ($metadata['page_url'] ?? 'N/A') . " |\n";
        $body .= "| Browser | " . ($metadata['browser'] ?? 'N/A') . " |\n";
        $body .= "| OS | " . ($metadata['os'] ?? 'N/A') . " |\n";
        $body .= "| Viewport | " . ($metadata['viewport'] ?? 'N/A') . " |\n";
        $body .= "| Environment | " . ($metadata['environment'] ?? 'N/A') . " |\n";
        $body .= "| Timestamp | " . ($metadata['timestamp'] ?? 'N/A') . " |\n\n";

        $body .= "### Reporter\n\n";
        $body .= "- **Name:** " . ($metadata['reporter_name'] ?? 'N/A') . "\n";
        $body .= "- **Email:** " . ($metadata['reporter_email'] ?? 'N/A') . "\n";
        $body .= "- **Account:** " . ($metadata['account_name'] ?? 'N/A') . "\n\n";

        if (!empty($data['console_logs'])) {
            $body .= "### Console Logs\n\n";
            $body .= "<details>\n<summary>Last 50 console messages</summary>\n\n";
            $body .= "```json\n" . json_encode($data['console_logs'], JSON_PRETTY_PRINT) . "\n```\n\n";
            $body .= "</details>\n\n";
        }

        $body .= "---\n\n";
        $body .= "**Reference:** `{$reference}`\n\n";
        $body .= "> This issue was automatically created from a portal bug report.\n";
        $body .= "> Claude Code may attempt an automated fix for this issue.\n";

        return $body;
    }

    private function getLabels(array $data): array
    {
        $labels = ['bug-report'];

        if (self::isAutoFixable($data['category'] ?? '')) {
            $labels[] = 'auto-fix';
        }

        $categoryLabelMap = [
            'portal_bug' => 'portal',
            'ui_layout'  => 'ui',
        ];

        if (isset($categoryLabelMap[$data['category'] ?? ''])) {
            $labels[] = $categoryLabelMap[$data['category']];
        }

        $severity = $data['severity'] ?? 'medium';
        if (in_array($severity, ['critical', 'high'])) {
            $labels[] = 'priority-high';
        }

        return $labels;
    }

    private function getMockIssueResponse(array $data, string $reference): array
    {
        return [
            'success' => true,
            'isMockData' => true,
            'issue_number' => crc32($reference) % 10000,
            'issue_url' => "https://github.com/{$this->repo}/issues/mock",
            'reference' => $reference,
        ];
    }
}
