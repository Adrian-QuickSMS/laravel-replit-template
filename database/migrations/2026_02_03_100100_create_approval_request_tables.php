<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('senderid_requests')) {
            Schema::create('senderid_requests', function (Blueprint $table) {
                $table->id();
                $table->uuid('request_uuid')->unique();
                $table->unsignedBigInteger('account_id');
                $table->unsignedBigInteger('sub_account_id')->nullable();
                $table->string('sender_id', 11);
                $table->enum('sender_type', ['ALPHA', 'NUMERIC', 'SHORTCODE'])->default('ALPHA');
                $table->text('use_case_description')->nullable();
                $table->json('supporting_documents')->nullable();
                $table->enum('workflow_status', [
                    'SUBMITTED', 'IN_REVIEW', 'RETURNED', 'RESUBMITTED', 
                    'VALIDATION_IN_PROGRESS', 'APPROVED', 'REJECTED', 
                    'PROVISIONING', 'LIVE', 'SUSPENDED', 'ARCHIVED'
                ])->default('SUBMITTED');
                $table->text('review_notes')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->unsignedBigInteger('reviewed_by')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->unsignedInteger('version')->default(1);
                $table->json('version_history')->nullable();
                $table->unsignedBigInteger('submitted_by');
                $table->timestamps();
                $table->softDeletes();

                $table->index(['account_id', 'workflow_status']);
                $table->index(['workflow_status']);
                $table->index(['created_at']);
            });
        }

        if (!Schema::hasTable('rcs_agent_requests')) {
            Schema::create('rcs_agent_requests', function (Blueprint $table) {
                $table->id();
                $table->uuid('request_uuid')->unique();
                $table->unsignedBigInteger('account_id');
                $table->unsignedBigInteger('sub_account_id')->nullable();
                $table->string('agent_name');
                $table->string('brand_name');
                $table->text('agent_description')->nullable();
                $table->string('logo_url')->nullable();
                $table->string('hero_image_url')->nullable();
                $table->string('primary_color', 7)->nullable();
                $table->string('secondary_color', 7)->nullable();
                $table->string('website_url')->nullable();
                $table->string('phone_number')->nullable();
                $table->string('email')->nullable();
                $table->string('privacy_policy_url')->nullable();
                $table->string('terms_url')->nullable();
                $table->json('capabilities')->nullable();
                $table->enum('workflow_status', [
                    'SUBMITTED', 'IN_REVIEW', 'RETURNED', 'RESUBMITTED', 
                    'VALIDATION_IN_PROGRESS', 'APPROVED', 'REJECTED', 
                    'PROVISIONING', 'LIVE', 'SUSPENDED', 'ARCHIVED'
                ])->default('SUBMITTED');
                $table->text('review_notes')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->unsignedBigInteger('reviewed_by')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->unsignedInteger('version')->default(1);
                $table->json('version_history')->nullable();
                $table->unsignedBigInteger('submitted_by');
                $table->timestamps();
                $table->softDeletes();

                $table->index(['account_id', 'workflow_status']);
                $table->index(['workflow_status']);
                $table->index(['created_at']);
            });
        }

        if (!Schema::hasTable('country_requests')) {
            Schema::create('country_requests', function (Blueprint $table) {
                $table->id();
                $table->uuid('request_uuid')->unique();
                $table->unsignedBigInteger('account_id');
                $table->unsignedBigInteger('sub_account_id')->nullable();
                $table->string('country_code', 3);
                $table->string('country_name');
                $table->text('use_case_description')->nullable();
                $table->decimal('estimated_monthly_volume', 12, 0)->nullable();
                $table->json('supporting_documents')->nullable();
                $table->enum('workflow_status', [
                    'SUBMITTED', 'IN_REVIEW', 'RETURNED', 'RESUBMITTED', 
                    'VALIDATION_IN_PROGRESS', 'APPROVED', 'REJECTED', 
                    'PROVISIONING', 'LIVE', 'SUSPENDED', 'ARCHIVED'
                ])->default('SUBMITTED');
                $table->text('review_notes')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->unsignedBigInteger('reviewed_by')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->unsignedInteger('version')->default(1);
                $table->json('version_history')->nullable();
                $table->unsignedBigInteger('submitted_by');
                $table->timestamps();
                $table->softDeletes();

                $table->index(['account_id', 'workflow_status']);
                $table->index(['workflow_status']);
                $table->index(['country_code']);
                $table->index(['created_at']);
            });
        }

        if (!Schema::hasTable('governance_audit_events')) {
            Schema::create('governance_audit_events', function (Blueprint $table) {
                $table->id();
                $table->uuid('event_uuid')->unique();
                $table->string('event_type', 100);
                $table->string('entity_type', 50);
                $table->unsignedBigInteger('entity_id');
                $table->unsignedBigInteger('account_id')->nullable();
                $table->unsignedBigInteger('sub_account_id')->nullable();
                $table->unsignedBigInteger('actor_id');
                $table->enum('actor_type', ['ADMIN', 'CUSTOMER', 'SYSTEM'])->default('ADMIN');
                $table->string('actor_email')->nullable();
                $table->json('before_state')->nullable();
                $table->json('after_state')->nullable();
                $table->text('reason')->nullable();
                $table->string('source_ip', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamp('created_at');

                $table->index(['entity_type', 'entity_id']);
                $table->index(['account_id']);
                $table->index(['actor_id', 'actor_type']);
                $table->index(['event_type']);
                $table->index(['created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('governance_audit_events');
        Schema::dropIfExists('country_requests');
        Schema::dropIfExists('rcs_agent_requests');
        Schema::dropIfExists('senderid_requests');
    }
};
