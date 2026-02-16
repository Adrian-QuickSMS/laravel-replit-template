<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('url_rules', 'use_normalisation')) {
            Schema::table('url_rules', function (Blueprint $table) {
                $table->boolean('use_normalisation')->default(true)->after('action');
            });
        }

        if (!Schema::hasColumn('content_rules', 'category')) {
            Schema::table('content_rules', function (Blueprint $table) {
                $table->string('category', 50)->nullable()->after('action');
            });
        }

        Schema::table('quarantine_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('quarantine_messages', 'sub_account_id')) {
                $table->uuid('sub_account_id')->nullable()->after('account_id');
            }
            if (!Schema::hasColumn('quarantine_messages', 'user_id')) {
                $table->uuid('user_id')->nullable()->after('sub_account_id');
            }
            if (!Schema::hasColumn('quarantine_messages', 'source')) {
                $table->string('source', 30)->default('single_send')->after('user_id');
            }
            if (!Schema::hasColumn('quarantine_messages', 'campaign_id')) {
                $table->uuid('campaign_id')->nullable()->after('source');
            }
            if (!Schema::hasColumn('quarantine_messages', 'message_body_normalised')) {
                $table->text('message_body_normalised')->nullable()->after('message_body');
            }
            if (!Schema::hasColumn('quarantine_messages', 'urls_detected')) {
                $table->jsonb('urls_detected')->nullable()->after('message_body_normalised');
            }
            if (!Schema::hasColumn('quarantine_messages', 'triggered_rules')) {
                $table->jsonb('triggered_rules')->nullable()->after('urls_detected');
            }
            if (!Schema::hasColumn('quarantine_messages', 'recipient_count')) {
                $table->integer('recipient_count')->default(0)->after('primary_engine');
            }
            if (!Schema::hasColumn('quarantine_messages', 'scheduled_send_at')) {
                $table->timestamp('scheduled_send_at')->nullable()->after('recipient_count');
            }
        });

        $checkConstraints = [
            "ALTER TABLE senderid_rules ADD CONSTRAINT chk_senderid_rules_action CHECK (action IN ('block', 'quarantine'))",
            "ALTER TABLE senderid_rules ADD CONSTRAINT chk_senderid_rules_match_type CHECK (match_type IN ('exact', 'contains', 'regex', 'startswith', 'endswith'))",
            "ALTER TABLE content_rules ADD CONSTRAINT chk_content_rules_action CHECK (action IN ('block', 'quarantine'))",
            "ALTER TABLE content_rules ADD CONSTRAINT chk_content_rules_match_type CHECK (match_type IN ('exact', 'contains', 'regex', 'startswith', 'endswith'))",
            "ALTER TABLE url_rules ADD CONSTRAINT chk_url_rules_action CHECK (action IN ('block', 'quarantine'))",
            "ALTER TABLE url_rules ADD CONSTRAINT chk_url_rules_match_type CHECK (match_type IN ('exact_domain', 'wildcard', 'regex', 'exact', 'contains'))",
            "ALTER TABLE enforcement_exemptions ADD CONSTRAINT chk_enforcement_exemptions_engine CHECK (engine IN ('senderid', 'content', 'url'))",
            "ALTER TABLE enforcement_exemptions ADD CONSTRAINT chk_enforcement_exemptions_type CHECK (exemption_type IN ('rule', 'engine', 'value'))",
            "ALTER TABLE enforcement_exemptions ADD CONSTRAINT chk_enforcement_exemptions_scope CHECK (scope IN ('global', 'account', 'sub_account'))",
            "ALTER TABLE quarantine_messages ADD CONSTRAINT chk_quarantine_messages_status CHECK (status IN ('pending', 'released', 'blocked', 'expired'))",
            "ALTER TABLE quarantine_messages ADD CONSTRAINT chk_quarantine_messages_source CHECK (source IN ('single_send', 'campaigns', 'inbox', 'api', 'email_to_sms', 'templates', 'rcs'))",
            "ALTER TABLE quarantine_messages ADD CONSTRAINT chk_quarantine_messages_engine CHECK (primary_engine IN ('senderid', 'content', 'url'))",
            "ALTER TABLE normalisation_characters ADD CONSTRAINT chk_normalisation_characters_type CHECK (character_type IN ('letter', 'digit'))",
            "ALTER TABLE domain_age_cache ADD CONSTRAINT chk_domain_age_cache_status CHECK (lookup_status IN ('success', 'failed', 'timeout', 'unknown'))",
        ];

        foreach ($checkConstraints as $sql) {
            preg_match('/CONSTRAINT\s+(\w+)/', $sql, $m);
            $constraintName = $m[1] ?? '';
            if ($constraintName) {
                $exists = DB::select("SELECT 1 FROM pg_constraint WHERE conname = ?", [$constraintName]);
                if (empty($exists)) {
                    try {
                        DB::statement($sql);
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::warning("Failed to add constraint {$constraintName}: " . $e->getMessage());
                    }
                }
            }
        }

        try {
            DB::statement('ALTER TABLE quarantine_recipients ENABLE ROW LEVEL SECURITY');
        } catch (\Exception $e) {
        }

        $indexExists = DB::select("SELECT 1 FROM pg_indexes WHERE indexname = 'idx_dedup_account_hash'");
        if (empty($indexExists)) {
            try {
                DB::statement('CREATE INDEX idx_dedup_account_hash ON message_dedup_log (account_id, content_hash)');
            } catch (\Exception $e) {
            }
        }
    }

    public function down(): void
    {
        $constraints = [
            'senderid_rules' => ['chk_senderid_rules_action', 'chk_senderid_rules_match_type'],
            'content_rules' => ['chk_content_rules_action', 'chk_content_rules_match_type'],
            'url_rules' => ['chk_url_rules_action', 'chk_url_rules_match_type'],
            'enforcement_exemptions' => ['chk_enforcement_exemptions_engine', 'chk_enforcement_exemptions_type', 'chk_enforcement_exemptions_scope'],
            'quarantine_messages' => ['chk_quarantine_messages_status', 'chk_quarantine_messages_source', 'chk_quarantine_messages_engine'],
            'normalisation_characters' => ['chk_normalisation_characters_type'],
            'domain_age_cache' => ['chk_domain_age_cache_status'],
        ];

        foreach ($constraints as $table => $names) {
            foreach ($names as $name) {
                DB::statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$name}");
            }
        }

        Schema::table('quarantine_messages', function (Blueprint $table) {
            $cols = ['sub_account_id', 'user_id', 'source', 'campaign_id', 'message_body_normalised', 'urls_detected', 'triggered_rules', 'recipient_count', 'scheduled_send_at'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('quarantine_messages', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        if (Schema::hasColumn('url_rules', 'use_normalisation')) {
            Schema::table('url_rules', function (Blueprint $table) {
                $table->dropColumn('use_normalisation');
            });
        }

        DB::statement("DROP INDEX IF EXISTS idx_dedup_account_hash");
    }
};
