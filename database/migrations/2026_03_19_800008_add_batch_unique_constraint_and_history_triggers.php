<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Add unique constraint to notification_batches (fixes race condition),
 * immutability triggers to alert_history, and composite index verification.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Unique constraint on notification_batches to prevent duplicate batch creation
        if (Schema::hasTable('notification_batches')) {
            Schema::table('notification_batches', function (Blueprint $table) {
                $table->unique(
                    ['tenant_id', 'user_id', 'batch_type', 'channel', 'scheduled_for'],
                    'notification_batches_unique_pending'
                );
            });
        }

        // 2. Immutability triggers on alert_history (DB-level enforcement)
        if (Schema::hasTable('alert_history')) {
            DB::unprepared("
                CREATE OR REPLACE FUNCTION prevent_alert_history_mutation()
                RETURNS TRIGGER AS \$\$
                BEGIN
                    RAISE EXCEPTION 'alert_history records are immutable — UPDATE and DELETE are prohibited';
                    RETURN NULL;
                END;
                \$\$ LANGUAGE plpgsql;
            ");

            DB::unprepared("
                CREATE TRIGGER alert_history_no_update
                BEFORE UPDATE ON alert_history
                FOR EACH ROW
                EXECUTE FUNCTION prevent_alert_history_mutation();
            ");

            DB::unprepared("
                CREATE TRIGGER alert_history_no_delete
                BEFORE DELETE ON alert_history
                FOR EACH ROW
                EXECUTE FUNCTION prevent_alert_history_mutation();
            ");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('alert_history')) {
            DB::unprepared("DROP TRIGGER IF EXISTS alert_history_no_update ON alert_history");
            DB::unprepared("DROP TRIGGER IF EXISTS alert_history_no_delete ON alert_history");
            DB::unprepared("DROP FUNCTION IF EXISTS prevent_alert_history_mutation()");
        }

        if (Schema::hasTable('notification_batches')) {
            Schema::table('notification_batches', function (Blueprint $table) {
                $table->dropUnique('notification_batches_unique_pending');
            });
        }
    }
};
