<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class HubSpotTicketService
{
    private string $ticketsUrl = 'https://api.hubapi.com/crm/v3/objects/tickets';
    private string $filesUrl = 'https://api.hubapi.com/files/v3/files';
    private string $notesUrl = 'https://api.hubapi.com/crm/v3/objects/notes';
    private string $associationsUrl = 'https://api.hubapi.com/crm/v4/objects';
    private ?string $accessToken;
    private string $pipelineId;
    private string $pipelineStageId;

    // HubSpot pipeline stage IDs — these are instance-specific defaults
    // Override via HUBSPOT_TICKET_STAGE_* env vars if your HubSpot has different stage IDs
    private array $stageMap = [
        'new'               => '1',
        'in_progress'       => '2',
        'fix_ready'         => '3',
        'ready_for_testing' => '4',
        'verified'          => '5',
        'closed'            => '6',
    ];

    private array $severityToPriority = [
        'critical' => 'URGENT',
        'high'     => 'HIGH',
        'medium'   => 'MEDIUM',
        'low'      => 'LOW',
    ];

    public function __construct()
    {
        $this->accessToken = config('services.hubspot.access_token');
        $this->pipelineId = config('services.hubspot.ticket_pipeline_id', '0');
        $this->pipelineStageId = config('services.hubspot.ticket_pipeline_stage_id', '1');
    }

    public function isConfigured(): bool
    {
        return !empty($this->accessToken);
    }

    /**
     * Generate a unique bug reference ID.
     */
    public function generateReference(): string
    {
        return 'BUG-' . now()->format('Ymd') . '-' . Str::random(6);
    }

    /**
     * Create a HubSpot ticket from bug report data.
     */
    public function createTicket(array $data): array
    {
        $reference = $data['reference'] ?? $this->generateReference();

        if (!$this->isConfigured()) {
            Log::warning('HubSpot access token not configured - mock ticket created', [
                'reference' => $reference,
            ]);
            return $this->getMockTicketResponse($data, $reference);
        }

        try {
            $ticketName = $this->formatTicketName($data);
            $ticketBody = $this->formatTicketBody($data, $reference);
            $priority = $this->severityToPriority[$data['severity'] ?? 'medium'] ?? 'MEDIUM';

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ])->post($this->ticketsUrl, [
                'properties' => [
                    'subject'            => $ticketName,
                    'content'            => $ticketBody,
                    'hs_pipeline'        => $this->pipelineId,
                    'hs_pipeline_stage'  => $this->pipelineStageId,
                    'hs_ticket_priority' => $priority,
                ],
            ]);

            if ($response->failed()) {
                Log::error('HubSpot Ticket API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'reference' => $reference,
                ]);
                return [
                    'success' => false,
                    'error' => 'Failed to create HubSpot ticket',
                    'reference' => $reference,
                ];
            }

            $ticketData = $response->json();
            $ticketId = $ticketData['id'] ?? null;

            Log::info('HubSpot ticket created', [
                'ticket_id' => $ticketId,
                'reference' => $reference,
                'category' => $data['category'] ?? 'unknown',
            ]);

            return [
                'success' => true,
                'ticket_id' => $ticketId,
                'reference' => $reference,
            ];

        } catch (\Exception $e) {
            Log::error('HubSpot Ticket API exception', [
                'message' => $e->getMessage(),
                'reference' => $reference,
            ]);
            return [
                'success' => false,
                'error' => 'Error creating ticket: ' . $e->getMessage(),
                'reference' => $reference,
            ];
        }
    }

    /**
     * Upload a file to HubSpot Files API.
     */
    public function uploadFile(string $filePath, string $fileName): ?string
    {
        if (!$this->isConfigured()) {
            Log::warning('HubSpot not configured - skipping file upload');
            return 'mock_file_' . Str::random(8);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
            ])->attach(
                'file', file_get_contents($filePath), $fileName
            )->post($this->filesUrl, [
                'options' => json_encode([
                    'access' => 'PRIVATE',
                    'overwrite' => false,
                ]),
                'folderPath' => '/bug-reports',
            ]);

            if ($response->failed()) {
                Log::error('HubSpot File upload error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $fileData = $response->json();
            $fileId = $fileData['id'] ?? null;

            Log::info('HubSpot file uploaded', ['file_id' => $fileId, 'name' => $fileName]);
            return $fileId;

        } catch (\Exception $e) {
            Log::error('HubSpot File upload exception', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Attach an uploaded file to a ticket via a Note engagement.
     */
    public function attachFileToTicket(string $ticketId, string $fileId, string $noteBody = ''): bool
    {
        if (!$this->isConfigured()) {
            return true;
        }

        try {
            // Create a note with the file attachment
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ])->post($this->notesUrl, [
                'properties' => [
                    'hs_note_body' => $noteBody ?: 'Screenshot attached to bug report',
                    'hs_attachment_ids' => $fileId,
                ],
            ]);

            if ($response->failed()) {
                Log::error('HubSpot Note creation error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return false;
            }

            $noteId = $response->json()['id'] ?? null;

            if (!$noteId) {
                return false;
            }

            // Associate the note with the ticket
            $assocResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ])->put("{$this->associationsUrl}/notes/{$noteId}/associations/tickets/{$ticketId}", [
                [
                    'associationCategory' => 'HUBSPOT_DEFINED',
                    'associationTypeId' => 18, // Note to Ticket
                ],
            ]);

            if ($assocResponse->failed()) {
                Log::warning('HubSpot Note-Ticket association failed', [
                    'note_id' => $noteId,
                    'ticket_id' => $ticketId,
                    'status' => $assocResponse->status(),
                ]);
                return false;
            }

            Log::info('File attached to HubSpot ticket', [
                'ticket_id' => $ticketId,
                'file_id' => $fileId,
                'note_id' => $noteId,
            ]);
            return true;

        } catch (\Exception $e) {
            Log::error('HubSpot attach file exception', ['message' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Update ticket pipeline stage.
     */
    public function updateTicketStage(string $ticketId, string $stage): bool
    {
        if (!$this->isConfigured()) {
            Log::info('HubSpot not configured - mock stage update', [
                'ticket_id' => $ticketId,
                'stage' => $stage,
            ]);
            return true;
        }

        $stageId = $this->stageMap[$stage] ?? $stage;

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ])->patch("{$this->ticketsUrl}/{$ticketId}", [
                'properties' => [
                    'hs_pipeline_stage' => $stageId,
                ],
            ]);

            if ($response->failed()) {
                Log::error('HubSpot ticket stage update error', [
                    'ticket_id' => $ticketId,
                    'stage' => $stage,
                    'status' => $response->status(),
                ]);
                return false;
            }

            Log::info('HubSpot ticket stage updated', [
                'ticket_id' => $ticketId,
                'stage' => $stage,
            ]);
            return true;

        } catch (\Exception $e) {
            Log::error('HubSpot stage update exception', ['message' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Add a timeline note to a ticket.
     */
    public function addTicketNote(string $ticketId, string $body): bool
    {
        if (!$this->isConfigured()) {
            Log::info('HubSpot not configured - mock note added', [
                'ticket_id' => $ticketId,
            ]);
            return true;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ])->post($this->notesUrl, [
                'properties' => [
                    'hs_note_body' => $body,
                ],
            ]);

            if ($response->failed()) {
                Log::error('HubSpot note creation error', [
                    'ticket_id' => $ticketId,
                    'status' => $response->status(),
                ]);
                return false;
            }

            $noteId = $response->json()['id'] ?? null;

            if ($noteId) {
                Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Content-Type' => 'application/json',
                ])->put("{$this->associationsUrl}/notes/{$noteId}/associations/tickets/{$ticketId}", [
                    [
                        'associationCategory' => 'HUBSPOT_DEFINED',
                        'associationTypeId' => 18,
                    ],
                ]);
            }

            Log::info('HubSpot note added to ticket', [
                'ticket_id' => $ticketId,
                'note_id' => $noteId,
            ]);
            return true;

        } catch (\Exception $e) {
            Log::error('HubSpot note exception', ['message' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Search for a ticket by bug reference ID.
     */
    public function findTicketByReference(string $reference): ?string
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ])->post('https://api.hubapi.com/crm/v3/objects/tickets/search', [
                'filterGroups' => [[
                    'filters' => [[
                        'propertyName' => 'content',
                        'operator' => 'CONTAINS_TOKEN',
                        'value' => $reference,
                    ]],
                ]],
                'limit' => 1,
            ]);

            if ($response->failed()) {
                return null;
            }

            $results = $response->json()['results'] ?? [];
            return $results[0]['id'] ?? null;

        } catch (\Exception $e) {
            Log::error('HubSpot ticket search exception', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Format ticket name: [Category][Product Area][Severity] Title
     */
    private function formatTicketName(array $data): string
    {
        $categoryLabels = [
            'portal_bug'       => 'Portal Bug',
            'ui_layout'        => 'UI/Layout',
            'performance'      => 'Performance',
            'sms_issue'        => 'SMS',
            'rcs_issue'        => 'RCS',
            'whatsapp_issue'   => 'WhatsApp',
            'api_webhook'      => 'API/Webhook',
            'reporting_billing' => 'Reporting/Billing',
            'login_permissions' => 'Login/Permissions',
            'feature_request'  => 'Feature Request',
            'other'            => 'Other',
        ];

        $category = $categoryLabels[$data['category'] ?? 'other'] ?? 'Other';
        $productArea = $data['product_area'] ?? 'Unknown';
        $severity = ucfirst($data['severity'] ?? 'medium');
        $title = $data['title'] ?? 'Bug Report';

        return "[{$category}][{$productArea}][{$severity}] {$title}";
    }

    /**
     * Format structured ticket description body.
     */
    private function formatTicketBody(array $data, string $reference): string
    {
        $metadata = $data['metadata'] ?? [];

        $body = "## Bug Report — {$reference}\n\n";
        $body .= "**Category:** " . ($data['category'] ?? 'N/A') . "\n";
        $body .= "**Severity:** " . ucfirst($data['severity'] ?? 'N/A') . "\n";
        $body .= "**Product Area:** " . ($data['product_area'] ?? 'N/A') . "\n\n";

        $body .= "### Description\n";
        $body .= ($data['description'] ?? 'No description provided.') . "\n\n";

        $body .= "### Reporter\n";
        $body .= "- **Name:** " . ($metadata['reporter_name'] ?? 'N/A') . "\n";
        $body .= "- **Email:** " . ($metadata['reporter_email'] ?? 'N/A') . "\n";
        $body .= "- **Account:** " . ($metadata['account_name'] ?? 'N/A') . " (" . ($metadata['account_id'] ?? 'N/A') . ")\n\n";

        $body .= "### Environment\n";
        $body .= "- **Page URL:** " . ($metadata['page_url'] ?? 'N/A') . "\n";
        $body .= "- **Timestamp:** " . ($metadata['timestamp'] ?? 'N/A') . "\n";
        $body .= "- **Browser:** " . ($metadata['browser'] ?? 'N/A') . "\n";
        $body .= "- **OS:** " . ($metadata['os'] ?? 'N/A') . "\n";
        $body .= "- **Viewport:** " . ($metadata['viewport'] ?? 'N/A') . "\n";
        $body .= "- **Environment:** " . ($metadata['environment'] ?? 'N/A') . "\n\n";

        $body .= "**Screenshot attached:** " . (!empty($data['has_screenshot']) ? 'Yes' : 'No') . "\n";
        $body .= "**Console logs attached:** " . (!empty($data['has_console_logs']) ? 'Yes' : 'No') . "\n\n";

        $body .= "---\n*Reference: {$reference}*\n";

        return $body;
    }

    private function getMockTicketResponse(array $data, string $reference): array
    {
        return [
            'success' => true,
            'isMockData' => true,
            'ticket_id' => 'mock_' . Str::random(8),
            'reference' => $reference,
        ];
    }
}
