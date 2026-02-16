<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Quarantine Messages - flagged messages awaiting admin review
 *
 * When any enforcement rule is set to "quarantine" (flag) mode rather than
 * "block", the matching message lands here for manual human review.
 *
 * Admin can: Release (send message), Block (permanently drop), or let expire.
 * Released messages go to the send queue, respecting any scheduling.
 *
 * DATA CLASSIFICATION: Internal - Message Content (sensitive)
 * SIDE: RED (admin-only review)
 * TENANT ISOLATION: account_id for future portal integration, RLS enabled
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quarantine_messages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Tenant context
            $table->uuid('account_id')->comment('FK to accounts.id');
            $table->uuid('sub_account_id')->nullable()->comment('FK to sub_accounts.id');
            $table->uuid('user_id')->nullable()->comment('FK to users.id - who submitted the message');

            // Message source context
            $table->string('source', 30)
                ->comment('CAMPAIGNS, INBOX, API, EMAIL_TO_SMS, TEMPLATES, RCS');
            $table->string('campaign_id', 100)->nullable()
                ->comment('Campaign reference if message came from a campaign');
            $table->string('sender_id_value', 15)->nullable()
                ->comment('The SenderID used for this message');

            // Message content (full body for review judgment)
            $table->text('message_body')->comment('Full message body for admin review');
            $table->text('message_body_normalised')->nullable()
                ->comment('Message after normalisation applied');

            // Extracted data
            $table->jsonb('urls_detected')->nullable()
                ->comment('Array of URLs found in the message body');

            // Enforcement result
            $table->jsonb('triggered_rules')
                ->comment('Array of {rule_id, rule_name, engine, action, pattern, matched_text}');
            $table->string('primary_engine', 20)
                ->comment('Which engine triggered quarantine: senderid, content, url');

            // Recipient count (full list in quarantine_recipients table)
            $table->integer('recipient_count')->default(0);

            // Scheduling context (for released messages)
            $table->timestamp('scheduled_send_at')->nullable()
                ->comment('Original scheduled time if message had scheduling');

            // Review workflow
            $table->string('status', 20)->default('pending')
                ->comment('pending, released, blocked');
            $table->uuid('reviewer_id')->nullable()->comment('Admin user who reviewed');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('reviewer_notes')->nullable()
                ->comment('Admin must leave a note on review');

            // Expiry
            $table->timestamp('expires_at')->comment('Auto-expire after 24 hours');

            $table->timestamps();

            // Foreign keys
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');

            // Indexes for admin queue
            $table->index('status');
            $table->index('primary_engine');
            $table->index('account_id');
            $table->index('source');
            $table->index('expires_at');
            $table->index(['status', 'created_at']);
            $table->index(['status', 'primary_engine']);
            $table->index(['account_id', 'status']);
        });

        // Separate table for recipients (can be thousands per campaign)
        Schema::create('quarantine_recipients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quarantine_message_id');
            $table->string('recipient_number', 20)->comment('E.164 phone number');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('quarantine_message_id')
                ->references('id')->on('quarantine_messages')
                ->onDelete('cascade');

            $table->index('quarantine_message_id');
        });

        // RLS on quarantine_messages for tenant isolation
        DB::unprepared("ALTER TABLE quarantine_messages ENABLE ROW LEVEL SECURITY");
        DB::unprepared("ALTER TABLE quarantine_messages FORCE ROW LEVEL SECURITY");

        DB::unprepared("
            CREATE POLICY quarantine_messages_isolation ON quarantine_messages
            FOR ALL
            USING (
                account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
            )
            WITH CHECK (
                account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
            );
        ");

        // Admin bypass
        DB::unprepared("
            CREATE POLICY quarantine_messages_admin_access ON quarantine_messages
            FOR ALL
            TO svc_red, ops_admin
            USING (true)
            WITH CHECK (true);
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP POLICY IF EXISTS quarantine_messages_admin_access ON quarantine_messages");
        DB::unprepared("DROP POLICY IF EXISTS quarantine_messages_isolation ON quarantine_messages");
        Schema::dropIfExists('quarantine_recipients');
        Schema::dropIfExists('quarantine_messages');
    }
};
