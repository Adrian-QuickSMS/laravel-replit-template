<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_templates', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('account_id');
            $table->uuid('sub_account_id')->nullable();

            $table->string('name', 255);
            $table->text('description')->nullable();

            // Template type: sms, rcs_basic, rcs_single (rich)
            $table->string('type', 30)->default('sms');

            // SMS / RCS Basic text content
            $table->text('content')->nullable();

            // RCS Rich content (cards, carousels, suggested replies/actions, media refs)
            $table->jsonb('rcs_content')->nullable();

            // Detected merge fields (e.g., ["first_name", "last_name", "custom_data.field"])
            $table->jsonb('placeholders')->default('[]');

            // Character/segment metadata (pre-calculated for SMS)
            $table->string('encoding', 10)->nullable(); // gsm7, unicode
            $table->integer('character_count')->default(0);
            $table->integer('segment_count')->default(1);

            // Template lifecycle
            $table->string('status', 20)->default('draft'); // draft, active, archived
            $table->boolean('is_favourite')->default(false);

            // Categorisation
            $table->string('category', 100)->nullable();
            $table->jsonb('tags')->default('[]');

            // Audit trail
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('account_id');
            $table->index(['account_id', 'status']);
            $table->index(['account_id', 'type']);
            $table->index(['account_id', 'category']);

            // Foreign keys
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
        });

        // Add CHECK constraint for type enum
        DB::statement("ALTER TABLE message_templates ADD CONSTRAINT chk_message_templates_type CHECK (type IN ('sms', 'rcs_basic', 'rcs_single'))");
        DB::statement("ALTER TABLE message_templates ADD CONSTRAINT chk_message_templates_status CHECK (status IN ('draft', 'active', 'archived'))");

        // RLS policy
        DB::statement("ALTER TABLE message_templates ENABLE ROW LEVEL SECURITY");
        DB::statement("DO \$\$ BEGIN
            IF NOT EXISTS (SELECT 1 FROM pg_policies WHERE tablename = 'message_templates' AND policyname = 'tenant_isolation_message_templates') THEN
                EXECUTE 'CREATE POLICY tenant_isolation_message_templates ON message_templates
                    USING (account_id = current_setting(''app.current_tenant_id'', true)::uuid)';
            END IF;
        END \$\$");
    }

    public function down(): void
    {
        Schema::dropIfExists('message_templates');
    }
};
