<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // =====================================================
        // 1. Employee HR Profiles
        // Links a QuickSMS user to their HR identity
        // =====================================================
        Schema::create('employee_hr_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('user_id')->unique();
            $table->string('employee_number', 50)->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('department', 100)->nullable();
            $table->string('job_title', 150)->nullable();
            $table->string('hr_role', 20)->default('employee'); // employee, manager, hr_admin
            $table->uuid('manager_id')->nullable(); // self-ref to employee_hr_profiles.id
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('manager_id')->references('id')->on('employee_hr_profiles')->onDelete('set null');
            $table->index(['tenant_id', 'is_active']);
        });

        // =====================================================
        // 2. Leave Entitlements
        // Per-employee per-year entitlement in quarter-day units
        // =====================================================
        Schema::create('leave_entitlements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id');
            $table->integer('year');
            $table->integer('total_entitlement_units'); // quarter-day units (1 day = 4)
            $table->integer('carried_over_units')->default(0);
            $table->integer('adjustment_units')->default(0); // manual +/- by admin
            $table->integer('purchased_units')->default(0); // future: bought extra days
            $table->boolean('is_prorated')->default(false);
            $table->text('prorate_note')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employee_hr_profiles')->onDelete('cascade');
            $table->unique(['employee_id', 'year']);
        });

        // =====================================================
        // 3. Leave Requests
        // =====================================================
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('employee_id');
            $table->string('leave_type', 30); // annual_leave, sickness, medical
            $table->string('status', 20)->default('pending'); // pending, approved, rejected, cancelled
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('duration_units'); // quarter-day units
            $table->decimal('duration_days_display', 6, 2); // human-readable days (e.g., 1.5)
            $table->string('day_portion', 20)->default('full'); // full, half_am, half_pm, quarter
            $table->text('employee_note')->nullable();
            $table->uuid('approver_id')->nullable();
            $table->text('approval_comment')->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employee_hr_profiles')->onDelete('cascade');
            $table->foreign('approver_id')->references('id')->on('employee_hr_profiles')->onDelete('set null');
            $table->index(['employee_id', 'status']);
            $table->index(['tenant_id', 'status']);
            $table->index(['start_date', 'end_date']);
        });

        // =====================================================
        // 4. Leave Audit Log
        // =====================================================
        Schema::create('leave_audit_log', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('actor_id'); // user who performed the action
            $table->string('action', 50); // request_submitted, request_approved, request_rejected, request_cancelled, entitlement_changed
            $table->uuid('target_employee_id'); // affected employee
            $table->uuid('leave_request_id')->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('tenant_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->index(['target_employee_id', 'created_at']);
        });

        // =====================================================
        // 5. Company HR Settings
        // =====================================================
        Schema::create('company_hr_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->unique();
            $table->integer('default_annual_entitlement_units')->default(100); // 25 days = 100 units
            $table->integer('holiday_year_start_month')->default(1); // January
            $table->integer('holiday_year_start_day')->default(1);
            $table->boolean('email_notifications_enabled')->default(false);
            $table->boolean('ics_generation_enabled')->default(true);
            $table->string('notification_email_from', 255)->nullable();
            $table->string('team_notification_email', 255)->nullable();
            $table->boolean('show_leave_type_in_notifications')->default(false); // privacy: hide sickness/medical
            $table->json('weekend_days')->default('[6,0]'); // Saturday=6, Sunday=0
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('accounts')->onDelete('cascade');
        });

        // =====================================================
        // 6. UK Bank Holidays (shared reference table)
        // =====================================================
        Schema::create('bank_holidays', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable()->index(); // null = system-wide
            $table->date('holiday_date')->index();
            $table->string('name', 150);
            $table->string('region', 50)->default('england-and-wales');
            $table->integer('year');
            $table->timestamps();

            $table->unique(['tenant_id', 'holiday_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_holidays');
        Schema::dropIfExists('company_hr_settings');
        Schema::dropIfExists('leave_audit_log');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('leave_entitlements');
        Schema::dropIfExists('employee_hr_profiles');
    }
};
