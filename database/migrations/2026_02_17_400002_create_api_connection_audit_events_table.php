<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Audit trail for API Connection lifecycle events.
 *
 * Append-only table — no updates or deletes. Records every state transition,
 * credential regeneration, and configuration change for compliance and debugging.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE TABLE api_connection_audit_events (
                id                  UUID PRIMARY KEY DEFAULT gen_random_uuid(),
                account_id          UUID NOT NULL,
                api_connection_id   UUID NOT NULL REFERENCES api_connections(id) ON DELETE CASCADE,

                event_type          VARCHAR(50) NOT NULL,
                actor_type          VARCHAR(20) NOT NULL,
                actor_id            VARCHAR(255),
                actor_name          VARCHAR(255),
                metadata            JSONB NOT NULL DEFAULT '{}'::jsonb,
                ip_address          VARCHAR(45),

                created_at          TIMESTAMPTZ NOT NULL DEFAULT NOW()
            )
        ");

        // ─── INDEXES ─────────────────────────────────────────────
        DB::statement("CREATE INDEX idx_api_conn_audit_connection_created
            ON api_connection_audit_events (api_connection_id, created_at DESC)");

        DB::statement("CREATE INDEX idx_api_conn_audit_account_created
            ON api_connection_audit_events (account_id, created_at DESC)");

        DB::statement("CREATE INDEX idx_api_conn_audit_event_type
            ON api_connection_audit_events (event_type)");

        // ─── UUID TRIGGER ────────────────────────────────────────
        DB::unprepared("
            CREATE OR REPLACE FUNCTION api_conn_audit_set_uuid()
            RETURNS TRIGGER AS \$\$
            BEGIN
                IF NEW.id IS NULL THEN
                    NEW.id := gen_random_uuid();
                END IF;
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;

            CREATE TRIGGER trg_api_conn_audit_uuid
                BEFORE INSERT ON api_connection_audit_events
                FOR EACH ROW
                EXECUTE FUNCTION api_conn_audit_set_uuid();
        ");

        // ─── RLS ─────────────────────────────────────────────────
        DB::statement("ALTER TABLE api_connection_audit_events ENABLE ROW LEVEL SECURITY");
        DB::statement("ALTER TABLE api_connection_audit_events FORCE ROW LEVEL SECURITY");

        DB::statement("
            CREATE POLICY api_conn_audit_tenant_isolation ON api_connection_audit_events
                USING (account_id::text = current_setting('app.current_tenant_id', true))
        ");
    }

    public function down(): void
    {
        DB::statement("DROP POLICY IF EXISTS api_conn_audit_tenant_isolation ON api_connection_audit_events");
        DB::statement("DROP TRIGGER IF EXISTS trg_api_conn_audit_uuid ON api_connection_audit_events");
        DB::statement("DROP FUNCTION IF EXISTS api_conn_audit_set_uuid()");
        DB::statement("DROP TABLE IF EXISTS api_connection_audit_events");
    }
};
