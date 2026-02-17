<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * API Connections table — stores customer API connection configurations.
 *
 * Each connection represents a set of credentials (API Key or Basic Auth) that
 * external systems use to call the QuickSMS API. Connections are scoped to an
 * account + optional sub-account, with per-connection rate limiting and IP allowlisting.
 *
 * State machine: draft → active → suspended → archived (terminal)
 * Environment: test | live (test can be promoted to live)
 */
return new class extends Migration
{
    public function up(): void
    {
        // ─── ENUMS ───────────────────────────────────────────────
        DB::statement("DO $$ BEGIN
            CREATE TYPE api_connection_type AS ENUM ('bulk', 'campaign', 'integration');
        EXCEPTION WHEN duplicate_object THEN NULL;
        END $$");

        DB::statement("DO $$ BEGIN
            CREATE TYPE api_connection_auth_type AS ENUM ('api_key', 'basic_auth');
        EXCEPTION WHEN duplicate_object THEN NULL;
        END $$");

        DB::statement("DO $$ BEGIN
            CREATE TYPE api_connection_status AS ENUM ('draft', 'active', 'suspended', 'archived');
        EXCEPTION WHEN duplicate_object THEN NULL;
        END $$");

        DB::statement("DO $$ BEGIN
            CREATE TYPE api_connection_environment AS ENUM ('test', 'live');
        EXCEPTION WHEN duplicate_object THEN NULL;
        END $$");

        // ─── TABLE ───────────────────────────────────────────────
        DB::statement("
            CREATE TABLE api_connections (
                id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
                account_id      UUID NOT NULL REFERENCES accounts(id),
                sub_account_id  UUID REFERENCES sub_accounts(id),

                -- Identity
                name            VARCHAR(255) NOT NULL,
                description     TEXT,

                -- Type & Auth
                type            api_connection_type NOT NULL,
                auth_type       api_connection_auth_type NOT NULL,
                environment     api_connection_environment NOT NULL DEFAULT 'test',
                status          api_connection_status NOT NULL DEFAULT 'draft',

                -- API Key auth (hash-based, shown once)
                api_key_hash    VARCHAR(64),
                api_key_prefix  VARCHAR(12),
                api_key_last4   VARCHAR(4),

                -- Basic Auth (hash-based, shown once)
                basic_auth_username     VARCHAR(255),
                basic_auth_password_hash VARCHAR(64),

                -- Security
                ip_allowlist_enabled    BOOLEAN NOT NULL DEFAULT false,
                ip_allowlist            JSONB NOT NULL DEFAULT '[]'::jsonb,

                -- Webhooks
                webhook_dlr_url     VARCHAR(2048),
                webhook_inbound_url VARCHAR(2048),

                -- Integration partner config
                partner_name    VARCHAR(100),
                partner_config  JSONB NOT NULL DEFAULT '{}'::jsonb,

                -- Rate limiting
                rate_limit_per_minute   INTEGER NOT NULL DEFAULT 100,

                -- Auto-set capabilities based on type
                capabilities    JSONB NOT NULL DEFAULT '[]'::jsonb,

                -- Usage tracking
                last_used_at    TIMESTAMPTZ,
                last_used_ip    VARCHAR(45),

                -- Audit
                created_by      VARCHAR(255),
                suspended_at    TIMESTAMPTZ,
                suspended_by    VARCHAR(255),
                suspended_reason TEXT,
                archived_at     TIMESTAMPTZ,
                archived_by     VARCHAR(255),

                created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW(),
                updated_at      TIMESTAMPTZ NOT NULL DEFAULT NOW()
            )
        ");

        // ─── INDEXES ─────────────────────────────────────────────
        DB::statement("CREATE UNIQUE INDEX idx_api_connections_account_name
            ON api_connections (account_id, name)
            WHERE status != 'archived'");

        DB::statement("CREATE INDEX idx_api_connections_account_status
            ON api_connections (account_id, status)");

        DB::statement("CREATE INDEX idx_api_connections_api_key_hash
            ON api_connections (api_key_hash)
            WHERE api_key_hash IS NOT NULL");

        DB::statement("CREATE INDEX idx_api_connections_basic_auth_username
            ON api_connections (basic_auth_username)
            WHERE basic_auth_username IS NOT NULL");

        DB::statement("CREATE INDEX idx_api_connections_account_sub_account
            ON api_connections (account_id, sub_account_id)");

        // ─── UUID TRIGGER ────────────────────────────────────────
        DB::unprepared("
            CREATE OR REPLACE FUNCTION api_connections_set_uuid()
            RETURNS TRIGGER AS \$\$
            BEGIN
                IF NEW.id IS NULL THEN
                    NEW.id := gen_random_uuid();
                END IF;
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;

            CREATE TRIGGER trg_api_connections_uuid
                BEFORE INSERT ON api_connections
                FOR EACH ROW
                EXECUTE FUNCTION api_connections_set_uuid();
        ");

        // ─── ACCOUNT VALIDATION TRIGGER ──────────────────────────
        DB::unprepared("
            CREATE OR REPLACE FUNCTION api_connections_validate_account()
            RETURNS TRIGGER AS \$\$
            BEGIN
                IF NOT EXISTS (SELECT 1 FROM accounts WHERE id = NEW.account_id) THEN
                    RAISE EXCEPTION 'Invalid account_id: %', NEW.account_id;
                END IF;
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;

            CREATE TRIGGER trg_api_connections_validate_account
                BEFORE INSERT ON api_connections
                FOR EACH ROW
                EXECUTE FUNCTION api_connections_validate_account();
        ");

        // ─── RLS ─────────────────────────────────────────────────
        DB::statement("ALTER TABLE api_connections ENABLE ROW LEVEL SECURITY");
        DB::statement("ALTER TABLE api_connections FORCE ROW LEVEL SECURITY");

        DB::statement("
            CREATE POLICY api_connections_tenant_isolation ON api_connections
                USING (account_id::text = current_setting('app.current_tenant_id', true))
        ");

        // ─── AUTO-SET CAPABILITIES TRIGGER ───────────────────────
        DB::unprepared("
            CREATE OR REPLACE FUNCTION api_connections_set_capabilities()
            RETURNS TRIGGER AS \$\$
            BEGIN
                IF NEW.type = 'bulk' THEN
                    NEW.capabilities := '[\"send_sms\", \"send_batch_sms\", \"check_delivery_status\", \"check_balance\"]'::jsonb;
                ELSIF NEW.type = 'campaign' THEN
                    NEW.capabilities := '[\"send_sms\", \"send_batch_sms\", \"check_delivery_status\", \"check_balance\", \"create_campaign\", \"schedule_campaign\", \"manage_templates\", \"audience_targeting\"]'::jsonb;
                ELSIF NEW.type = 'integration' THEN
                    NEW.capabilities := '[\"send_sms\", \"check_delivery_status\", \"partner_webhook\", \"managed_connector\"]'::jsonb;
                END IF;

                -- Set default rate limits by type
                IF NEW.rate_limit_per_minute = 100 THEN
                    IF NEW.type = 'campaign' THEN
                        NEW.rate_limit_per_minute := 30;
                    ELSIF NEW.type = 'integration' THEN
                        NEW.rate_limit_per_minute := 50;
                    END IF;
                END IF;

                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;

            CREATE TRIGGER trg_api_connections_capabilities
                BEFORE INSERT OR UPDATE OF type ON api_connections
                FOR EACH ROW
                EXECUTE FUNCTION api_connections_set_capabilities();
        ");
    }

    public function down(): void
    {
        DB::statement("DROP POLICY IF EXISTS api_connections_tenant_isolation ON api_connections");
        DB::statement("DROP TRIGGER IF EXISTS trg_api_connections_capabilities ON api_connections");
        DB::statement("DROP FUNCTION IF EXISTS api_connections_set_capabilities()");
        DB::statement("DROP TRIGGER IF EXISTS trg_api_connections_validate_account ON api_connections");
        DB::statement("DROP FUNCTION IF EXISTS api_connections_validate_account()");
        DB::statement("DROP TRIGGER IF EXISTS trg_api_connections_uuid ON api_connections");
        DB::statement("DROP FUNCTION IF EXISTS api_connections_set_uuid()");
        DB::statement("DROP TABLE IF EXISTS api_connections");
        DB::statement("DROP TYPE IF EXISTS api_connection_environment");
        DB::statement("DROP TYPE IF EXISTS api_connection_status");
        DB::statement("DROP TYPE IF EXISTS api_connection_auth_type");
        DB::statement("DROP TYPE IF EXISTS api_connection_type");
    }
};
