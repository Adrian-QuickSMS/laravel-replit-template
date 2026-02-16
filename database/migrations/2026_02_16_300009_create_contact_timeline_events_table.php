<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Contact Book Module — Contact Activity Timeline Events (Partitioned)
 *
 * DATA CLASSIFICATION: Confidential - Audit Trail
 * SIDE: GREEN (customer portal accessible — masked PII)
 * TENANT ISOLATION: account_id scoped via RLS + app.current_tenant_id
 *
 * PURPOSE:
 * - Per-contact audit trail: messages, tag/list changes, opt-outs
 * - Append-only — no updates or deletes (compliance)
 * - msisdn_hash (SHA-256) instead of raw mobile number (GDPR)
 * - Partitioned by month on created_at for performance at scale
 * - metadata JSONB stores event-specific details (campaign IDs, delivery status, etc.)
 *
 * PARTITIONING NOTES:
 * - Parent table partitioned by RANGE on created_at
 * - Monthly partitions auto-created for current year + 1 year ahead
 * - Primary key includes created_at (required for partition key inclusion)
 * - No FK from other tables to this table (partitioned tables can't be FK targets)
 * - contact_id is indexed but not FK-constrained (audit trail survives contact deletion)
 */
return new class extends Migration
{
    public function up(): void
    {
        // Create ENUM types
        DB::statement("CREATE TYPE timeline_event_type AS ENUM (
            'message_sent', 'message_received',
            'tag_added', 'tag_removed',
            'list_added', 'list_removed',
            'optout', 'optin'
        )");

        DB::statement("CREATE TYPE timeline_source_module AS ENUM (
            'campaign', 'inbox', 'api', 'email_to_sms', 'system'
        )");

        DB::statement("CREATE TYPE timeline_actor_type AS ENUM ('user', 'system', 'api')");

        // Create partitioned table using raw SQL (Laravel Schema builder doesn't support PARTITION BY)
        DB::unprepared("
            CREATE TABLE contact_timeline_events (
                event_id        UUID NOT NULL DEFAULT gen_random_uuid(),
                account_id      UUID NOT NULL,
                contact_id      UUID,
                msisdn_hash     VARCHAR(64) NOT NULL,
                event_type      timeline_event_type NOT NULL,
                source_module   timeline_source_module NOT NULL,
                actor_type      timeline_actor_type NOT NULL DEFAULT 'system',
                actor_id        UUID,
                actor_name      VARCHAR(255),
                metadata        JSONB DEFAULT '{}',
                created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW(),

                PRIMARY KEY (event_id, created_at)
            ) PARTITION BY RANGE (created_at)
        ");

        // Add comments
        DB::statement("COMMENT ON TABLE contact_timeline_events IS 'Per-contact audit trail — append-only, partitioned by month'");
        DB::statement("COMMENT ON COLUMN contact_timeline_events.msisdn_hash IS 'SHA-256 hash of E.164 mobile number — no raw PII in timeline'");
        DB::statement("COMMENT ON COLUMN contact_timeline_events.contact_id IS 'Soft reference to contacts.id — not FK constrained (survives deletion)'");
        DB::statement("COMMENT ON COLUMN contact_timeline_events.metadata IS 'Event-specific JSON: campaign IDs, delivery status, message snippets, etc.'");

        // Create monthly partitions: 2026-01 through 2027-12 (2 years)
        $startYear = 2026;
        $endYear = 2027;

        for ($year = $startYear; $year <= $endYear; $year++) {
            for ($month = 1; $month <= 12; $month++) {
                $partName = sprintf('contact_timeline_events_%d_%02d', $year, $month);
                $rangeStart = sprintf('%d-%02d-01', $year, $month);

                // Calculate next month
                $nextMonth = $month + 1;
                $nextYear = $year;
                if ($nextMonth > 12) {
                    $nextMonth = 1;
                    $nextYear = $year + 1;
                }
                $rangeEnd = sprintf('%d-%02d-01', $nextYear, $nextMonth);

                DB::statement("
                    CREATE TABLE {$partName} PARTITION OF contact_timeline_events
                    FOR VALUES FROM ('{$rangeStart}') TO ('{$rangeEnd}')
                ");
            }
        }

        // Indexes (created on parent — automatically apply to all partitions)
        DB::statement("CREATE INDEX idx_timeline_account_contact ON contact_timeline_events (account_id, contact_id, created_at DESC)");
        DB::statement("CREATE INDEX idx_timeline_account_created ON contact_timeline_events (account_id, created_at DESC)");
        DB::statement("CREATE INDEX idx_timeline_contact_type ON contact_timeline_events (contact_id, event_type, created_at DESC)");
        DB::statement("CREATE INDEX idx_timeline_msisdn_hash ON contact_timeline_events (msisdn_hash, created_at DESC)");
        DB::statement("CREATE INDEX idx_timeline_metadata ON contact_timeline_events USING GIN (metadata)");

        // Row Level Security
        DB::unprepared("ALTER TABLE contact_timeline_events ENABLE ROW LEVEL SECURITY");
        DB::unprepared("ALTER TABLE contact_timeline_events FORCE ROW LEVEL SECURITY");

        DB::unprepared("
            CREATE POLICY timeline_tenant_isolation ON contact_timeline_events
            FOR ALL
            USING (
                account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
            )
            WITH CHECK (
                account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
            );
        ");

        DB::unprepared("
            CREATE POLICY timeline_postgres_bypass ON contact_timeline_events
            FOR ALL
            TO postgres
            USING (true)
            WITH CHECK (true);
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP POLICY IF EXISTS timeline_postgres_bypass ON contact_timeline_events");
        DB::unprepared("DROP POLICY IF EXISTS timeline_tenant_isolation ON contact_timeline_events");

        // Drop parent table (cascades to all partitions)
        DB::statement("DROP TABLE IF EXISTS contact_timeline_events CASCADE");

        DB::statement("DROP TYPE IF EXISTS timeline_event_type CASCADE");
        DB::statement("DROP TYPE IF EXISTS timeline_source_module CASCADE");
        DB::statement("DROP TYPE IF EXISTS timeline_actor_type CASCADE");
    }
};
