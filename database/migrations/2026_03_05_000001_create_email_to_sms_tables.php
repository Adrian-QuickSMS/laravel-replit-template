<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Reporting Groups
        Schema::create('email_to_sms_reporting_groups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('account_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('active'); // active, archived
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->index(['account_id', 'status']);
            $table->unique(['account_id', 'name']);
        });

        // Standard Email-to-SMS Setups
        Schema::create('email_to_sms_setups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('account_id');
            $table->uuid('sub_account_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type')->default('standard'); // standard, contact_list
            $table->string('generated_email_address')->nullable();
            $table->jsonb('originating_emails')->default('[]');
            $table->jsonb('allowed_sender_emails')->default('[]');
            $table->uuid('sender_id_template_id')->nullable();
            $table->string('sender_id')->nullable();
            $table->boolean('multiple_sms_enabled')->default(false);
            $table->boolean('delivery_reports_enabled')->default(false);
            $table->string('delivery_reports_email')->nullable();
            $table->string('status')->default('active'); // active, suspended, archived
            $table->uuid('reporting_group_id')->nullable();

            // Contact List specific fields
            $table->jsonb('contact_book_list_ids')->default('[]');
            $table->string('opt_out_mode')->default('NONE'); // NONE, SELECTED
            $table->jsonb('opt_out_list_ids')->default('[]');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->foreign('sub_account_id')->references('id')->on('sub_accounts')->onDelete('set null');
            $table->foreign('reporting_group_id')->references('id')->on('email_to_sms_reporting_groups')->onDelete('set null');
            $table->index(['account_id', 'status']);
            $table->index(['account_id', 'type']);
            $table->index(['sub_account_id']);
            $table->unique(['account_id', 'name']);
            $table->unique(['generated_email_address']);
        });

        // Audit Log for Email-to-SMS actions
        Schema::create('email_to_sms_audit_log', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('account_id');
            $table->uuid('user_id')->nullable();
            $table->uuid('setup_id')->nullable();
            $table->uuid('reporting_group_id')->nullable();
            $table->string('action'); // created, updated, suspended, reactivated, archived, unarchived, deleted
            $table->string('entity_type'); // setup, reporting_group
            $table->jsonb('changes')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->index(['account_id', 'created_at']);
            $table->index(['setup_id']);
            $table->index(['reporting_group_id']);
        });

        // Row Level Security for email_to_sms_setups
        DB::unprepared("ALTER TABLE email_to_sms_setups ENABLE ROW LEVEL SECURITY");
        DB::unprepared("ALTER TABLE email_to_sms_setups FORCE ROW LEVEL SECURITY");
        DB::unprepared("
            CREATE POLICY email_to_sms_setups_isolation ON email_to_sms_setups
            FOR ALL
            USING (
                account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
            )
            WITH CHECK (
                account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
            );
        ");

        // Row Level Security for email_to_sms_reporting_groups
        DB::unprepared("ALTER TABLE email_to_sms_reporting_groups ENABLE ROW LEVEL SECURITY");
        DB::unprepared("ALTER TABLE email_to_sms_reporting_groups FORCE ROW LEVEL SECURITY");
        DB::unprepared("
            CREATE POLICY email_to_sms_reporting_groups_isolation ON email_to_sms_reporting_groups
            FOR ALL
            USING (
                account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
            )
            WITH CHECK (
                account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
            );
        ");

        // Row Level Security for email_to_sms_audit_log
        DB::unprepared("ALTER TABLE email_to_sms_audit_log ENABLE ROW LEVEL SECURITY");
        DB::unprepared("ALTER TABLE email_to_sms_audit_log FORCE ROW LEVEL SECURITY");
        DB::unprepared("
            CREATE POLICY email_to_sms_audit_log_isolation ON email_to_sms_audit_log
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
        DB::unprepared("DROP POLICY IF EXISTS email_to_sms_audit_log_isolation ON email_to_sms_audit_log");
        DB::unprepared("DROP POLICY IF EXISTS email_to_sms_reporting_groups_isolation ON email_to_sms_reporting_groups");
        DB::unprepared("DROP POLICY IF EXISTS email_to_sms_setups_isolation ON email_to_sms_setups");
        Schema::dropIfExists('email_to_sms_audit_log');
        Schema::dropIfExists('email_to_sms_setups');
        Schema::dropIfExists('email_to_sms_reporting_groups');
    }
};
