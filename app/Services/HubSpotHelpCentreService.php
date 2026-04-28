<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Read-only HubSpot integration for the Help Centre dashboard section.
 *
 * Distinct from {@see HubSpotTicketService}, which is bug-report focused.
 * This service:
 *   - Lists open tickets for the logged-in customer (counts only).
 *   - Searches the HubSpot Knowledge Base for help articles.
 *
 * Both methods degrade gracefully when HUBSPOT_ACCESS_TOKEN is missing —
 * they return realistic mock data so the dashboard always renders.
 */
class HubSpotHelpCentreService
{
    private const TICKET_SEARCH_URL  = 'https://api.hubapi.com/crm/v3/objects/tickets/search';
    private const CONTACT_SEARCH_URL = 'https://api.hubapi.com/crm/v3/objects/contacts/search';
    private const KB_SEARCH_URL      = 'https://api.hubapi.com/cms/v3/site-search/search';

    private const TICKETS_CACHE_TTL = 60;   // seconds
    private const KB_CACHE_TTL      = 300;  // seconds

    /**
     * HubSpot ticket pipeline status -> internal bucket.
     * The defaults match the standard HubSpot Service Hub pipeline.
     * Anything not mapped counts as "in_progress" (still open, not closed).
     */
    private const STATUS_BUCKETS = [
        '1' => 'awaiting_reply', // New
        '2' => 'in_progress',    // Waiting on us
        '3' => 'in_progress',    // Waiting on contact
        '4' => 'resolved',       // Closed
    ];

    private ?string $accessToken;
    private int $timeout;

    public function __construct()
    {
        $this->accessToken = config('services.hubspot.access_token');
        $this->timeout = (int) config('services.bug_report.http_timeout', 10);
    }

    public function isConfigured(): bool
    {
        return !empty($this->accessToken);
    }

    private function client(): PendingRequest
    {
        return Http::timeout($this->timeout)->withHeaders([
            'Authorization' => 'Bearer ' . $this->accessToken,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ]);
    }

    /**
     * Return open ticket counts for the contact identified by $email.
     *
     * Shape:
     *   [
     *     'configured'      => bool,   // true when HUBSPOT_ACCESS_TOKEN is set
     *     'live'            => bool,   // false when fallback / mock data was used
     *     'total'           => int,
     *     'awaiting_reply'  => int,
     *     'in_progress'     => int,
     *     'resolved'        => int,
     *     'fetched_at'      => ISO8601 string,
     *   ]
     */
    public function listOpenTicketsForEmail(?string $email, ?string $userKey = null): array
    {
        if (empty($email)) {
            return $this->emptyTicketCounts(true);
        }

        if (!$this->isConfigured()) {
            return $this->mockTicketCounts();
        }

        $cacheKey = 'help_centre.tickets.' . md5(strtolower($email) . '|' . ($userKey ?? ''));

        return Cache::remember($cacheKey, self::TICKETS_CACHE_TTL, function () use ($email) {
            try {
                $contactId = $this->findContactIdByEmail($email);
                if (!$contactId) {
                    return $this->emptyTicketCounts(true);
                }

                // Pull all recent tickets (open and closed) so the "Resolved"
                // chip on the dashboard reflects real activity. We bucket
                // them in PHP and the headline "active tickets" total only
                // counts non-resolved buckets.
                $response = $this->client()->post(self::TICKET_SEARCH_URL, [
                    'filterGroups' => [[
                        'filters' => [
                            [
                                'propertyName' => 'associations.contact',
                                'operator'     => 'EQ',
                                'value'        => $contactId,
                            ],
                        ],
                    ]],
                    'properties' => ['hs_pipeline_stage', 'subject'],
                    'limit'      => 100,
                    'sorts'      => [['propertyName' => 'createdate', 'direction' => 'DESCENDING']],
                ]);

                if (!$response->successful()) {
                    Log::warning('HelpCentre: HubSpot ticket search failed', [
                        'status' => $response->status(),
                        'body'   => mb_substr($response->body(), 0, 500),
                    ]);
                    return $this->failureTicketCounts();
                }

                $results = $response->json('results', []);
                return $this->bucketTicketResults($results);
            } catch (\Throwable $e) {
                Log::warning('HelpCentre: HubSpot ticket lookup threw', [
                    'error' => $e->getMessage(),
                ]);
                return $this->failureTicketCounts();
            }
        });
    }

    /**
     * Search the HubSpot Knowledge Base.
     *
     * Returns a list of articles: [['title','snippet','url'], ...].
     * Falls back to deterministic suggested links when the token is missing
     * or the call fails so the UI never goes blank.
     */
    public function searchKnowledgeBase(string $query, int $limit = 5): array
    {
        $query = trim($query);
        if ($query === '') {
            return [
                'live'    => false,
                'results' => [],
            ];
        }

        $cacheKey = 'help_centre.kb.' . md5(strtolower($query) . '|' . $limit);

        return Cache::remember($cacheKey, self::KB_CACHE_TTL, function () use ($query, $limit) {
            if (!$this->isConfigured()) {
                return [
                    'live'    => false,
                    'results' => $this->mockKbResults($query, $limit),
                ];
            }

            try {
                $response = $this->client()->get(self::KB_SEARCH_URL, [
                    'term'  => $query,
                    'type'  => 'KNOWLEDGE_ARTICLE',
                    'limit' => $limit,
                ]);

                if (!$response->successful()) {
                    Log::warning('HelpCentre: HubSpot KB search failed', [
                        'status' => $response->status(),
                        'body'   => mb_substr($response->body(), 0, 500),
                    ]);
                    return [
                        'live'    => false,
                        'results' => $this->mockKbResults($query, $limit),
                    ];
                }

                $results = collect($response->json('results', []))
                    ->take($limit)
                    ->map(function ($r) {
                        return [
                            'title'   => $r['title'] ?? ($r['name'] ?? 'Untitled'),
                            'snippet' => mb_substr(strip_tags((string) ($r['description'] ?? $r['featuredImageAltText'] ?? '')), 0, 160),
                            'url'     => $r['url'] ?? ($r['absoluteUrl'] ?? '#'),
                        ];
                    })
                    ->values()
                    ->all();

                return [
                    'live'    => true,
                    'results' => $results,
                ];
            } catch (\Throwable $e) {
                Log::warning('HelpCentre: HubSpot KB search threw', [
                    'error' => $e->getMessage(),
                ]);
                return [
                    'live'    => false,
                    'results' => $this->mockKbResults($query, $limit),
                ];
            }
        });
    }

    private function findContactIdByEmail(string $email): ?string
    {
        try {
            $response = $this->client()->post(self::CONTACT_SEARCH_URL, [
                'filterGroups' => [[
                    'filters' => [[
                        'propertyName' => 'email',
                        'operator'     => 'EQ',
                        'value'        => $email,
                    ]],
                ]],
                'properties' => ['email'],
                'limit'      => 1,
            ]);

            if (!$response->successful()) {
                return null;
            }

            return $response->json('results.0.id');
        } catch (\Throwable $e) {
            Log::warning('HelpCentre: HubSpot contact lookup threw', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function bucketTicketResults(array $results): array
    {
        $counts = [
            'awaiting_reply' => 0,
            'in_progress'    => 0,
            'resolved'       => 0,
        ];

        foreach ($results as $row) {
            $stage = (string) ($row['properties']['hs_pipeline_stage'] ?? '');
            $bucket = self::STATUS_BUCKETS[$stage] ?? 'in_progress';
            $counts[$bucket]++;
        }

        return [
            'configured'     => true,
            'live'           => true,
            // Headline "active tickets" excludes resolved.
            'total'          => $counts['awaiting_reply'] + $counts['in_progress'],
            'awaiting_reply' => $counts['awaiting_reply'],
            'in_progress'    => $counts['in_progress'],
            'resolved'       => $counts['resolved'],
            'fetched_at'     => now()->toIso8601String(),
        ];
    }

    private function emptyTicketCounts(bool $configured): array
    {
        return [
            'configured'     => $configured,
            'live'           => true,
            'total'          => 0,
            'awaiting_reply' => 0,
            'in_progress'    => 0,
            'resolved'       => 0,
            'fetched_at'     => now()->toIso8601String(),
        ];
    }

    private function failureTicketCounts(): array
    {
        return [
            'configured'     => true,
            'live'           => false,
            'total'          => 0,
            'awaiting_reply' => 0,
            'in_progress'    => 0,
            'resolved'       => 0,
            'fetched_at'     => now()->toIso8601String(),
        ];
    }

    private function mockTicketCounts(): array
    {
        return [
            'configured'     => false,
            'live'           => false,
            'total'          => 3, // active = awaiting + in progress
            'awaiting_reply' => 1,
            'in_progress'    => 2,
            'resolved'       => 5,
            'fetched_at'     => now()->toIso8601String(),
        ];
    }

    /**
     * Deterministic stand-in results for KB search when HubSpot is unavailable.
     * Anchored on a small library of real-feeling QuickSMS topics so users still
     * get something useful to click while the integration is down.
     */
    private function mockKbResults(string $query, int $limit): array
    {
        $library = [
            ['title' => 'Getting started with QuickSMS',                  'snippet' => 'A quick walkthrough of your account setup, sender IDs and your first campaign.',         'url' => '/support/knowledge-base'],
            ['title' => 'Understanding test mode and how to activate',    'snippet' => 'What test mode allows, the limits applied, and how to move to a fully active account.', 'url' => '/support/knowledge-base/test-mode'],
            ['title' => 'API integration: authentication and webhooks',   'snippet' => 'How to authenticate with the QuickSMS API, send messages and handle delivery receipts.', 'url' => '/support/knowledge-base'],
            ['title' => 'RCS Business Messaging guides',                  'snippet' => 'Build agents, design rich cards and carousels, and submit your first RCS campaign.',     'url' => '/support/knowledge-base'],
            ['title' => 'Billing, payments and invoices',                 'snippet' => 'Top-up your balance, set up auto top-up, find your invoices and manage VAT settings.',  'url' => '/support/knowledge-base'],
            ['title' => 'Account settings and team management',           'snippet' => 'Invite users, configure roles and permissions, and manage your sub-accounts.',          'url' => '/support/knowledge-base'],
            ['title' => 'Number management: VMNs and shortcodes',         'snippet' => 'How to purchase virtual mobile numbers and configure inbound message routing.',         'url' => '/support/knowledge-base'],
            ['title' => 'Two-way messaging with the Inbox',               'snippet' => 'Reply to inbound SMS and RCS messages from a single, shared inbox.',                  'url' => '/support/knowledge-base'],
        ];

        // Rough ranking: prefer items containing the query word(s).
        $needle = mb_strtolower($query);
        usort($library, function ($a, $b) use ($needle) {
            $aMatch = mb_strpos(mb_strtolower($a['title'] . ' ' . $a['snippet']), $needle) !== false ? 0 : 1;
            $bMatch = mb_strpos(mb_strtolower($b['title'] . ' ' . $b['snippet']), $needle) !== false ? 0 : 1;
            return $aMatch <=> $bMatch;
        });

        return array_slice($library, 0, $limit);
    }
}
