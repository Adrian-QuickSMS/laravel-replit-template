<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Add security settings columns to account_settings table.
 *
 * Covers: Message Data Retention, Data Masking, Anti-Flood Protection,
 * Out-of-Hours Restriction, IP Allowlist toggle, Owner masking bypass.
 *
 * DATA CLASSIFICATION: Internal - Account Security Configuration
 * SIDE: GREEN (customer-configurable)
 * TENANT ISOLATION: Inherits from account_settings (RLS on account_id)
 */
return new class extends Migration
{
    public function up(): void
    {
        // Message Data Retention
        if (!Schema::hasColumn('account_settings', 'message_retention_days')) {
            Schema::table('account_settings', function (Blueprint $table) {
                $table->integer('message_retention_days')->default(180)
                    ->comment('Message log retention: 30-180 days. Default 180 (6 months)');
            });
        }

        // Data Visibility & Masking (JSONB)
        if (!Schema::hasColumn('account_settings', 'data_masking_config')) {
            DB::statement("ALTER TABLE account_settings ADD COLUMN data_masking_config JSONB DEFAULT '{\"mask_mobile\": false, \"mask_content\": false, \"mask_sent_time\": false, \"mask_delivered_time\": false}'::jsonb");
            DB::statement("COMMENT ON COLUMN account_settings.data_masking_config IS 'Per-field masking toggles for message logs and exports'");
        }

        // Owner masking bypass toggle
        if (!Schema::hasColumn('account_settings', 'owner_bypass_masking')) {
            Schema::table('account_settings', function (Blueprint $table) {
                $table->boolean('owner_bypass_masking')->default(true)
                    ->comment('If true, owner/admin sees unmasked data regardless of masking config');
            });
        }

        // Anti-Flood Protection
        if (!Schema::hasColumn('account_settings', 'anti_flood_enabled')) {
            Schema::table('account_settings', function (Blueprint $table) {
                $table->boolean('anti_flood_enabled')->default(false)
                    ->comment('Enable duplicate message flood protection');
            });
        }

        if (!Schema::hasColumn('account_settings', 'anti_flood_window_hours')) {
            Schema::table('account_settings', function (Blueprint $table) {
                $table->integer('anti_flood_window_hours')->default(2)
                    ->comment('Anti-flood window in hours (2-48)');
            });
        }

        if (!Schema::hasColumn('account_settings', 'anti_flood_mode')) {
            DB::statement("DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'anti_flood_mode_enum') THEN CREATE TYPE anti_flood_mode_enum AS ENUM ('enforce', 'monitor', 'off'); END IF; END $$");
            DB::statement("ALTER TABLE account_settings ADD COLUMN anti_flood_mode anti_flood_mode_enum DEFAULT 'off'");
            DB::statement("COMMENT ON COLUMN account_settings.anti_flood_mode IS 'enforce=block duplicates, monitor=log only, off=disabled'");
        }

        // Out-of-Hours Sending Restriction
        if (!Schema::hasColumn('account_settings', 'out_of_hours_enabled')) {
            Schema::table('account_settings', function (Blueprint $table) {
                $table->boolean('out_of_hours_enabled')->default(false)
                    ->comment('Enable out-of-hours sending restriction');
            });
        }

        if (!Schema::hasColumn('account_settings', 'out_of_hours_start')) {
            Schema::table('account_settings', function (Blueprint $table) {
                $table->time('out_of_hours_start')->default('21:00')
                    ->comment('Start of restricted window (default 21:00)');
            });
        }

        if (!Schema::hasColumn('account_settings', 'out_of_hours_end')) {
            Schema::table('account_settings', function (Blueprint $table) {
                $table->time('out_of_hours_end')->default('08:00')
                    ->comment('End of restricted window (default 08:00)');
            });
        }

        if (!Schema::hasColumn('account_settings', 'out_of_hours_action')) {
            DB::statement("DO $$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'out_of_hours_action_enum') THEN CREATE TYPE out_of_hours_action_enum AS ENUM ('reject', 'hold'); END IF; END $$");
            DB::statement("ALTER TABLE account_settings ADD COLUMN out_of_hours_action out_of_hours_action_enum DEFAULT 'reject'");
            DB::statement("COMMENT ON COLUMN account_settings.out_of_hours_action IS 'reject=return error, hold=queue until window opens'");
        }

        // IP Allowlist toggle
        if (!Schema::hasColumn('account_settings', 'ip_allowlist_enabled')) {
            Schema::table('account_settings', function (Blueprint $table) {
                $table->boolean('ip_allowlist_enabled')->default(false)
                    ->comment('Enable login IP allowlist for this account');
            });
        }
    }

    public function down(): void
    {
        Schema::table('account_settings', function (Blueprint $table) {
            $columns = [
                'message_retention_days',
                'owner_bypass_masking',
                'anti_flood_enabled',
                'anti_flood_window_hours',
                'out_of_hours_enabled',
                'out_of_hours_start',
                'out_of_hours_end',
                'ip_allowlist_enabled',
            ];
            foreach ($columns as $col) {
                if (Schema::hasColumn('account_settings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        // Drop columns added via raw SQL
        DB::statement("ALTER TABLE account_settings DROP COLUMN IF EXISTS data_masking_config");
        DB::statement("ALTER TABLE account_settings DROP COLUMN IF EXISTS anti_flood_mode");
        DB::statement("ALTER TABLE account_settings DROP COLUMN IF EXISTS out_of_hours_action");
        DB::statement("DROP TYPE IF EXISTS anti_flood_mode_enum");
        DB::statement("DROP TYPE IF EXISTS out_of_hours_action_enum");
    }
};
