<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_entitlements', function (Blueprint $table) {
            $table->integer('gifted_units')->default(0)->after('purchased_units');
        });

        Schema::table('hr_settings', function (Blueprint $table) {
            $table->integer('max_additional_units')->default(20)->after('default_annual_entitlement_units');
            $table->boolean('allow_purchase')->default(true)->after('max_additional_units');
            $table->boolean('allow_toil')->default(true)->after('allow_purchase');
            $table->boolean('allow_carry_over')->default(true)->after('allow_toil');
            $table->string('slack_webhook_url', 500)->nullable()->after('weekend_days');
            $table->string('teams_webhook_url', 500)->nullable()->after('slack_webhook_url');
        });

        Schema::create('holiday_adjustment_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('employee_id');
            $table->string('type', 30);
            $table->string('status', 20)->default('pending');
            $table->integer('units');
            $table->integer('year');
            $table->uuid('requested_by')->nullable();
            $table->uuid('approved_by')->nullable();
            $table->text('reason')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employee_hr_profiles')->onDelete('cascade');
            $table->foreign('requested_by')->references('id')->on('employee_hr_profiles')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('employee_hr_profiles')->onDelete('set null');
            $table->index(['employee_id', 'year', 'type']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holiday_adjustment_requests');

        Schema::table('hr_settings', function (Blueprint $table) {
            $table->dropColumn([
                'max_additional_units', 'allow_purchase', 'allow_toil',
                'allow_carry_over', 'slack_webhook_url', 'teams_webhook_url',
            ]);
        });

        Schema::table('leave_entitlements', function (Blueprint $table) {
            $table->dropColumn('gifted_units');
        });
    }
};
