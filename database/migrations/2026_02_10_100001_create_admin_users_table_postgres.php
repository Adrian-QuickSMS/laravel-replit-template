<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * RED SIDE: Admin Users (PostgreSQL Version)
     *
     * DATA CLASSIFICATION: Restricted - Internal Staff Only
     * SIDE: RED (never accessible to customer portal)
     * TENANT ISOLATION: No tenant_id - these are platform administrators
     *
     * POSTGRESQL ENHANCEMENTS:
     * - Native UUID type for id
     * - PostgreSQL ENUM types for role and status
     * - JSONB for permissions and ip_whitelist (queryable)
     * - INET type for IP addresses
     * - NO Row Level Security (RLS) - RED side, access controlled by grants
     * - PL/pgSQL triggers for UUID generation and MFA enforcement
     *
     * SECURITY NOTES:
     * - Completely separate from customer users table
     * - Portal roles have ZERO access to this table
     * - MFA mandatory for all admin users
     * - Password expiry enforced (90 days)
     * - IP whitelist enforced
     * - All logins logged to auth_audit_log
     *
     * ACCESS:
     * - ops_admin role: SELECT, INSERT, UPDATE only (no DELETE)
     * - Portal roles: NO ACCESS
     */
    public function up(): void
    {
        // Create ENUM types
        DB::statement("
            DO $$ BEGIN
                CREATE TYPE admin_role AS ENUM ('super_admin', 'admin', 'support', 'finance', 'readonly');
            EXCEPTION
                WHEN duplicate_object THEN null;
            END $$;
        ");

        DB::statement("
            DO $$ BEGIN
                CREATE TYPE admin_status AS ENUM ('active', 'suspended', 'locked');
            EXCEPTION
                WHEN duplicate_object THEN null;
            END $$;
        ");

        Schema::create('admin_users', function (Blueprint $table) {
            // Primary identifier - native UUID
            $table->uuid('id')->primary();

            // Authentication
            $table->string('email')->unique();
            $table->string('password')->comment('Argon2id hash');
            $table->rememberToken();

            // Personal details
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone')->nullable();

            // Admin role and permissions - ENUM and JSONB will be added via raw SQL below

            // Account status - ENUM will be added via raw SQL below

            // MANDATORY MFA for admins
            $table->boolean('mfa_enabled')->default(false);
            $table->string('mfa_secret')->nullable();
            $table->text('mfa_recovery_codes')->nullable();
            $table->timestamp('mfa_enabled_at')->nullable();

            // Password management (stricter than customer users)
            $table->timestamp('password_changed_at')->nullable();
            $table->boolean('force_password_change')->default(true)->comment('Force change on first login');
            $table->integer('failed_login_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();

            // IP whitelist (mandatory for admin access) - JSONB will be added via raw SQL below

            // Session tracking
            $table->timestamp('last_login_at')->nullable();
            // last_login_ip will be added as INET type via raw SQL below

            // Audit
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('email');
        });

        // Add ENUM columns via raw SQL
        DB::statement("ALTER TABLE admin_users ADD COLUMN role admin_role DEFAULT 'readonly' NOT NULL");
        DB::statement("ALTER TABLE admin_users ADD COLUMN status admin_status DEFAULT 'active' NOT NULL");

        // Add JSONB columns
        DB::statement("ALTER TABLE admin_users ADD COLUMN permissions JSONB");
        DB::statement("COMMENT ON COLUMN admin_users.permissions IS 'Array of granular permissions'");

        DB::statement("ALTER TABLE admin_users ADD COLUMN ip_whitelist JSONB");
        DB::statement("COMMENT ON COLUMN admin_users.ip_whitelist IS 'Array of allowed IPs/CIDRs'");

        // Add INET column for IP address
        DB::statement("ALTER TABLE admin_users ADD COLUMN last_login_ip INET");

        // Create indexes
        DB::statement("CREATE INDEX idx_admin_users_status ON admin_users (status)");
        DB::statement("CREATE INDEX idx_admin_users_role ON admin_users (role)");

        // UUID generation trigger function
        DB::unprepared("
            CREATE OR REPLACE FUNCTION generate_uuid_admin_users()
            RETURNS TRIGGER AS $$
            BEGIN
                IF NEW.id IS NULL THEN
                    NEW.id = gen_random_uuid();
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        ");

        DB::unprepared("
            CREATE TRIGGER before_insert_admin_users_uuid
            BEFORE INSERT ON admin_users
            FOR EACH ROW
            EXECUTE FUNCTION generate_uuid_admin_users();
        ");

        // MFA enforcement trigger (prevent disabling MFA without approval)
        DB::unprepared("
            CREATE OR REPLACE FUNCTION enforce_admin_mfa()
            RETURNS TRIGGER AS $$
            BEGIN
                IF OLD.mfa_enabled = TRUE AND NEW.mfa_enabled = FALSE THEN
                    RAISE EXCEPTION 'MFA cannot be disabled for admin users without security approval';
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        ");

        DB::unprepared("
            CREATE TRIGGER before_update_admin_users_mfa
            BEFORE UPDATE ON admin_users
            FOR EACH ROW
            EXECUTE FUNCTION enforce_admin_mfa();
        ");

        // NO Row Level Security on RED side admin table
        // Access control is enforced via database grants (see 01_create_roles_and_grants.sql)
    }

    public function down(): void
    {
        // Drop triggers
        DB::unprepared("DROP TRIGGER IF EXISTS before_insert_admin_users_uuid ON admin_users");
        DB::unprepared("DROP TRIGGER IF EXISTS before_update_admin_users_mfa ON admin_users");

        // Drop functions
        DB::unprepared("DROP FUNCTION IF EXISTS generate_uuid_admin_users()");
        DB::unprepared("DROP FUNCTION IF EXISTS enforce_admin_mfa()");

        // Drop table
        Schema::dropIfExists('admin_users');

        // Drop ENUM types
        DB::statement("DROP TYPE IF EXISTS admin_role CASCADE");
        DB::statement("DROP TYPE IF EXISTS admin_status CASCADE");
    }
};
