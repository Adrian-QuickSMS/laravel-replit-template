<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
        });

        // Standard Email-to-SMS Setups
        Schema::create('email_to_sms_setups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('account_id');
            $table->uuid('sub_account_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type')->default('standard'); // standard, contact_list
            $table->jsonb('originating_emails')->default('[]');
            $table->jsonb('allowed_sender_emails')->default('[]');
            $table->uuid('sender_id_template_id')->nullable();
            $table->string('sender_id')->nullable();
            $table->boolean('subject_overrides_sender_id')->default(false);
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
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_to_sms_setups');
        Schema::dropIfExists('email_to_sms_reporting_groups');
    }
};
