<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * RED SIDE: Account Flags (PostgreSQL Version)
     *
     * DATA CLASSIFICATION: Restricted - Internal Operations
     * SIDE: RED (never accessible to customer portal)
     * TENANT ISOLATION: References tenant via account_id
     *
     * POSTGRESQL ENHANCEMENTS:
     * - Native UUID type for account_id
     * - PostgreSQL ENUM types for fraud_risk_level and payment_status
     * - NO Row Level Security (RLS) - RED side tables are blocked by grants, not RLS
     *
     * SECURITY NOTES:
     * - Contains fraud risk scores, payment status, compliance flags
     * - Portal roles: NO ACCESS (enforced via database grants, not RLS)
     * - Accessed by internal services only (svc_red role)
     * - Customers NEVER see these flags
     * - Affects platform behavior (rate limiting, message routing, etc)
     */
    public function up(): void
    {
        // Create ENUM types
        DB::statement("
            DO $$ BEGIN
                CREATE TYPE fraud_risk_level AS ENUM ('low', 'medium', 'high', 'critical');
            EXCEPTION
                WHEN duplicate_object THEN null;
            END $$;
        ");

        DB::statement("
            DO $$ BEGIN
                CREATE TYPE payment_status AS ENUM ('current', 'overdue', 'suspended', 'collections');
            EXCEPTION
                WHEN duplicate_object THEN null;
            END $$;
        ");

        Schema::create('account_flags', function (Blueprint $table) {
            // Primary key IS the account_id - native UUID
            $table->uuid('account_id')->primary();
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');

            // Fraud and risk (RED - internal only)
            // fraud_risk_level will be added as PostgreSQL ENUM via raw SQL below
            $table->integer('fraud_score')->default(0)->comment('0-100 calculated risk score');
            $table->boolean('under_investigation')->default(false);
            $table->text('investigation_notes')->nullable();

            // Payment status (RED - affects service but not customer-visible)
            // payment_status will be added as PostgreSQL ENUM via raw SQL below
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
        });

        // Add ENUM columns via raw SQL
        DB::statement("ALTER TABLE account_flags ADD COLUMN fraud_risk_level fraud_risk_level DEFAULT 'low' NOT NULL");
        DB::statement("ALTER TABLE account_flags ADD COLUMN payment_status payment_status DEFAULT 'current' NOT NULL");

        // Create indexes
        DB::statement("CREATE INDEX idx_account_flags_fraud_risk ON account_flags (fraud_risk_level)");
        DB::statement("CREATE INDEX idx_account_flags_payment_status ON account_flags (payment_status)");
        DB::statement("CREATE INDEX idx_account_flags_fraud_payment ON account_flags (fraud_risk_level, payment_status)");

        // NO Row Level Security on RED side tables
        // Access control is enforced via database grants (see 01_create_roles_and_grants.sql)
    }

    public function down(): void
    {
        Schema::dropIfExists('account_flags');

        // Drop ENUM types
        DB::statement("DROP TYPE IF EXISTS fraud_risk_level CASCADE");
        DB::statement("DROP TYPE IF EXISTS payment_status CASCADE");
    }
};
