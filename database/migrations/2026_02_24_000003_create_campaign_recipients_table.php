<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_recipients', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('campaign_id');

            // ========================================
            // RECIPIENT IDENTITY
            // ========================================

            // Optional link to contact book (null for manual/CSV entries)
            $table->uuid('contact_id')->nullable();

            // The resolved phone number to send to (E.164 format)
            $table->string('mobile_number', 20);

            // Snapshotted contact data for merge field resolution
            // (avoids re-querying contacts during send, preserves data at send-time)
            $table->string('first_name', 255)->nullable();
            $table->string('last_name', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->jsonb('custom_data')->default('{}');

            // ========================================
            // SOURCE TRACKING
            // ========================================

            // How this recipient was added: list, tag, csv, manual, individual
            $table->string('source', 30)->default('manual');
            // ID of the source (list_id, tag_id, csv import batch id)
            $table->string('source_id')->nullable();

            // ========================================
            // DELIVERY STATUS
            // ========================================

            // pending   -> recipient resolved, waiting to be queued
            // queued    -> job dispatched to queue worker
            // sent      -> submitted to gateway
            // delivered -> gateway confirmed delivery
            // failed    -> gateway reported failure
            // undeliverable -> permanent delivery failure
            // opted_out -> skipped because recipient is on opt-out list
            // skipped   -> skipped for other reasons (invalid number, duplicate, etc.)
            $table->string('status', 20)->default('pending');

            $table->text('failure_reason')->nullable();
            $table->string('failure_code', 50)->nullable();

            // ========================================
            // MESSAGE DETAILS
            // ========================================

            // The personalised message after merge field substitution
            $table->text('resolved_content')->nullable();

            // Which channel was actually used (for RCS campaigns with SMS fallback)
            $table->string('delivered_channel', 20)->nullable(); // sms, rcs_basic, rcs_single

            // SMS segment info
            $table->integer('segments')->default(1);

            // ========================================
            // BILLING
            // ========================================

            $table->decimal('cost', 10, 6)->default(0);
            $table->string('currency', 3)->default('GBP');
            $table->string('country_iso', 2)->nullable(); // detected from phone number, used for pricing

            // ========================================
            // GATEWAY / DELIVERY
            // ========================================

            // Link to message_logs (the canonical delivery record)
            $table->uuid('message_log_id')->nullable();

            // Gateway-assigned message ID (for DLR correlation)
            $table->string('gateway_message_id', 255)->nullable();
            $table->unsignedBigInteger('gateway_id')->nullable();

            // ========================================
            // TIMING
            // ========================================

            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('failed_at')->nullable();

            // ========================================
            // BATCH PROCESSING
            // ========================================

            // Which batch this recipient belongs to (for chunked processing)
            $table->integer('batch_number')->default(0);

            // Retry tracking
            $table->integer('retry_count')->default(0);
            $table->timestamp('next_retry_at')->nullable();

            $table->jsonb('metadata')->default('{}');

            $table->timestamps();

            // ========================================
            // INDEXES
            // ========================================

            // Primary lookups
            $table->index('campaign_id');
            $table->index(['campaign_id', 'status']);
            $table->index(['campaign_id', 'batch_number']);

            // Dedup check: same number shouldn't appear twice in one campaign
            $table->unique(['campaign_id', 'mobile_number']);

            // DLR correlation: find recipient by gateway message ID
            $table->index('gateway_message_id');

            // Message log correlation
            $table->index('message_log_id');

            // Contact linkage
            $table->index('contact_id');

            // Queue worker: find next batch of pending recipients
            $table->index(['campaign_id', 'status', 'batch_number']);

            // ========================================
            // FOREIGN KEYS
            // ========================================

            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');
            // contact_id FK is intentionally omitted — contacts may be soft-deleted
            // message_log_id FK is intentionally omitted — message_logs may be in a separate schema/partition
        });

        // CHECK constraints
        DB::statement("ALTER TABLE campaign_recipients ADD CONSTRAINT chk_cr_status CHECK (status IN ('pending', 'queued', 'sent', 'delivered', 'failed', 'undeliverable', 'opted_out', 'skipped'))");
        DB::statement("ALTER TABLE campaign_recipients ADD CONSTRAINT chk_cr_source CHECK (source IN ('list', 'tag', 'csv', 'manual', 'individual'))");

        // Partial index: quickly find recipients needing retry
        DB::statement("CREATE INDEX idx_cr_retry ON campaign_recipients (campaign_id, next_retry_at) WHERE status = 'failed' AND retry_count < 3 AND next_retry_at IS NOT NULL");
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_recipients');
    }
};
