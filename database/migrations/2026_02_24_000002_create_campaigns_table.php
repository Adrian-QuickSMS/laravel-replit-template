<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('account_id');
            $table->uuid('sub_account_id')->nullable();

            // Campaign identification
            $table->string('name', 255);
            $table->text('description')->nullable();

            // Message type: sms | rcs_basic | rcs_single
            // For RCS campaigns, SMS fallback is handled by the gateway
            $table->string('type', 30);

            // Campaign state machine
            // draft -> scheduled -> queued -> sending -> completed
            //       -> sending (send-now) ----^           ^
            //                    sending -> paused -> sending (resume)
            //                    sending -> failed
            //                    scheduled -> cancelled
            //                    paused -> cancelled
            $table->string('status', 30)->default('draft');

            // ========================================
            // MESSAGE CONTENT
            // ========================================

            // Optional link to a reusable template (content can diverge after selection)
            $table->uuid('message_template_id')->nullable();

            // SMS / RCS Basic: the text body (with placeholders like {{first_name}})
            $table->text('message_content')->nullable();

            // RCS Rich: structured content (cards, carousels, suggested actions, media)
            $table->jsonb('rcs_content')->nullable();

            // Pre-calculated encoding/segment info for SMS
            $table->string('encoding', 10)->nullable(); // gsm7, unicode
            $table->integer('segment_count')->default(1);

            // ========================================
            // SENDER
            // ========================================

            // SMS sender (FK to sender_ids, must be approved)
            $table->unsignedBigInteger('sender_id_id')->nullable();

            // RCS agent (FK to rcs_agents, must be approved)
            $table->unsignedBigInteger('rcs_agent_id')->nullable();

            // ========================================
            // RECIPIENTS (summary - detail in campaign_recipients)
            // ========================================

            // Source configuration: which lists, tags, CSVs, manual numbers were selected
            $table->jsonb('recipient_sources')->default('[]');

            // Recipient counts (updated during resolution)
            $table->integer('total_recipients')->default(0);
            $table->integer('total_unique_recipients')->default(0);
            $table->integer('total_opted_out')->default(0);
            $table->integer('total_invalid')->default(0);

            // ========================================
            // SCHEDULING
            // ========================================

            $table->timestamp('scheduled_at')->nullable();
            $table->string('timezone', 50)->nullable();

            // ========================================
            // SEND CONFIGURATION
            // ========================================

            // Messages per second (0 = no throttle / system default)
            $table->integer('send_rate')->default(0);
            // Batch size for job chunking
            $table->integer('batch_size')->default(1000);

            // ========================================
            // PROGRESS TRACKING
            // ========================================

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('paused_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('failed_at')->nullable();

            // Delivery counters (updated in real-time via atomic increments)
            $table->integer('sent_count')->default(0);
            $table->integer('delivered_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->integer('pending_count')->default(0);

            // For RCS campaigns: how many fell back to SMS
            $table->integer('fallback_sms_count')->default(0);

            // ========================================
            // BILLING
            // ========================================

            // Pre-send cost estimate
            $table->decimal('estimated_cost', 14, 4)->default(0);
            // Actual cost (accumulated as messages are billed)
            $table->decimal('actual_cost', 14, 4)->default(0);
            $table->string('currency', 3)->default('GBP');

            // Link to campaign_reservations (created by BalanceService::reserveForCampaign)
            $table->uuid('reservation_id')->nullable();

            // ========================================
            // METADATA
            // ========================================

            $table->jsonb('tags')->default('[]');
            $table->jsonb('metadata')->default('{}');

            // Audit
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // ========================================
            // INDEXES
            // ========================================

            $table->index('account_id');
            $table->index(['account_id', 'status']);
            $table->index(['account_id', 'type']);
            $table->index(['account_id', 'created_at']);
            $table->index('status');
            $table->index('scheduled_at');
            $table->index('reservation_id');

            // ========================================
            // FOREIGN KEYS
            // ========================================

            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->foreign('message_template_id')->references('id')->on('message_templates')->onDelete('set null');
            $table->foreign('sender_id_id')->references('id')->on('sender_ids')->onDelete('set null');
            $table->foreign('rcs_agent_id')->references('id')->on('rcs_agents')->onDelete('set null');
            $table->foreign('reservation_id')->references('id')->on('campaign_reservations')->onDelete('set null');
        });

        // CHECK constraints
        DB::statement("ALTER TABLE campaigns ADD CONSTRAINT chk_campaigns_type CHECK (type IN ('sms', 'rcs_basic', 'rcs_single'))");
        DB::statement("ALTER TABLE campaigns ADD CONSTRAINT chk_campaigns_status CHECK (status IN ('draft', 'scheduled', 'queued', 'sending', 'paused', 'completed', 'cancelled', 'failed'))");

        // RLS policy
        DB::statement("ALTER TABLE campaigns ENABLE ROW LEVEL SECURITY");
        DB::statement("DO \$\$ BEGIN
            IF NOT EXISTS (SELECT 1 FROM pg_policies WHERE tablename = 'campaigns' AND policyname = 'tenant_isolation_campaigns') THEN
                EXECUTE 'CREATE POLICY tenant_isolation_campaigns ON campaigns
                    USING (account_id = current_setting(''app.current_tenant_id'', true)::uuid)';
            END IF;
        END \$\$");
    }

    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
