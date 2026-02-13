<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * RED SIDE: Authentication Audit Log (PostgreSQL Version)
     *
     * DATA CLASSIFICATION: Restricted - Security Audit Trail
     * SIDE: RED (never accessible to customer portal)
     * TENANT ISOLATION: Records events for both tenants and admins
     *
     * POSTGRESQL ENHANCEMENTS:
     * - Native UUID type for actor_id and tenant_id
     * - PostgreSQL ENUM types for actor_type, event_type, result
     * - JSONB for metadata (queryable JSON)
     * - INET type for IP addresses
     * - NO Row Level Security (RLS) - RED side, access controlled by grants
     * - Immutability enforced by grants (INSERT only, no UPDATE/DELETE)
     *
     * SECURITY NOTES:
     * - Immutable log (no UPDATE or DELETE via grants)
     * - Records ALL authentication attempts (success and failure)
     * - Could reveal enumeration attacks - must be RED
     * - Retention: 2 years for compliance
     * - Portal roles: NO ACCESS
     * - ops_admin: SELECT only (read-only audit review)
     *
     * EVENTS LOGGED:
     * - login_success, login_failed, logout
     * - password_changed, password_reset_requested, password_reset_completed
     * - mfa_enabled, mfa_disabled, mfa_challenge_failed
     * - api_token_created, api_token_revoked
     * - account_locked, account_unlocked
     * - session_expired, session_terminated
     */
    public function up(): void
    {
        // Create ENUM types
        DB::statement("
            DO $$ BEGIN
                CREATE TYPE actor_type AS ENUM ('customer_user', 'admin_user', 'api_token', 'system');
            EXCEPTION
                WHEN duplicate_object THEN null;
            END $$;
        ");

        DB::statement("
            DO $$ BEGIN
                CREATE TYPE auth_event_type AS ENUM (
                    'login_success',
                    'login_failed',
                    'logout',
                    'password_changed',
                    'password_reset_requested',
                    'password_reset_completed',
                    'mfa_enabled',
                    'mfa_disabled',
                    'mfa_challenge_failed',
                    'api_token_created',
                    'api_token_revoked',
                    'account_locked',
                    'account_unlocked',
                    'session_expired',
                    'session_terminated',
                    'email_verified',
                    'signup_completed'
                );
            EXCEPTION
                WHEN duplicate_object THEN null;
            END $$;
        ");

        DB::statement("
            DO $$ BEGIN
                CREATE TYPE auth_result AS ENUM ('success', 'failure', 'suspicious');
            EXCEPTION
                WHEN duplicate_object THEN null;
            END $$;
        ");

        Schema::create('auth_audit_log', function (Blueprint $table) {
            // Auto-incrementing ID
            $table->id();

            // Who (user or admin) - native UUIDs
            // actor_type will be added as PostgreSQL ENUM via raw SQL below
            $table->uuid('actor_id')->nullable()->comment('user.id or admin_user.id');
            $table->string('actor_email')->nullable();

            // For customer users - record tenant for isolation queries
            $table->uuid('tenant_id')->nullable()->comment('NULL for admin events');

            // What happened - event_type will be added as PostgreSQL ENUM via raw SQL below

            // Context
            // ip_address will be added as INET type via raw SQL below
            $table->text('user_agent')->nullable();
            // metadata will be added as JSONB via raw SQL below

            // Result - result will be added as PostgreSQL ENUM via raw SQL below
            $table->text('failure_reason')->nullable();

            // When
            $table->timestamp('created_at');
        });

        // Add ENUM columns via raw SQL
        DB::statement("ALTER TABLE auth_audit_log ADD COLUMN actor_type actor_type DEFAULT 'customer_user' NOT NULL");
        DB::statement("ALTER TABLE auth_audit_log ADD COLUMN event_type auth_event_type NOT NULL");
        DB::statement("ALTER TABLE auth_audit_log ADD COLUMN result auth_result DEFAULT 'success' NOT NULL");

        // Add INET column for IP address
        DB::statement("ALTER TABLE auth_audit_log ADD COLUMN ip_address INET NOT NULL");

        // Add JSONB column for metadata
        DB::statement("ALTER TABLE auth_audit_log ADD COLUMN metadata JSONB");
        DB::statement("COMMENT ON COLUMN auth_audit_log.metadata IS 'Additional context as queryable JSON'");

        // Create indexes for audit queries
        DB::statement("CREATE INDEX idx_auth_audit_actor_type ON auth_audit_log (actor_type)");
        DB::statement("CREATE INDEX idx_auth_audit_actor_id ON auth_audit_log (actor_type, actor_id)");
        DB::statement("CREATE INDEX idx_auth_audit_tenant_id ON auth_audit_log (tenant_id)");
        DB::statement("CREATE INDEX idx_auth_audit_event_type ON auth_audit_log (event_type)");
        DB::statement("CREATE INDEX idx_auth_audit_result ON auth_audit_log (result)");
        DB::statement("CREATE INDEX idx_auth_audit_ip ON auth_audit_log (ip_address)");
        DB::statement("CREATE INDEX idx_auth_audit_created_at ON auth_audit_log (created_at)");
        DB::statement("CREATE INDEX idx_auth_audit_tenant_time ON auth_audit_log (tenant_id, created_at)");
        DB::statement("CREATE INDEX idx_auth_audit_actor_event_time ON auth_audit_log (actor_id, event_type, created_at)");

        // NO Row Level Security on RED side audit log
        // Access control is enforced via database grants (see 01_create_roles_and_grants.sql)
        // Immutability is enforced by revoking UPDATE/DELETE from all roles except ops_admin
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_audit_log');

        // Drop ENUM types
        DB::statement("DROP TYPE IF EXISTS actor_type CASCADE");
        DB::statement("DROP TYPE IF EXISTS auth_event_type CASCADE");
        DB::statement("DROP TYPE IF EXISTS auth_result CASCADE");
    }
};
