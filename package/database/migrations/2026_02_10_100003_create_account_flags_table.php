<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * RED SIDE: Account Flags (Internal Risk/Status)
     *
     * DATA CLASSIFICATION: Restricted - Internal Operations
     * SIDE: RED (never accessible to customer portal)
     * TENANT ISOLATION: References tenant via account_id
     *
     * SECURITY NOTES:
     * - Contains fraud risk scores, payment status, compliance flags
     * - Portal roles: NO ACCESS
     * - Accessed by internal services only (svc_red role)
     * - Customers NEVER see these flags
     * - Affects platform behavior (rate limiting, message routing, etc)
     */
    public function up(): void
    {
        Schema::create('account_flags', function (Blueprint $table) {
            $table->binary('account_id', 16)->primary();
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');

            // Fraud and risk (RED - internal only)
            $table->enum('fraud_risk_level', ['low', 'medium', 'high', 'critical'])->default('low');
            $table->integer('fraud_score')->default(0)->comment('0-100 calculated risk score');
            $table->boolean('under_investigation')->default(false);
            $table->text('investigation_notes')->nullable();

            // Payment status (RED - affects service but not customer-visible)
            $table->enum('payment_status', ['current', 'overdue', 'suspended', 'collections'])->default('current');
            $table->decimal('outstanding_balance', 10, 2)->default(0.00);
            $table->date('last_payment_date')->nullable();

            // Platform limits (RED - internal controls)
            $table->integer('daily_message_limit')->default(1000);
            $table->integer('messages_sent_today')->default(0);
            $table->date('limit_reset_date')->nullable();

            // Rate limiting (RED - anti-abuse)
            $table->integer('api_rate_limit_per_minute')->default(60);
            $table->boolean('rate_limit_exceeded')->default(false);
            $table->timestamp('rate_limit_reset_at')->nullable();

            // Compliance flags (RED)
            $table->boolean('kyc_completed')->default(false);
            $table->boolean('aml_check_passed')->default(false);
            $table->timestamp('last_compliance_review')->nullable();

            // Account health (RED)
            $table->boolean('deliverability_issues')->default(false);
            $table->decimal('spam_complaint_rate', 5, 2)->default(0.00)->comment('Percentage');
            $table->integer('consecutive_failed_sends')->default(0);

            // Audit
            $table->string('updated_by')->nullable();
            $table->timestamps();

            $table->index('fraud_risk_level');
            $table->index('payment_status');
            $table->index(['fraud_risk_level', 'payment_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_flags');
    }
};
