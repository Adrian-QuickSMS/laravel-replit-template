<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Add Row Level Security policies to all tenant-scoped alerting tables.
 * Follows the established pattern from notifications and sender_ids migrations.
 */
return new class extends Migration
{
    private const TABLES = [
        'alert_rules',
        'alert_history',
        'alert_channel_configs',
        'alert_preferences',
        'notification_batches',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            $policyName = "{$table}_tenant_isolation";

            // Enable RLS
            DB::statement("ALTER TABLE {$table} ENABLE ROW LEVEL SECURITY");
            DB::statement("ALTER TABLE {$table} FORCE ROW LEVEL SECURITY");

            // Create tenant isolation policy
            DB::statement("
                CREATE POLICY {$policyName} ON {$table}
                FOR ALL
                USING (
                    tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
                    OR tenant_id IS NULL
                )
                WITH CHECK (
                    tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
                    OR tenant_id IS NULL
                )
            ");

            // Grant access to portal roles
            DB::statement("GRANT SELECT, INSERT, UPDATE, DELETE ON {$table} TO portal_rw");
            DB::statement("GRANT SELECT ON {$table} TO portal_ro");
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            $policyName = "{$table}_tenant_isolation";

            DB::statement("DROP POLICY IF EXISTS {$policyName} ON {$table}");
            DB::statement("ALTER TABLE {$table} DISABLE ROW LEVEL SECURITY");
        }
    }
};
