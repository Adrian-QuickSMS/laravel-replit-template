<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("DROP POLICY IF EXISTS email_to_sms_audit_log_isolation ON email_to_sms_audit_log");
        DB::unprepared("DROP POLICY IF EXISTS email_to_sms_setups_isolation ON email_to_sms_setups");
        DB::unprepared("DROP POLICY IF EXISTS email_to_sms_reporting_groups_isolation ON email_to_sms_reporting_groups");

        Schema::dropIfExists('email_to_sms_audit_log');
        Schema::dropIfExists('email_to_sms_setups');
        Schema::dropIfExists('email_to_sms_reporting_groups');

        Schema::create('email_to_sms_reporting_groups', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('account_id');
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->unique(['account_id', 'name']);
            $table->index(['account_id', 'status']);
        });

        Schema::create('email_to_sms_setups', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('account_id');
            $table->uuid('sub_account_id')->nullable();
            $table->string('type', 20);
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active');
            $table->uuid('reporting_group_id')->nullable();
            $table->uuid('sender_id_template_id')->nullable();
            $table->string('sender_id_label', 11)->nullable();
            $table->boolean('multiple_sms_enabled')->default(true);
            $table->boolean('delivery_reports_enabled')->default(false);
            $table->string('delivery_report_email', 255)->nullable();
            $table->integer('daily_limit')->nullable()->default(5000);
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->foreign('sub_account_id')->references('id')->on('sub_accounts')->onDelete('set null');
            $table->foreign('reporting_group_id')->references('id')->on('email_to_sms_reporting_groups')->onDelete('set null');
            $table->index(['account_id', 'status']);
            $table->index(['account_id', 'type', 'status']);
            $table->unique(['account_id', 'name']);
        });

        Schema::create('email_to_sms_addresses', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('setup_id');
            $table->uuid('account_id');
            $table->string('email_address', 255);
            $table->boolean('is_primary')->default(true);
            $table->string('status', 20)->default('active');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('setup_id')->references('id')->on('email_to_sms_setups')->onDelete('cascade');
            $table->unique(['email_address']);
            $table->index(['setup_id']);
        });

        Schema::create('email_to_sms_allowed_senders', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('setup_id');
            $table->uuid('account_id');
            $table->string('email_pattern', 255);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('setup_id')->references('id')->on('email_to_sms_setups')->onDelete('cascade');
            $table->index(['setup_id']);
        });

        Schema::create('email_to_sms_recipients', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('setup_id');
            $table->uuid('account_id');
            $table->string('recipient_type', 20);
            $table->uuid('recipient_id');
            $table->string('recipient_name', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('setup_id')->references('id')->on('email_to_sms_setups')->onDelete('cascade');
            $table->index(['setup_id', 'recipient_type']);
            $table->unique(['setup_id', 'recipient_type', 'recipient_id']);
        });

        Schema::create('email_to_sms_opt_out_config', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('setup_id');
            $table->uuid('account_id');
            $table->uuid('opt_out_list_id');
            $table->string('opt_out_list_name', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('setup_id')->references('id')->on('email_to_sms_setups')->onDelete('cascade');
            $table->unique(['setup_id', 'opt_out_list_id']);
        });

        Schema::create('email_to_sms_audit_log', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('setup_id')->nullable();
            $table->uuid('reporting_group_id')->nullable();
            $table->uuid('account_id');
            $table->string('action', 30);
            $table->uuid('user_id')->nullable();
            $table->string('user_name', 255)->nullable();
            $table->text('details')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('setup_id')->references('id')->on('email_to_sms_setups')->onDelete('set null');
            $table->foreign('reporting_group_id')->references('id')->on('email_to_sms_reporting_groups')->onDelete('set null');
            $table->index(['setup_id', 'created_at']);
            $table->index(['account_id', 'created_at']);
        });

        $tables = [
            'email_to_sms_reporting_groups',
            'email_to_sms_setups',
            'email_to_sms_addresses',
            'email_to_sms_allowed_senders',
            'email_to_sms_recipients',
            'email_to_sms_opt_out_config',
            'email_to_sms_audit_log',
        ];

        foreach ($tables as $table) {
            DB::unprepared("ALTER TABLE {$table} ENABLE ROW LEVEL SECURITY");
            DB::unprepared("ALTER TABLE {$table} FORCE ROW LEVEL SECURITY");
            DB::unprepared("
                CREATE POLICY {$table}_isolation ON {$table}
                FOR ALL
                USING (
                    account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
                )
                WITH CHECK (
                    account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
                );
            ");
        }
    }

    public function down(): void
    {
        $tables = [
            'email_to_sms_audit_log',
            'email_to_sms_opt_out_config',
            'email_to_sms_recipients',
            'email_to_sms_allowed_senders',
            'email_to_sms_addresses',
            'email_to_sms_setups',
            'email_to_sms_reporting_groups',
        ];

        foreach ($tables as $table) {
            DB::unprepared("DROP POLICY IF EXISTS {$table}_isolation ON {$table}");
        }

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }
    }
};
