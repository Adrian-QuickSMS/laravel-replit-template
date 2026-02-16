<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Message Dedup Log - anti-spam duplicate message detection
 *
 * Records hash of (message content + recipient number) for each sent message.
 * When a new message is submitted, we check if the same hash exists within
 * the configurable time window (15/30/60/120 minutes). If so, block as duplicate.
 *
 * Normalisation is applied based on the rule that triggered the check —
 * if normalisation is enabled on the anti-spam rule, the content hash
 * uses the normalised form.
 *
 * DATA CLASSIFICATION: Internal - Message Metadata
 * SIDE: RED (system-level)
 * TENANT ISOLATION: account_id scoped + RLS
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_dedup_log', function (Blueprint $table) {
            $table->id();
            $table->string('content_hash', 64)
                ->comment('SHA-256 of message content (or normalised content)');
            $table->string('recipient_hash', 64)
                ->comment('SHA-256 of recipient phone number');
            $table->string('sender_id_value', 15)->nullable();
            $table->uuid('account_id')->comment('FK to accounts.id');
            $table->string('message_source', 30)
                ->comment('CAMPAIGNS, INBOX, API, EMAIL_TO_SMS, TEMPLATES, RCS');
            $table->boolean('normalisation_applied')->default(false)
                ->comment('Whether normalisation was applied to generate content_hash');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('expires_at')
                ->comment('Based on anti-spam window setting');

            // Primary lookup index: find duplicate by hash pair within time window
            $table->index(['content_hash', 'recipient_hash', 'expires_at'], 'dedup_lookup');
            $table->index(['account_id', 'created_at']);
            $table->index('expires_at'); // For cleanup job
        });

        // RLS for tenant isolation
        DB::unprepared("ALTER TABLE message_dedup_log ENABLE ROW LEVEL SECURITY");
        DB::unprepared("ALTER TABLE message_dedup_log FORCE ROW LEVEL SECURITY");

        DB::unprepared("
            CREATE POLICY message_dedup_log_isolation ON message_dedup_log
            FOR ALL
            USING (
                account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
            )
            WITH CHECK (
                account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
            );
        ");

        DB::unprepared("
            CREATE POLICY message_dedup_log_service_access ON message_dedup_log
            FOR ALL
            TO svc_red, ops_admin
            USING (true)
            WITH CHECK (true);
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP POLICY IF EXISTS message_dedup_log_service_access ON message_dedup_log");
        DB::unprepared("DROP POLICY IF EXISTS message_dedup_log_isolation ON message_dedup_log");
        Schema::dropIfExists('message_dedup_log');
    }
};
