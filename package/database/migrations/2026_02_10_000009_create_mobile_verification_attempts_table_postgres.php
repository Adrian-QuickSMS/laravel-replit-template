<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * RED SIDE: Mobile Verification Attempts (PostgreSQL Version)
     *
     * DATA CLASSIFICATION: Internal - Anti-Abuse
     * SIDE: RED (internal rate limiting only)
     * TENANT ISOLATION: None (tracks by mobile number for fraud detection)
     *
     * POSTGRESQL ENHANCEMENTS:
     * - Native UUID type for user_id and account_id
     * - PostgreSQL ENUM type for result
     * - INET type for IP addresses
     * - NO Row Level Security (RLS) - RED side, cross-tenant fraud detection
     *
     * SECURITY NOTES:
     * - Tracks SMS verification code send attempts
     * - Rate limit: Max 4 codes per phone number per 24 hours
     * - Used to prevent SMS flooding/abuse
     * - Portal roles: NO ACCESS
     * - Auto-cleanup: Records older than 24 hours can be purged
     */
    public function up(): void
    {
        // Create ENUM type for result
        DB::statement("
            DO $$ BEGIN
                CREATE TYPE verification_result AS ENUM ('sent', 'rate_limited', 'failed');
            EXCEPTION
                WHEN duplicate_object THEN null;
            END $$;
        ");

        Schema::create('mobile_verification_attempts', function (Blueprint $table) {
            // Auto-incrementing ID
            $table->id();

            // Mobile number (normalized format: 447XXXXXXXXX)
            $table->string('mobile_number', 12);

            // When attempt was made
            $table->timestamp('attempted_at');

            // IP address (for fraud detection) - will be added as INET type via raw SQL below

            // Optional user/account reference - native UUIDs
            $table->uuid('user_id')->nullable();
            $table->uuid('account_id')->nullable();

            // Result - will be added as PostgreSQL ENUM via raw SQL below
            $table->string('failure_reason')->nullable();
        });

        // Add ENUM column via raw SQL
        DB::statement("ALTER TABLE mobile_verification_attempts ADD COLUMN result verification_result DEFAULT 'sent' NOT NULL");

        // Add INET column for IP address
        DB::statement("ALTER TABLE mobile_verification_attempts ADD COLUMN ip_address INET NOT NULL");

        // Create indexes for rate limit queries
        DB::statement("CREATE INDEX idx_mobile_verify_number_time ON mobile_verification_attempts (mobile_number, attempted_at)");
        DB::statement("CREATE INDEX idx_mobile_verify_ip ON mobile_verification_attempts (ip_address)");
        DB::statement("CREATE INDEX idx_mobile_verify_cleanup ON mobile_verification_attempts (attempted_at)");

        // NO Row Level Security on RED side rate limiting table
        // This table is used for cross-tenant fraud detection
        // Access control is enforced via database grants (see 01_create_roles_and_grants.sql)
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_verification_attempts');

        // Drop ENUM type
        DB::statement("DROP TYPE IF EXISTS verification_result CASCADE");
    }
};
