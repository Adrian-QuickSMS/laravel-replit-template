<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Sender IDs table - SMS SenderID registration and approval workflow
 *
 * DATA CLASSIFICATION: Internal - Messaging Asset
 * SIDE: GREEN (customer portal accessible for own account)
 * TENANT ISOLATION: account_id scoped via RLS + app.current_tenant_id
 *
 * Follows the RcsAgent approval workflow pattern:
 * draft -> submitted -> in_review -> approved/rejected
 * with pending_info/info_provided loop, suspension, and revocation
 */
return new class extends Migration
{
    public function up(): void
    {
        // Create sender_type enum
        DB::statement("CREATE TYPE sender_id_type AS ENUM ('ALPHA', 'NUMERIC', 'SHORTCODE')");

        // Create workflow_status enum
        DB::statement("CREATE TYPE sender_id_status AS ENUM ('draft', 'submitted', 'in_review', 'pending_info', 'info_provided', 'approved', 'rejected', 'suspended', 'revoked')");

        // Create use_case enum
        DB::statement("CREATE TYPE sender_id_use_case AS ENUM ('transactional', 'promotional', 'otp', 'mixed')");

        Schema::create('sender_ids', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('account_id')->comment('FK to accounts.id - owning tenant');

            // SenderID value and type
            $table->string('sender_id_value', 15)->comment('The actual sender ID string');
            // sender_type added via raw SQL below (PostgreSQL ENUM)
            $table->string('brand_name', 255)->comment('Business/brand this SenderID represents');
            $table->string('country_code', 2)->default('GB');

            // Use case (added via raw SQL below)
            $table->text('use_case_description')->nullable();

            // Permission confirmation
            $table->boolean('permission_confirmed')->default(false);
            $table->text('permission_explanation')->nullable();

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

            // Full payload snapshot (like RcsAgent pattern)
            $table->jsonb('full_payload')->nullable();
            $table->boolean('is_locked')->default(false);

            // Default SenderID flag
            $table->boolean('is_default')->default(false)->comment('System default QuickSMS sender');

            // Audit
            $table->uuid('created_by')->nullable()->comment('FK to users.id');
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index('account_id');
            $table->index(['account_id', 'sender_id_value']);
            $table->index('created_at');
        });

        // Add ENUM columns via raw SQL (Blueprint doesn't support PostgreSQL ENUMs natively)
        DB::statement("ALTER TABLE sender_ids ADD COLUMN sender_type sender_id_type NOT NULL DEFAULT 'ALPHA'");
        DB::statement("ALTER TABLE sender_ids ADD COLUMN use_case sender_id_use_case DEFAULT 'transactional'");
        DB::statement("ALTER TABLE sender_ids ADD COLUMN workflow_status sender_id_status NOT NULL DEFAULT 'draft'");

        // Add indexes on ENUM columns
        DB::statement("CREATE INDEX idx_sender_ids_workflow_status ON sender_ids (workflow_status)");
        DB::statement("CREATE INDEX idx_sender_ids_account_status ON sender_ids (account_id, workflow_status)");
        DB::statement("CREATE INDEX idx_sender_ids_sender_type ON sender_ids (sender_type)");

        // UUID generation trigger
        DB::unprepared("
            CREATE OR REPLACE FUNCTION generate_uuid_sender_ids()
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
            CREATE TRIGGER before_insert_sender_ids_uuid
            BEFORE INSERT ON sender_ids
            FOR EACH ROW
            EXECUTE FUNCTION generate_uuid_sender_ids();
        ");

        // Row Level Security
        DB::unprepared("ALTER TABLE sender_ids ENABLE ROW LEVEL SECURITY");
        DB::unprepared("ALTER TABLE sender_ids FORCE ROW LEVEL SECURITY");

        DB::unprepared("
            CREATE POLICY sender_ids_isolation ON sender_ids
            FOR ALL
            USING (
                account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
            )
            WITH CHECK (
                account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
            );
        ");

        DB::unprepared("
            CREATE POLICY sender_ids_service_access ON sender_ids
            FOR ALL
            TO svc_red, ops_admin
            USING (true)
            WITH CHECK (true);
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP POLICY IF EXISTS sender_ids_service_access ON sender_ids");
        DB::unprepared("DROP POLICY IF EXISTS sender_ids_isolation ON sender_ids");
        DB::unprepared("DROP TRIGGER IF EXISTS before_insert_sender_ids_uuid ON sender_ids");
        DB::unprepared("DROP FUNCTION IF EXISTS generate_uuid_sender_ids()");
        Schema::dropIfExists('sender_ids');
        DB::statement("DROP TYPE IF EXISTS sender_id_use_case");
        DB::statement("DROP TYPE IF EXISTS sender_id_status");
        DB::statement("DROP TYPE IF EXISTS sender_id_type");
    }
};
