<?php

namespace App\Services\Campaign;

/**
 * Result DTO for RecipientResolverService::resolve().
 *
 * Contains counts and breakdowns of recipient resolution for a campaign.
 */
class ResolverResult
{
    public function __construct(
        /** Total contacts found across all sources before dedup */
        public readonly int $totalResolved,
        /** Unique phone numbers after deduplication */
        public readonly int $totalUnique,
        /** Recipients removed due to opt-out list membership */
        public readonly int $totalOptedOut,
        /** Recipients removed due to invalid phone numbers */
        public readonly int $totalInvalid,
        /** Campaign recipient records successfully created */
        public readonly int $totalCreated,
        /** Per-source breakdown: ['list' => 500, 'tag' => 200, 'manual' => 5, ...] */
        public readonly array $sourceBreakdown,
        /** Per-country breakdown: ['GB' => 300, 'US' => 100, 'DE' => 50, ...] */
        public readonly array $countryBreakdown,
        /** Invalid number details: [['number' => '...', 'error' => '...'], ...] (capped at 100) */
        public readonly array $invalidDetails,
    ) {}

    public function toArray(): array
    {
        return [
            'total_resolved' => $this->totalResolved,
            'total_unique' => $this->totalUnique,
            'total_opted_out' => $this->totalOptedOut,
            'total_invalid' => $this->totalInvalid,
            'total_created' => $this->totalCreated,
            'source_breakdown' => $this->sourceBreakdown,
            'country_breakdown' => $this->countryBreakdown,
            'invalid_details' => $this->invalidDetails,
        ];
    }
}
