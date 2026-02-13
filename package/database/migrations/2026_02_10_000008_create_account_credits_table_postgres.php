<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * GREEN SIDE: Account Credits (PostgreSQL Version)
     *
     * DATA CLASSIFICATION: Internal - Credit Tracking
     * SIDE: GREEN (customer can view own credits)
     * TENANT ISOLATION: Direct tenant_id via account_id + RLS policies
     *
     * POSTGRESQL ENHANCEMENTS:
     * - Native UUID type for account_id
     * - PostgreSQL ENUM type for credit type
     * - Row Level Security (RLS) for tenant isolation
     *
     * SECURITY NOTES:
     * - Tracks all credit awards (signup, mobile verification, referrals, purchases)
     * - Credits expire when account transitions from trial to live
     * - Portal users can view via safe view
     * - Usage tracked for billing
     * - RLS ensures accounts only see their own credits
     */
    public function up(): void
    {
        // Create ENUM type for credit types
        DB::statement("
            DO $$ BEGIN
                CREATE TYPE credit_type AS ENUM (
                    'signup_promo',
                    'mobile_verification',
                    'referral',
                    'purchased',
                    'bonus',
                    'compensation'
                );
            EXCEPTION
                WHEN duplicate_object THEN null;
            END $$;
        ");

        Schema::create('account_credits', function (Blueprint $table) {
            // Auto-incrementing ID
            $table->id();

            // Account reference - native UUID
            $table->uuid('account_id');
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');

            // Credit type - will be added as PostgreSQL ENUM via raw SQL below

            // Credit amounts
            $table->integer('credits_awarded')->default(0)->comment('Initial amount awarded');
            $table->integer('credits_used')->default(0)->comment('Amount consumed');
            $table->integer('credits_remaining')->default(0)->comment('Available balance');

            // Metadata
            $table->string('reason')->nullable()->comment('Description of why credits awarded');
            $table->string('reference_id')->nullable()->comment('External reference (order ID, promo code)');

            // Expiry
            $table->timestamp('expires_at')->nullable()->comment('NULL = valid during trial');
            $table->timestamp('expired_at')->nullable()->comment('When credits actually expired');

            // Audit
            $table->string('awarded_by')->nullable()->comment('Admin user who awarded (for manual bonuses)');
            $table->timestamps();

            // Indexes
            $table->index('account_id');
            $table->index('expires_at');
        });

        // Add ENUM column via raw SQL
        DB::statement("ALTER TABLE account_credits ADD COLUMN type credit_type NOT NULL");

        // Create composite indexes
        DB::statement("CREATE INDEX idx_account_credits_account_type ON account_credits (account_id, type)");
        DB::statement("CREATE INDEX idx_account_credits_balance ON account_credits (account_id, expires_at, credits_remaining)");

        // Enable Row Level Security (including for table owner)
        DB::unprepared("ALTER TABLE account_credits ENABLE ROW LEVEL SECURITY");
        DB::unprepared("ALTER TABLE account_credits FORCE ROW LEVEL SECURITY");

        // RLS Policy: Tenant isolation (fail-closed)
        DB::unprepared("
            CREATE POLICY account_credits_isolation ON account_credits
            FOR ALL
            USING (
                account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
            )
            WITH CHECK (
                account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
            );
        ");

        // Privileged roles bypass
        DB::unprepared("
            CREATE POLICY account_credits_service_access ON account_credits
            FOR ALL
            TO svc_red, ops_admin
            USING (true)
            WITH CHECK (true);
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('account_credits');

        // Drop ENUM type
        DB::statement("DROP TYPE IF EXISTS credit_type CASCADE");
    }
};
