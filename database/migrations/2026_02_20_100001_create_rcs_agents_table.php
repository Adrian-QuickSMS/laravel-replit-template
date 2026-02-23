<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * RCS Agents table - RCS Agent registration and approval workflow
 *
 * DATA CLASSIFICATION: Internal - Messaging Asset
 * SIDE: GREEN (customer portal accessible for own account)
 * TENANT ISOLATION: account_id scoped via RLS + app.current_tenant_id
 *
 * Follows the SenderId approval workflow pattern:
 * draft -> submitted -> in_review -> approved/rejected
 * with pending_info/info_provided loop, suspension, and revocation
 */
return new class extends Migration
{
    public function up(): void
    {
        // Create billing_category enum
        DB::statement("CREATE TYPE rcs_billing_category AS ENUM ('conversational', 'non-conversational')");

        // Create use_case enum
        DB::statement("CREATE TYPE rcs_use_case AS ENUM ('otp', 'transactional', 'promotional', 'multi-use')");

        // Create workflow_status enum
        DB::statement("CREATE TYPE rcs_agent_status AS ENUM ('draft', 'submitted', 'in_review', 'pending_info', 'info_provided', 'approved', 'rejected', 'suspended', 'revoked')");

        Schema::create('rcs_agents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('account_id')->comment('FK to accounts.id - owning tenant');

            // Agent identity
            $table->string('name', 25)->comment('RCS Agent display name');
            $table->string('description', 100)->comment('Short agent description');
            $table->string('brand_color', 7)->default('#886CC0')->comment('Hex colour code');

            // Media
            $table->string('logo_url')->nullable()->comment('Agent logo (222x222)');
            $table->jsonb('logo_crop_metadata')->nullable();
            $table->string('hero_url')->nullable()->comment('Hero image (1480x448)');
            $table->jsonb('hero_crop_metadata')->nullable();

            // Contact details
            $table->string('support_phone', 20)->nullable();
            $table->string('website')->nullable();
            $table->string('support_email')->nullable();
            $table->string('privacy_url')->nullable();
            $table->string('terms_url')->nullable();
            $table->boolean('show_phone')->default(true);
            $table->boolean('show_website')->default(true);
            $table->boolean('show_email')->default(true);

            // Use case / campaign details (billing_category and use_case added via raw SQL)
            $table->string('campaign_frequency', 50)->nullable();
            $table->string('monthly_volume', 50)->nullable();
            $table->text('opt_in_description')->nullable();
            $table->text('opt_out_description')->nullable();
            $table->text('use_case_overview')->nullable();
            $table->jsonb('test_numbers')->nullable();

            // Company details
            $table->string('company_number', 20)->nullable();
            $table->string('company_website')->nullable();
            $table->text('registered_address')->nullable();

            // Approver details
            $table->string('approver_name', 100)->nullable();
            $table->string('approver_job_title', 100)->nullable();
            $table->string('approver_email')->nullable();

            // Workflow (status added via raw SQL below)
            $table->timestamp('submitted_at')->nullable();
            $table->uuid('reviewed_by')->nullable()->comment('FK to admin users.id');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('admin_notes')->nullable()->comment('RED side - internal admin notes');
            $table->text('suspension_reason')->nullable();
            $table->text('revocation_reason')->nullable();
            $table->text('additional_info')->nullable()->comment('Customer response to pending_info request');

            // Version tracking for re-submissions
            $table->integer('version')->default(1);
            $table->jsonb('version_history')->nullable();

            // Full payload snapshot (like SenderId pattern)
            $table->jsonb('full_payload')->nullable();
            $table->boolean('is_locked')->default(false);

            // Audit
            $table->uuid('created_by')->nullable()->comment('FK to users.id');
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index('account_id');
            $table->index(['account_id', 'name']);
            $table->index('created_at');
        });

        // Add ENUM columns via raw SQL (Blueprint doesn't support PostgreSQL ENUMs natively)
        DB::statement("ALTER TABLE rcs_agents ADD COLUMN billing_category rcs_billing_category DEFAULT 'non-conversational'");
        DB::statement("ALTER TABLE rcs_agents ADD COLUMN use_case rcs_use_case DEFAULT 'transactional'");
        DB::statement("ALTER TABLE rcs_agents ADD COLUMN workflow_status rcs_agent_status NOT NULL DEFAULT 'draft'");

        // Add indexes on ENUM columns
        DB::statement("CREATE INDEX idx_rcs_agents_workflow_status ON rcs_agents (workflow_status)");
        DB::statement("CREATE INDEX idx_rcs_agents_account_status ON rcs_agents (account_id, workflow_status)");
        DB::statement("CREATE INDEX idx_rcs_agents_billing_category ON rcs_agents (billing_category)");

        // UUID generation trigger
        DB::unprepared("
            CREATE OR REPLACE FUNCTION generate_uuid_rcs_agents()
            RETURNS TRIGGER AS \$\$
            BEGIN
                IF NEW.uuid IS NULL THEN
                    NEW.uuid = gen_random_uuid();
                END IF;
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        DB::unprepared("
            CREATE TRIGGER before_insert_rcs_agents_uuid
            BEFORE INSERT ON rcs_agents
            FOR EACH ROW
            EXECUTE FUNCTION generate_uuid_rcs_agents();
        ");

        // Row Level Security
        DB::unprepared("ALTER TABLE rcs_agents ENABLE ROW LEVEL SECURITY");
        DB::unprepared("ALTER TABLE rcs_agents FORCE ROW LEVEL SECURITY");

        DB::unprepared("
            CREATE POLICY rcs_agents_isolation ON rcs_agents
            FOR ALL
            USING (
                account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
            )
            WITH CHECK (
                account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
            );
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP POLICY IF EXISTS rcs_agents_isolation ON rcs_agents");
        DB::unprepared("DROP TRIGGER IF EXISTS before_insert_rcs_agents_uuid ON rcs_agents");
        DB::unprepared("DROP FUNCTION IF EXISTS generate_uuid_rcs_agents()");
        Schema::dropIfExists('rcs_agents');
        DB::statement("DROP TYPE IF EXISTS rcs_agent_status");
        DB::statement("DROP TYPE IF EXISTS rcs_use_case");
        DB::statement("DROP TYPE IF EXISTS rcs_billing_category");
    }
};
