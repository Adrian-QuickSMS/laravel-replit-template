<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS hr_role VARCHAR(20) NOT NULL DEFAULT 'none'");
        DB::statement("ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS birthday DATE");

        Schema::create('employee_hr_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('admin_user_id')->unique();
            $table->string('employee_number', 50)->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('department', 100)->nullable();
            $table->string('job_title', 150)->nullable();
            $table->string('hr_role', 20)->default('employee');
            $table->uuid('manager_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('admin_user_id')->references('id')->on('admin_users')->onDelete('cascade');
            $table->index('is_active');
        });

        Schema::table('employee_hr_profiles', function (Blueprint $table) {
            $table->foreign('manager_id')->references('id')->on('employee_hr_profiles')->onDelete('set null');
        });

        Schema::create('leave_entitlements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('employee_id');
            $table->integer('year');
            $table->integer('total_entitlement_units')->default(116);
            $table->integer('carried_over_units')->default(0);
            $table->integer('adjustment_units')->default(0);
            $table->integer('purchased_units')->default(0);
            $table->boolean('is_prorated')->default(false);
            $table->text('prorate_note')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employee_hr_profiles')->onDelete('cascade');
            $table->unique(['employee_id', 'year']);
        });

        Schema::create('leave_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('employee_id');
            $table->string('leave_type', 30);
            $table->string('status', 20)->default('pending');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('duration_units');
            $table->decimal('duration_days_display', 6, 2);
            $table->string('day_portion', 20)->default('full');
            $table->text('employee_note')->nullable();
            $table->uuid('approver_id')->nullable();
            $table->text('approval_comment')->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employee_hr_profiles')->onDelete('cascade');
            $table->foreign('approver_id')->references('id')->on('employee_hr_profiles')->onDelete('set null');
            $table->index(['employee_id', 'status']);
            $table->index(['status']);
            $table->index(['start_date', 'end_date']);
        });

        Schema::create('leave_audit_log', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('actor_id');
            $table->string('action', 50);
            $table->uuid('target_employee_id');
            $table->uuid('leave_request_id')->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['target_employee_id', 'created_at']);
            $table->index('actor_id');
        });

        Schema::create('hr_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('default_annual_entitlement_units')->default(116);
            $table->integer('holiday_year_start_month')->default(1);
            $table->integer('holiday_year_start_day')->default(1);
            $table->boolean('email_notifications_enabled')->default(false);
            $table->boolean('birthday_leave_enabled')->default(true);
            $table->string('team_notification_email', 255)->nullable();
            $table->boolean('show_leave_type_in_notifications')->default(false);
            $table->json('weekend_days')->default('[6,0]');
            $table->timestamps();
        });

        Schema::create('bank_holidays', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('holiday_date')->index();
            $table->string('name', 150);
            $table->string('region', 50)->default('england-and-wales');
            $table->integer('year');
            $table->timestamps();

            $table->unique('holiday_date');
        });

        DB::statement("
            CREATE OR REPLACE FUNCTION leave_audit_immutable()
            RETURNS TRIGGER AS \$\$
            BEGIN
                IF TG_OP = 'UPDATE' OR TG_OP = 'DELETE' THEN
                    RAISE EXCEPTION 'leave_audit_log is immutable: % not allowed', TG_OP;
                END IF;
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        DB::statement("
            CREATE TRIGGER trg_leave_audit_immutable
            BEFORE UPDATE OR DELETE ON leave_audit_log
            FOR EACH ROW EXECUTE FUNCTION leave_audit_immutable();
        ");
    }

    public function down(): void
    {
        DB::statement("DROP TRIGGER IF EXISTS trg_leave_audit_immutable ON leave_audit_log");
        DB::statement("DROP FUNCTION IF EXISTS leave_audit_immutable()");
        Schema::dropIfExists('bank_holidays');
        Schema::dropIfExists('hr_settings');
        Schema::dropIfExists('leave_audit_log');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('leave_entitlements');
        Schema::dropIfExists('employee_hr_profiles');
        DB::statement("ALTER TABLE admin_users DROP COLUMN IF EXISTS hr_role");
        DB::statement("ALTER TABLE admin_users DROP COLUMN IF EXISTS birthday");
    }
};
