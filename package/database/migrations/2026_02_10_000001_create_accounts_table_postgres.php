<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * POSTGRESQL VERSION - GREEN SIDE: Customer Accounts (Tenants)
     *
     * DATA CLASSIFICATION: Internal - Customer Configuration
     * SIDE: GREEN (customer accessible via views)
     * TENANT ISOLATION: This IS the tenant table (root of isolation)
     *
     * SECURITY NOTES:
     * - Native PostgreSQL UUID type for performance
     * - Row Level Security (RLS) enabled for defense-in-depth
     * - Portal users access via account_safe_view only
     * - Direct SELECT blocked for portal roles via GRANTS
     *
     * CHANGES FROM MYSQL:
     * - BINARY(16) → native UUID type
     * - UNHEX(UUID()) → gen_random_uuid()
     * - MySQL triggers → PL/pgSQL trigger functions
     * - ENUM fields → PostgreSQL ENUM types
     */
    public function up(): void
    {
        // Create ENUM types first (PostgreSQL requires this)
        DB::statement("CREATE TYPE account_status AS ENUM ('pending_verification', 'active', 'suspended', 'closed')");
        DB::statement("CREATE TYPE account_type AS ENUM ('trial', 'prepay', 'postpay', 'system')");

        Schema::create('accounts', function (Blueprint $table) {
            // Primary identifier - Native PostgreSQL UUID
            $table->uuid('id')->primary();

            // Account identification
            $table->string('account_number', 20)->unique()->comment('Public account identifier (QS00000001)');
            $table->string('company_name');
            $table->string('trading_name')->nullable();

            // Account status (uses ENUM type)
            DB::statement("ALTER TABLE accounts ADD COLUMN status account_status DEFAULT 'pending_verification'");
            DB::statement("ALTER TABLE accounts ADD COLUMN account_type account_type DEFAULT 'trial'");

            // Contact details (GREEN - customer can see/edit)
            $table->string('email')->comment('Primary account email');
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

            // Verification tracking
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('phone_verified')->default(false);

            // External integration IDs (GREEN - hashed references only)
            $table->string('hubspot_company_id')->nullable()->unique();
            $table->timestamp('last_hubspot_sync')->nullable();

            // Consent tracking (GDPR compliance)
            // Terms of Service
            $table->timestamp('terms_accepted_at')->nullable();
            $table->inet('terms_accepted_ip')->nullable();
            $table->string('terms_version', 20)->nullable();

            // Privacy Policy
            $table->timestamp('privacy_accepted_at')->nullable();
            $table->inet('privacy_accepted_ip')->nullable();
            $table->string('privacy_version', 20)->nullable();

            // Fraud Prevention (mandatory)
            $table->timestamp('fraud_consent_at')->nullable();
            $table->inet('fraud_consent_ip')->nullable();
            $table->string('fraud_consent_version', 20)->nullable();

            // Marketing (optional)
            $table->timestamp('marketing_consent_at')->nullable();
            $table->inet('marketing_consent_ip')->nullable();

            // Signup tracking
            $table->inet('signup_ip_address')->nullable();
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
            $table->index('email');
            $table->index('status');
            $table->index(['status', 'account_type']);
            $table->index('hubspot_company_id');
            $table->index('signup_ip_address'); // For fraud detection queries
            $table->index('signup_utm_source'); // For marketing attribution
        });

        // PL/pgSQL trigger function for UUID generation
        DB::unprepared("
            CREATE OR REPLACE FUNCTION generate_uuid_accounts()
            RETURNS TRIGGER AS \$\$
            BEGIN
                IF NEW.id IS NULL THEN
                    NEW.id = gen_random_uuid();
                END IF;
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        DB::unprepared("
            CREATE TRIGGER before_insert_accounts_uuid
            BEFORE INSERT ON accounts
            FOR EACH ROW
            EXECUTE FUNCTION generate_uuid_accounts();
        ");

        // PL/pgSQL trigger function for account number generation
        DB::unprepared("
            CREATE SEQUENCE IF NOT EXISTS accounts_number_seq START 1;
        ");

        DB::unprepared("
            CREATE OR REPLACE FUNCTION generate_account_number()
            RETURNS TRIGGER AS \$\$
            DECLARE
                next_number INTEGER;
            BEGIN
                IF NEW.account_number IS NULL THEN
                    -- Get next available number
                    SELECT COALESCE(
                        MAX(CAST(SUBSTRING(account_number FROM 3) AS INTEGER)),
                        0
                    ) + 1
                    INTO next_number
                    FROM accounts
                    WHERE account_number ~ '^QS[0-9]+$';

                    -- Format as QS00000001
                    NEW.account_number := 'QS' || LPAD(next_number::TEXT, 8, '0');
                END IF;
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        DB::unprepared("
            CREATE TRIGGER before_insert_accounts_number
            BEFORE INSERT ON accounts
            FOR EACH ROW
            EXECUTE FUNCTION generate_account_number();
        ");

        // ENABLE ROW LEVEL SECURITY
        DB::unprepared("ALTER TABLE accounts ENABLE ROW LEVEL SECURITY");

        // RLS Policy: Accounts are isolated by their own ID (tenant root)
        // This is defense-in-depth - app-level filtering is primary control
        DB::unprepared("
            CREATE POLICY accounts_isolation ON accounts
            FOR ALL
            USING (
                id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
                OR current_setting('app.current_tenant_id', true) IS NULL
                OR current_user IN ('svc_red', 'ops_admin')
            )
            WITH CHECK (
                id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
                OR current_setting('app.current_tenant_id', true) IS NULL
                OR current_user IN ('svc_red', 'ops_admin')
            );
        ");

        // System account (00000000-0000-0000-0000-000000000001) bypass
        DB::unprepared("
            CREATE POLICY accounts_system_bypass ON accounts
            FOR ALL
            TO PUBLIC
            USING (id = '00000000-0000-0000-0000-000000000001'::uuid);
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS before_insert_accounts_uuid ON accounts");
        DB::unprepared("DROP TRIGGER IF EXISTS before_insert_accounts_number ON accounts");
        DB::unprepared("DROP FUNCTION IF EXISTS generate_uuid_accounts()");
        DB::unprepared("DROP FUNCTION IF EXISTS generate_account_number()");
        DB::unprepared("DROP SEQUENCE IF EXISTS accounts_number_seq");

        Schema::dropIfExists('accounts');

        DB::statement("DROP TYPE IF EXISTS account_status CASCADE");
        DB::statement("DROP TYPE IF EXISTS account_type CASCADE");
    }
};
