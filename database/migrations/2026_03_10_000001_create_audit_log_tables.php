<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Creates the 5 new audit log tables specified in AUDIT_LOG_SPEC.md Section 5:
 *
 * 1. campaign_audit_log    (Tenant, RLS, Critical)
 * 2. user_audit_log        (Tenant, RLS, Critical) — also holds sub-account events via module column
 * 3. account_audit_log     (Tenant, RLS, High)
 * 4. number_audit_log      (Tenant, RLS, Medium)
 * 5. admin_audit_log       (RED zone, no RLS, Medium)
 *
 * Also converts contact_timeline_events.timeline_event_type from ENUM to VARCHAR(50)
 * per GAP 4 recommendation.
 *
 * All tenant-scoped tables enforce:
 * - Dual-layer immutability (Eloquent hooks + DB trigger + REVOKE)
 * - RLS via app.current_tenant_id
 * - UUID primary keys via gen_random_uuid()
 * - Standard schema from AUDIT_LOG_SPEC.md Section 1
 */
return new class extends Migration
{
    public function up(): void
    {
        // =====================================================
        // Shared immutability trigger function (reused by all audit tables)
        // =====================================================
        DB::unprepared("
            CREATE OR REPLACE FUNCTION prevent_audit_mutation()
            RETURNS TRIGGER AS \$\$
            BEGIN
                RAISE EXCEPTION 'Audit log entries are immutable — updates and deletes are prohibited';
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        // =====================================================
        // 1. campaign_audit_log — CRITICAL
        // =====================================================
        DB::unprepared("
            CREATE TABLE IF NOT EXISTS campaign_audit_log (
                id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
                account_id      UUID NOT NULL,
                campaign_id     UUID NOT NULL,
                action          VARCHAR(50) NOT NULL,
                user_id         UUID,
                user_name       VARCHAR(255),
                details         TEXT,
                metadata        JSONB DEFAULT '{}',
                ip_address      INET,
                user_agent      TEXT,
                created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW()
            );

            CREATE INDEX IF NOT EXISTS idx_campaign_audit_campaign ON campaign_audit_log(campaign_id);
            CREATE INDEX IF NOT EXISTS idx_campaign_audit_account ON campaign_audit_log(account_id, created_at DESC);
            CREATE INDEX IF NOT EXISTS idx_campaign_audit_action ON campaign_audit_log(action);
            CREATE INDEX IF NOT EXISTS idx_campaign_audit_created ON campaign_audit_log(created_at DESC);

            ALTER TABLE campaign_audit_log ENABLE ROW LEVEL SECURITY;

            DO \$guard\$
            BEGIN
                IF NOT EXISTS (SELECT 1 FROM pg_policies WHERE tablename = 'campaign_audit_log' AND policyname = 'campaign_audit_tenant_isolation') THEN
                    CREATE POLICY campaign_audit_tenant_isolation ON campaign_audit_log
                        FOR SELECT
                        USING (account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::UUID);
                END IF;
                IF NOT EXISTS (SELECT 1 FROM pg_policies WHERE tablename = 'campaign_audit_log' AND policyname = 'campaign_audit_insert') THEN
                    CREATE POLICY campaign_audit_insert ON campaign_audit_log
                        FOR INSERT
                        WITH CHECK (account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::UUID);
                END IF;
            END \$guard\$;

            DROP TRIGGER IF EXISTS trg_campaign_audit_immutable ON campaign_audit_log;
            CREATE TRIGGER trg_campaign_audit_immutable
                BEFORE UPDATE OR DELETE ON campaign_audit_log
                FOR EACH ROW EXECUTE FUNCTION prevent_audit_mutation();
        ");

        // Revoke mutation grants (safe even if roles don't exist yet)
        DB::unprepared("
            DO \$\$
            BEGIN
                EXECUTE 'REVOKE UPDATE, DELETE ON campaign_audit_log FROM portal_rw, portal_ro';
            EXCEPTION WHEN undefined_object THEN NULL;
            END \$\$;
        ");

        // =====================================================
        // 2. user_audit_log — CRITICAL (also handles sub-account events)
        // =====================================================
        DB::unprepared("
            CREATE TABLE IF NOT EXISTS user_audit_log (
                id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
                account_id      UUID NOT NULL,
                target_user_id  UUID,
                module          VARCHAR(30) NOT NULL DEFAULT 'user_management',
                action          VARCHAR(50) NOT NULL,
                user_id         UUID,
                user_name       VARCHAR(255),
                details         TEXT,
                metadata        JSONB DEFAULT '{}',
                ip_address      INET,
                user_agent      TEXT,
                created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW()
            );

            CREATE INDEX IF NOT EXISTS idx_user_audit_account ON user_audit_log(account_id, created_at DESC);
            CREATE INDEX IF NOT EXISTS idx_user_audit_target ON user_audit_log(target_user_id);
            CREATE INDEX IF NOT EXISTS idx_user_audit_action ON user_audit_log(action);
            CREATE INDEX IF NOT EXISTS idx_user_audit_module ON user_audit_log(module);
            CREATE INDEX IF NOT EXISTS idx_user_audit_created ON user_audit_log(created_at DESC);

            ALTER TABLE user_audit_log ENABLE ROW LEVEL SECURITY;

            DO \$guard\$
            BEGIN
                IF NOT EXISTS (SELECT 1 FROM pg_policies WHERE tablename = 'user_audit_log' AND policyname = 'user_audit_tenant_isolation') THEN
                    CREATE POLICY user_audit_tenant_isolation ON user_audit_log
                        FOR SELECT
                        USING (account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::UUID);
                END IF;
                IF NOT EXISTS (SELECT 1 FROM pg_policies WHERE tablename = 'user_audit_log' AND policyname = 'user_audit_insert') THEN
                    CREATE POLICY user_audit_insert ON user_audit_log
                        FOR INSERT
                        WITH CHECK (account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::UUID);
                END IF;
            END \$guard\$;

            DROP TRIGGER IF EXISTS trg_user_audit_immutable ON user_audit_log;
            CREATE TRIGGER trg_user_audit_immutable
                BEFORE UPDATE OR DELETE ON user_audit_log
                FOR EACH ROW EXECUTE FUNCTION prevent_audit_mutation();
        ");

        DB::unprepared("
            DO \$\$
            BEGIN
                EXECUTE 'REVOKE UPDATE, DELETE ON user_audit_log FROM portal_rw, portal_ro';
            EXCEPTION WHEN undefined_object THEN NULL;
            END \$\$;
        ");

        // =====================================================
        // 3. account_audit_log — HIGH
        // =====================================================
        DB::unprepared("
            CREATE TABLE IF NOT EXISTS account_audit_log (
                id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
                account_id      UUID NOT NULL,
                action          VARCHAR(50) NOT NULL,
                user_id         UUID,
                user_name       VARCHAR(255),
                details         TEXT,
                metadata        JSONB DEFAULT '{}',
                ip_address      INET,
                user_agent      TEXT,
                created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW()
            );

            CREATE INDEX IF NOT EXISTS idx_account_audit_account ON account_audit_log(account_id, created_at DESC);
            CREATE INDEX IF NOT EXISTS idx_account_audit_action ON account_audit_log(action);
            CREATE INDEX IF NOT EXISTS idx_account_audit_created ON account_audit_log(created_at DESC);

            ALTER TABLE account_audit_log ENABLE ROW LEVEL SECURITY;

            DO \$guard\$
            BEGIN
                IF NOT EXISTS (SELECT 1 FROM pg_policies WHERE tablename = 'account_audit_log' AND policyname = 'account_audit_tenant_isolation') THEN
                    CREATE POLICY account_audit_tenant_isolation ON account_audit_log
                        FOR SELECT
                        USING (account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::UUID);
                END IF;
                IF NOT EXISTS (SELECT 1 FROM pg_policies WHERE tablename = 'account_audit_log' AND policyname = 'account_audit_insert') THEN
                    CREATE POLICY account_audit_insert ON account_audit_log
                        FOR INSERT
                        WITH CHECK (account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::UUID);
                END IF;
            END \$guard\$;

            DROP TRIGGER IF EXISTS trg_account_audit_immutable ON account_audit_log;
            CREATE TRIGGER trg_account_audit_immutable
                BEFORE UPDATE OR DELETE ON account_audit_log
                FOR EACH ROW EXECUTE FUNCTION prevent_audit_mutation();
        ");

        DB::unprepared("
            DO \$\$
            BEGIN
                EXECUTE 'REVOKE UPDATE, DELETE ON account_audit_log FROM portal_rw, portal_ro';
            EXCEPTION WHEN undefined_object THEN NULL;
            END \$\$;
        ");

        // =====================================================
        // 4. number_audit_log — MEDIUM
        // =====================================================
        DB::unprepared("
            CREATE TABLE IF NOT EXISTS number_audit_log (
                id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
                account_id      UUID NOT NULL,
                number_id       UUID,
                action          VARCHAR(50) NOT NULL,
                user_id         UUID,
                user_name       VARCHAR(255),
                details         TEXT,
                metadata        JSONB DEFAULT '{}',
                ip_address      INET,
                user_agent      TEXT,
                created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW()
            );

            CREATE INDEX IF NOT EXISTS idx_number_audit_account ON number_audit_log(account_id, created_at DESC);
            CREATE INDEX IF NOT EXISTS idx_number_audit_number ON number_audit_log(number_id);
            CREATE INDEX IF NOT EXISTS idx_number_audit_action ON number_audit_log(action);
            CREATE INDEX IF NOT EXISTS idx_number_audit_created ON number_audit_log(created_at DESC);

            ALTER TABLE number_audit_log ENABLE ROW LEVEL SECURITY;

            DO \$guard\$
            BEGIN
                IF NOT EXISTS (SELECT 1 FROM pg_policies WHERE tablename = 'number_audit_log' AND policyname = 'number_audit_tenant_isolation') THEN
                    CREATE POLICY number_audit_tenant_isolation ON number_audit_log
                        FOR SELECT
                        USING (account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::UUID);
                END IF;
                IF NOT EXISTS (SELECT 1 FROM pg_policies WHERE tablename = 'number_audit_log' AND policyname = 'number_audit_insert') THEN
                    CREATE POLICY number_audit_insert ON number_audit_log
                        FOR INSERT
                        WITH CHECK (account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::UUID);
                END IF;
            END \$guard\$;

            DROP TRIGGER IF EXISTS trg_number_audit_immutable ON number_audit_log;
            CREATE TRIGGER trg_number_audit_immutable
                BEFORE UPDATE OR DELETE ON number_audit_log
                FOR EACH ROW EXECUTE FUNCTION prevent_audit_mutation();
        ");

        DB::unprepared("
            DO \$\$
            BEGIN
                EXECUTE 'REVOKE UPDATE, DELETE ON number_audit_log FROM portal_rw, portal_ro';
            EXCEPTION WHEN undefined_object THEN NULL;
            END \$\$;
        ");

        // =====================================================
        // 5. admin_audit_log — MEDIUM (RED zone, no RLS)
        // =====================================================
        DB::unprepared("
            CREATE TABLE IF NOT EXISTS admin_audit_log (
                id                UUID PRIMARY KEY DEFAULT gen_random_uuid(),
                admin_user_id     UUID,
                admin_user_name   VARCHAR(255),
                action            VARCHAR(50) NOT NULL,
                category          VARCHAR(50),
                severity          VARCHAR(20) DEFAULT 'medium',
                target_type       VARCHAR(50),
                target_id         UUID,
                target_account_id UUID,
                details           TEXT,
                metadata          JSONB DEFAULT '{}',
                ip_address        INET,
                user_agent        TEXT,
                created_at        TIMESTAMPTZ NOT NULL DEFAULT NOW()
            );

            CREATE INDEX IF NOT EXISTS idx_admin_audit_action ON admin_audit_log(action);
            CREATE INDEX IF NOT EXISTS idx_admin_audit_category ON admin_audit_log(category);
            CREATE INDEX IF NOT EXISTS idx_admin_audit_severity ON admin_audit_log(severity);
            CREATE INDEX IF NOT EXISTS idx_admin_audit_admin ON admin_audit_log(admin_user_id);
            CREATE INDEX IF NOT EXISTS idx_admin_audit_target_account ON admin_audit_log(target_account_id);
            CREATE INDEX IF NOT EXISTS idx_admin_audit_created ON admin_audit_log(created_at DESC);

            DROP TRIGGER IF EXISTS trg_admin_audit_immutable ON admin_audit_log;
            CREATE TRIGGER trg_admin_audit_immutable
                BEFORE UPDATE OR DELETE ON admin_audit_log
                FOR EACH ROW EXECUTE FUNCTION prevent_audit_mutation();
        ");

        // =====================================================
        // 6. Convert contact_timeline_events ENUM columns to VARCHAR
        //    (per GAP 4 recommendation — more flexible, avoids future migrations)
        // =====================================================
        DB::unprepared("
            DO \$\$
            BEGIN
                -- Only alter if the column is currently an enum type
                IF EXISTS (
                    SELECT 1 FROM information_schema.columns
                    WHERE table_name = 'contact_timeline_events'
                    AND column_name = 'event_type'
                    AND udt_name = 'timeline_event_type'
                ) THEN
                    ALTER TABLE contact_timeline_events
                        ALTER COLUMN event_type TYPE VARCHAR(50) USING event_type::VARCHAR(50);
                    DROP TYPE IF EXISTS timeline_event_type;
                END IF;

                IF EXISTS (
                    SELECT 1 FROM information_schema.columns
                    WHERE table_name = 'contact_timeline_events'
                    AND column_name = 'source_module'
                    AND udt_name = 'timeline_source_module'
                ) THEN
                    ALTER TABLE contact_timeline_events
                        ALTER COLUMN source_module TYPE VARCHAR(50) USING source_module::VARCHAR(50);
                    DROP TYPE IF EXISTS timeline_source_module;
                END IF;

                IF EXISTS (
                    SELECT 1 FROM information_schema.columns
                    WHERE table_name = 'contact_timeline_events'
                    AND column_name = 'actor_type'
                    AND udt_name = 'timeline_actor_type'
                ) THEN
                    ALTER TABLE contact_timeline_events
                        ALTER COLUMN actor_type TYPE VARCHAR(20) USING actor_type::VARCHAR(20);
                    DROP TYPE IF EXISTS timeline_actor_type;
                END IF;
            END \$\$;
        ");
    }

    public function down(): void
    {
        // Drop triggers first
        $tables = ['campaign_audit_log', 'user_audit_log', 'account_audit_log', 'number_audit_log', 'admin_audit_log'];
        foreach ($tables as $table) {
            DB::unprepared("DROP TRIGGER IF EXISTS trg_{$table}_immutable ON {$table}");
        }

        // Drop RLS policies
        $tenantTables = ['campaign_audit_log', 'user_audit_log', 'account_audit_log', 'number_audit_log'];
        foreach ($tenantTables as $table) {
            DB::unprepared("DROP POLICY IF EXISTS {$table}_tenant_isolation ON {$table}");
            DB::unprepared("DROP POLICY IF EXISTS {$table}_insert ON {$table}");
            DB::unprepared("ALTER TABLE {$table} DISABLE ROW LEVEL SECURITY");
        }

        // Drop tables
        foreach ($tables as $table) {
            DB::unprepared("DROP TABLE IF EXISTS {$table} CASCADE");
        }

        // Note: We intentionally do NOT revert the VARCHAR conversion back to ENUM
        // as that would be lossy if new event types have been written.

        // Drop shared function only if no other triggers reference it
        DB::unprepared("DROP FUNCTION IF EXISTS prevent_audit_mutation() CASCADE");
    }
};
