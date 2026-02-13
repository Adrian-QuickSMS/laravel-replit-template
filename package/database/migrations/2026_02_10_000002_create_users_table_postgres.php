<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * POSTGRESQL VERSION - GREEN SIDE: Customer Users
     *
     * DATA CLASSIFICATION: Confidential - User Authentication
     * SIDE: GREEN (customer accessible via views for own data only)
     * TENANT ISOLATION: Every user belongs to exactly one account (tenant_id)
     *
     * SECURITY NOTES:
     * - Password hashes stored here but NEVER exposed via views
     * - Portal users can SELECT from user_profile_view only
     * - All writes via sp_update_user_profile stored procedure
     * - user_type='customer' only (admins in separate RED table)
     * - Row Level Security enforces tenant_id filtering
     *
     * CHANGES FROM MYSQL:
     * - BINARY(16) → native UUID type
     * - MySQL triggers → PL/pgSQL functions
     * - SIGNAL SQLSTATE → RAISE EXCEPTION
     * - Added RLS policies for tenant isolation
     */
    public function up(): void
    {
        // Create ENUM types
        DB::statement("CREATE TYPE user_type AS ENUM ('customer', 'api')");
        DB::statement("CREATE TYPE user_status AS ENUM ('pending_verification', 'active', 'suspended', 'locked')");
        DB::statement("CREATE TYPE user_role AS ENUM ('owner', 'admin', 'user', 'readonly')");

        Schema::create('users', function (Blueprint $table) {
            // Primary identifier - Native PostgreSQL UUID
            $table->uuid('id')->primary();

            // MANDATORY tenant isolation
            $table->uuid('tenant_id')->comment('FK to accounts.id - MANDATORY for all queries');
            $table->foreign('tenant_id')->references('id')->on('accounts')->onDelete('cascade');

            // User type (customer only on GREEN side)
            DB::statement("ALTER TABLE users ADD COLUMN user_type user_type DEFAULT 'customer'");

            // Authentication credentials
            $table->string('email')->comment('Must be unique per tenant');
            $table->string('password'); // Argon2id hash - NEVER exposed via views
            $table->rememberToken();

            // User details (GREEN - visible to user)
            $table->string('first_name');
            $table->string('last_name');
            $table->string('job_title')->nullable();
            $table->string('phone')->nullable();

            // Account status (uses ENUM type)
            DB::statement("ALTER TABLE users ADD COLUMN status user_status DEFAULT 'pending_verification'");
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('phone_verified')->default(false);

            // Role within account (uses ENUM type)
            DB::statement("ALTER TABLE users ADD COLUMN role user_role DEFAULT 'user'");

            // Security settings
            $table->boolean('mfa_enabled')->default(false);
            $table->string('mfa_secret')->nullable()->comment('Encrypted TOTP secret');
            $table->text('mfa_recovery_codes')->nullable()->comment('JSON encrypted');

            // Mobile verification (for MFA)
            $table->string('mobile_number', 12)->nullable()->comment('Stored as 447XXXXXXXXX');
            $table->timestamp('mobile_verified_at')->nullable();
            $table->string('mobile_verification_code', 64)->nullable()->comment('SHA-256 hash of 6-digit code');
            $table->timestamp('mobile_verification_expires_at')->nullable();

            // Password management
            $table->timestamp('password_changed_at')->nullable();
            $table->boolean('force_password_change')->default(false);
            $table->integer('failed_login_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();

            // External integration IDs
            $table->string('hubspot_contact_id')->nullable();
            $table->timestamp('last_hubspot_sync')->nullable();

            // Session tracking
            $table->timestamp('last_login_at')->nullable();
            $table->inet('last_login_ip')->nullable();

            // Audit fields
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance and isolation enforcement
            $table->index('tenant_id'); // CRITICAL - all queries must filter by this
            $table->unique(['tenant_id', 'email']); // Email unique per tenant, not globally
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'role']);
            $table->index('hubspot_contact_id');
            $table->index('mobile_number'); // For mobile verification lookup
        });

        // PL/pgSQL trigger function for UUID generation
        DB::unprepared("
            CREATE OR REPLACE FUNCTION generate_uuid_users()
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
            CREATE TRIGGER before_insert_users_uuid
            BEFORE INSERT ON users
            FOR EACH ROW
            EXECUTE FUNCTION generate_uuid_users();
        ");

        // PL/pgSQL trigger function for tenant_id validation
        DB::unprepared("
            CREATE OR REPLACE FUNCTION validate_users_tenant_id()
            RETURNS TRIGGER AS \$\$
            BEGIN
                IF NEW.tenant_id IS NULL THEN
                    RAISE EXCEPTION 'tenant_id is mandatory for all users';
                END IF;
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        DB::unprepared("
            CREATE TRIGGER before_insert_users_tenant_validation
            BEFORE INSERT ON users
            FOR EACH ROW
            EXECUTE FUNCTION validate_users_tenant_id();
        ");

        // ENABLE ROW LEVEL SECURITY (including for table owner)
        DB::unprepared("ALTER TABLE users ENABLE ROW LEVEL SECURITY");
        DB::unprepared("ALTER TABLE users FORCE ROW LEVEL SECURITY");

        // RLS Policy: Tenant isolation (fail-closed)
        DB::unprepared("
            CREATE POLICY users_tenant_isolation ON users
            FOR ALL
            USING (
                tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
            )
            WITH CHECK (
                tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
            );
        ");

        // Privileged roles bypass
        DB::unprepared("
            CREATE POLICY users_service_access ON users
            FOR ALL
            TO svc_red, ops_admin
            USING (true)
            WITH CHECK (true);
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS before_insert_users_uuid ON users");
        DB::unprepared("DROP TRIGGER IF EXISTS before_insert_users_tenant_validation ON users");
        DB::unprepared("DROP FUNCTION IF EXISTS generate_uuid_users()");
        DB::unprepared("DROP FUNCTION IF EXISTS validate_users_tenant_id()");

        Schema::dropIfExists('users');

        DB::statement("DROP TYPE IF EXISTS user_type CASCADE");
        DB::statement("DROP TYPE IF EXISTS user_status CASCADE");
        DB::statement("DROP TYPE IF EXISTS user_role CASCADE");
    }
};
