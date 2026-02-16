<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Security audit remediation migration.
 *
 * Fixes:
 * - M6: Add RLS policy on quarantine_recipients (tenant isolation gap)
 * - M7: Add account_id to dedup_lookup index (cross-tenant false positives)
 * - M13: Add CHECK constraints on enum-like columns across all enforcement tables
 * - L8: Add use_normalisation column to url_rules (consistency with senderid/content)
 * - L9: Add category column to content_rules (consistency with senderid_rules)
 * - L10: Remove redundant standalone engine index on enforcement_exemptions
 */
return new class extends Migration
{
    public function up(): void
    {
        // ─── M7 FIX: Account-scoped dedup index ─────────────────────
        // The original dedup_lookup index was (content_hash, recipient_hash, expires_at)
        // which could match across tenants. Add account_id as leading column.
        Schema::table('message_dedup_log', function (Blueprint $table) {
            $table->dropIndex('dedup_lookup');
            $table->index(
                ['account_id', 'content_hash', 'recipient_hash', 'expires_at'],
                'dedup_lookup_account_scoped'
            );
        });

        // ─── L10 FIX: Remove redundant standalone engine index ──────
        // The composite indexes already have 'engine' as a leading column
        Schema::table('enforcement_exemptions', function (Blueprint $table) {
            $table->dropIndex('enforcement_exemptions_engine_index');
        });

        // ─── L8 FIX: Add use_normalisation to url_rules for consistency ──
        Schema::table('url_rules', function (Blueprint $table) {
            $table->boolean('use_normalisation')->default(false)
                ->after('action')
                ->comment('Whether to apply normalisation library before matching');
        });

        // ─── L9 FIX: Add category to content_rules for consistency ──
        Schema::table('content_rules', function (Blueprint $table) {
            $table->string('category', 50)->nullable()
                ->after('action')
                ->comment('Rule category e.g. fraud, adult, gambling');
        });

        // ─── M13 FIX: CHECK constraints on enum-like columns ────────
        // These prevent invalid values from being stored regardless of
        // whether the write comes through Eloquent or raw SQL.

        // senderid_rules
        DB::statement("ALTER TABLE senderid_rules ADD CONSTRAINT chk_senderid_rules_match_type CHECK (match_type IN ('exact', 'contains', 'regex', 'startswith', 'endswith'))");
        DB::statement("ALTER TABLE senderid_rules ADD CONSTRAINT chk_senderid_rules_action CHECK (action IN ('block', 'quarantine'))");

        // content_rules
        DB::statement("ALTER TABLE content_rules ADD CONSTRAINT chk_content_rules_match_type CHECK (match_type IN ('exact', 'contains', 'regex', 'startswith', 'endswith'))");
        DB::statement("ALTER TABLE content_rules ADD CONSTRAINT chk_content_rules_action CHECK (action IN ('block', 'quarantine'))");

        // url_rules
        DB::statement("ALTER TABLE url_rules ADD CONSTRAINT chk_url_rules_match_type CHECK (match_type IN ('exact_domain', 'wildcard', 'regex', 'exact', 'contains'))");
        DB::statement("ALTER TABLE url_rules ADD CONSTRAINT chk_url_rules_action CHECK (action IN ('block', 'quarantine'))");

        // enforcement_exemptions
        DB::statement("ALTER TABLE enforcement_exemptions ADD CONSTRAINT chk_exemptions_engine CHECK (engine IN ('senderid', 'content', 'url'))");
        DB::statement("ALTER TABLE enforcement_exemptions ADD CONSTRAINT chk_exemptions_type CHECK (exemption_type IN ('rule', 'engine', 'value'))");
        DB::statement("ALTER TABLE enforcement_exemptions ADD CONSTRAINT chk_exemptions_scope CHECK (scope_type IN ('global', 'account', 'sub_account'))");

        // quarantine_messages
        DB::statement("ALTER TABLE quarantine_messages ADD CONSTRAINT chk_quarantine_status CHECK (status IN ('pending', 'released', 'blocked', 'expired'))");
        DB::statement("ALTER TABLE quarantine_messages ADD CONSTRAINT chk_quarantine_engine CHECK (primary_engine IN ('senderid', 'content', 'url', 'domain_age', 'antispam'))");
        DB::statement("ALTER TABLE quarantine_messages ADD CONSTRAINT chk_quarantine_source CHECK (source IN ('single_send', 'campaigns', 'inbox', 'api', 'email_to_sms', 'templates', 'rcs'))");

        // normalisation_characters
        DB::statement("ALTER TABLE normalisation_characters ADD CONSTRAINT chk_norm_char_type CHECK (character_type IN ('letter', 'digit'))");

        // domain_age_cache
        DB::statement("ALTER TABLE domain_age_cache ADD CONSTRAINT chk_domain_lookup_status CHECK (lookup_status IN ('success', 'failed', 'timeout', 'unknown'))");

        // ─── M6 FIX: RLS on quarantine_recipients ────────────────────
        // Inherits tenant isolation from parent quarantine_messages table
        // via JOIN-based policy. Only accessible for rows where the parent
        // message belongs to the current tenant.
        if (config('database.default') === 'pgsql') {
            DB::statement('ALTER TABLE quarantine_recipients ENABLE ROW LEVEL SECURITY');

            DB::statement("
                CREATE POLICY tenant_isolation_recipients ON quarantine_recipients
                FOR ALL
                USING (
                    quarantine_message_id IN (
                        SELECT id FROM quarantine_messages
                        WHERE account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
                    )
                )
            ");
        }
    }

    public function down(): void
    {
        // Remove RLS
        if (config('database.default') === 'pgsql') {
            DB::statement('DROP POLICY IF EXISTS tenant_isolation_recipients ON quarantine_recipients');
            DB::statement('ALTER TABLE quarantine_recipients DISABLE ROW LEVEL SECURITY');
        }

        // Remove CHECK constraints
        $constraints = [
            'senderid_rules' => ['chk_senderid_rules_match_type', 'chk_senderid_rules_action'],
            'content_rules' => ['chk_content_rules_match_type', 'chk_content_rules_action'],
            'url_rules' => ['chk_url_rules_match_type', 'chk_url_rules_action'],
            'enforcement_exemptions' => ['chk_exemptions_engine', 'chk_exemptions_type', 'chk_exemptions_scope'],
            'quarantine_messages' => ['chk_quarantine_status', 'chk_quarantine_engine', 'chk_quarantine_source'],
            'normalisation_characters' => ['chk_norm_char_type'],
            'domain_age_cache' => ['chk_domain_lookup_status'],
        ];

        foreach ($constraints as $table => $names) {
            foreach ($names as $name) {
                DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$name}");
            }
        }

        // Remove added columns
        Schema::table('url_rules', function (Blueprint $table) {
            $table->dropColumn('use_normalisation');
        });
        Schema::table('content_rules', function (Blueprint $table) {
            $table->dropColumn('category');
        });

        // Restore original indexes
        Schema::table('enforcement_exemptions', function (Blueprint $table) {
            $table->index('engine');
        });

        Schema::table('message_dedup_log', function (Blueprint $table) {
            $table->dropIndex('dedup_lookup_account_scoped');
            $table->index(['content_hash', 'recipient_hash', 'expires_at'], 'dedup_lookup');
        });
    }
};
