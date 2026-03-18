<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Held Messages — stores messages blocked by out-of-hours restrictions
 * for automatic release when the sending window opens.
 *
 * Applies to all send methods: campaigns, API single sends, email-to-SMS.
 *
 * DATA CLASSIFICATION: Internal - Message Queue
 * SIDE: GREEN (system-managed, tenant-scoped)
 * TENANT ISOLATION: tenant_id + RLS
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('held_messages')) {
            return;
        }

        Schema::create('held_messages', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('tenant_id')->comment('FK to accounts.id — RLS key');
            $table->string('recipient_number', 20)->comment('E.164 phone number');
            $table->text('message_content')->comment('Message body (encrypted at rest)');
            $table->string('sender_id', 15)->comment('Sender ID / number');
            $table->string('message_type', 20)->default('sms')->comment('sms, rcs_basic, rcs_rich');
            $table->string('origin', 30)->comment('portal, api, email_to_sms, campaign');
            $table->uuid('campaign_id')->nullable()->comment('FK to campaigns.id if from a campaign');
            $table->uuid('campaign_recipient_id')->nullable()->comment('FK to campaign_recipients.id');
            $table->uuid('sub_account_id')->nullable();
            $table->uuid('user_id')->nullable()->comment('User who initiated the send');
            $table->string('held_reason', 50)->default('out_of_hours');
            $table->timestamp('release_after')->comment('When this message can be released');
            $table->string('status', 20)->default('held')->comment('held, released, expired, cancelled');
            $table->timestamp('released_at')->nullable();
            $table->jsonb('metadata')->nullable()->comment('Additional context (encoding, fragments, etc.)');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->index(['status', 'release_after'], 'held_messages_release_lookup');
            $table->index(['tenant_id', 'status']);
            $table->index(['campaign_id', 'status']);
        });

        // Enable RLS
        DB::unprepared("ALTER TABLE held_messages ENABLE ROW LEVEL SECURITY");
        DB::unprepared("ALTER TABLE held_messages FORCE ROW LEVEL SECURITY");

        DB::unprepared("
            CREATE POLICY held_messages_isolation ON held_messages
            FOR ALL
            USING (
                tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
            )
            WITH CHECK (
                tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
            );
        ");

        DB::unprepared("
            CREATE POLICY held_messages_service_access ON held_messages
            FOR ALL
            TO svc_red, ops_admin
            USING (true)
            WITH CHECK (true);
        ");

        DB::unprepared("
            CREATE POLICY held_messages_postgres_bypass ON held_messages
            FOR ALL
            TO postgres
            USING (true)
            WITH CHECK (true);
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP POLICY IF EXISTS held_messages_postgres_bypass ON held_messages");
        DB::unprepared("DROP POLICY IF EXISTS held_messages_service_access ON held_messages");
        DB::unprepared("DROP POLICY IF EXISTS held_messages_isolation ON held_messages");
        Schema::dropIfExists('held_messages');
    }
};
