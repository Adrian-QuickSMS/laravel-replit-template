<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rcs_agents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('sub_account_id')->nullable()->index();
            
            $table->string('name', 25);
            $table->string('description', 100);
            $table->string('brand_color', 7)->default('#886CC0');
            
            $table->string('logo_url')->nullable();
            $table->json('logo_crop_metadata')->nullable();
            $table->string('hero_url')->nullable();
            $table->json('hero_crop_metadata')->nullable();
            
            $table->string('support_phone', 20);
            $table->string('website');
            $table->string('support_email');
            $table->string('privacy_url');
            $table->string('terms_url');
            $table->boolean('show_phone')->default(true);
            $table->boolean('show_website')->default(true);
            $table->boolean('show_email')->default(true);
            
            $table->enum('billing_category', ['conversational', 'non-conversational']);
            $table->enum('use_case', ['otp', 'transactional', 'promotional', 'multi-use']);
            $table->string('campaign_frequency', 50);
            $table->string('monthly_volume', 50);
            $table->text('opt_in_description');
            $table->text('opt_out_description');
            $table->text('use_case_overview');
            $table->json('test_numbers')->nullable();
            
            $table->string('company_number', 20);
            $table->string('company_website');
            $table->text('registered_address');
            $table->string('approver_name', 100);
            $table->string('approver_job_title', 100);
            $table->string('approver_email');
            
            $table->enum('status', ['draft', 'submitted', 'in_review', 'approved', 'rejected'])->default('draft');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            
            $table->json('full_payload')->nullable();
            $table->boolean('is_locked')->default(false);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rcs_agents');
    }
};
