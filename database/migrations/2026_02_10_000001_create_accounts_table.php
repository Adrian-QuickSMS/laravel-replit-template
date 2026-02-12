<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * GREEN SIDE: Customer Accounts (Tenants)
     *
     * DATA CLASSIFICATION: Internal - Customer Configuration
     * SIDE: GREEN (customer accessible via views)
     * TENANT ISOLATION: This IS the tenant table (root of isolation)
     *
     * SECURITY NOTES:
     * - UUIDs stored as BINARY(16) to prevent enumeration
     * - Portal users access via account_safe_view only
     * - Direct SELECT blocked for portal roles
     */
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            // Primary identifier - UUID stored as BINARY(16)
            $table->binary('id', 16)->primary();

            // Account identification
            $table->string('account_number', 20)->unique()->comment('Public account identifier');
            $table->string('company_name');
            $table->string('trading_name')->nullable();

            // Account status
            $table->enum('status', ['pending_verification', 'active', 'suspended', 'closed'])->default('pending_verification');
            $table->enum('account_type', ['trial', 'prepay', 'postpay', 'system'])->default('trial');

            // Contact details (GREEN - customer can see/edit)
            $table->string('email'); // Primary account email
            $table->string('billing_email')->nullable();
            $table->string('phone')->nullable();

            // Address (GREEN)
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city')->nullable();
            $table->string('county')->nullable();
            $table->string('postcode')->nullable();
            $table->string('country', 2)->default('GB');

            // Business details (GREEN)
            $table->string('vat_number')->nullable();
            // company_number field defined in activation migration (2026_02_11_000001_extend_accounts_for_activation.php)

            // Verification tracking
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('phone_verified')->default(false);

            // External integration IDs (GREEN - hashed references only)
            $table->string('hubspot_company_id')->nullable()->unique();
            $table->timestamp('last_hubspot_sync')->nullable();

            // Consent tracking (GDPR compliance)
            // Terms of Service
            $table->timestamp('terms_accepted_at')->nullable();
            $table->string('terms_accepted_ip', 45)->nullable();
            $table->string('terms_version', 20)->nullable();

            // Privacy Policy
            $table->timestamp('privacy_accepted_at')->nullable();
            $table->string('privacy_accepted_ip', 45)->nullable();
            $table->string('privacy_version', 20)->nullable();

            // Fraud Prevention (mandatory)
            $table->timestamp('fraud_consent_at')->nullable();
            $table->string('fraud_consent_ip', 45)->nullable();
            $table->string('fraud_consent_version', 20)->nullable();

            // Marketing (optional)
            $table->timestamp('marketing_consent_at')->nullable();
            $table->string('marketing_consent_ip', 45)->nullable();

            // Signup tracking
            $table->string('signup_ip_address', 45)->nullable();
            $table->string('signup_referrer', 512)->nullable();

            // UTM tracking (marketing attribution)
            $table->string('signup_utm_source')->nullable();
            $table->string('signup_utm_medium')->nullable();
            $table->string('signup_utm_campaign')->nullable();
            $table->string('signup_utm_content')->nullable();
            $table->string('signup_utm_term')->nullable();

            // Promotional credits
            $table->integer('signup_credits_awarded')->default(0);
            $table->string('signup_promotion_code')->nullable();

            // Audit fields
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('account_number');
            $table->index('primary_email');
            $table->index('status');
            $table->index(['status', 'account_type']);
            $table->index('hubspot_company_id');
            $table->index('signup_ip_address'); // For fraud detection queries
            $table->index('signup_utm_source'); // For marketing attribution
        });

        // Add UUID generation trigger
        DB::unprepared("
            CREATE TRIGGER before_insert_accounts_uuid
            BEFORE INSERT ON accounts
            FOR EACH ROW
            BEGIN
                IF NEW.id IS NULL OR NEW.id = '' THEN
                    SET NEW.id = UNHEX(REPLACE(UUID(), '-', ''));
                END IF;
            END
        ");

        // Add account number generation trigger
        DB::unprepared("
            CREATE TRIGGER before_insert_accounts_number
            BEFORE INSERT ON accounts
            FOR EACH ROW
            BEGIN
                IF NEW.account_number IS NULL OR NEW.account_number = '' THEN
                    SET NEW.account_number = CONCAT('QS', LPAD((SELECT COALESCE(MAX(CAST(SUBSTRING(account_number, 3) AS UNSIGNED)), 0) + 1 FROM accounts), 8, '0'));
                END IF;
            END
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS before_insert_accounts_uuid");
        DB::unprepared("DROP TRIGGER IF EXISTS before_insert_accounts_number");
        Schema::dropIfExists('accounts');
    }
};
