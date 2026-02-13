<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * GREEN SIDE: User Sessions (PostgreSQL Version)
     *
     * DATA CLASSIFICATION: Confidential - Session Management
     * SIDE: GREEN (customer can see own active sessions only)
     * TENANT ISOLATION: Scoped via user_id → users.tenant_id (FK chain)
     *
     * POSTGRESQL ENHANCEMENTS:
     * - Native UUID type for user_id
     * - JSONB for abilities (queryable JSON)
     * - INET type for IP addresses
     * - Row Level Security (RLS) via FK to users table
     *
     * SECURITY NOTES:
     * - Portal users access via user_sessions_view
     * - Token hash stored, never plain token
     * - Session timeout enforced at application and database level
     * - RLS policies automatically scope to tenant via users FK
     */
    public function up(): void
    {
        Schema::create('user_sessions', function (Blueprint $table) {
            // Auto-incrementing ID (standard for sessions)
            $table->id();

            // User reference (tenant isolation via FK) - native UUID
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Session token (hashed)
            $table->string('token', 64)->unique()->comment('SHA-256 hash of bearer token');
            // abilities will be added as JSONB via raw SQL below

            // Session metadata
            $table->string('name')->nullable()->comment('Device/app name');
            // ip_address will be added as INET type via raw SQL below
            $table->text('user_agent')->nullable();

            // Session lifecycle
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index('user_id');
            $table->index(['user_id', 'expires_at']);
            $table->index('token');
        });

        // Add JSONB column for abilities
        DB::statement("ALTER TABLE user_sessions ADD COLUMN abilities JSONB");
        DB::statement("COMMENT ON COLUMN user_sessions.abilities IS 'Array of token permissions'");

        // Add INET column for IP address
        DB::statement("ALTER TABLE user_sessions ADD COLUMN ip_address INET NOT NULL");

        // Add tenant_id directly for efficient RLS (avoids subquery)
        DB::statement("ALTER TABLE user_sessions ADD COLUMN tenant_id UUID REFERENCES accounts(id)");
        DB::statement("CREATE INDEX idx_user_sessions_tenant ON user_sessions (tenant_id)");

        // Trigger to auto-populate tenant_id from user record
        DB::unprepared("
            CREATE OR REPLACE FUNCTION set_user_sessions_tenant_id()
            RETURNS TRIGGER AS \$\$
            BEGIN
                IF NEW.tenant_id IS NULL THEN
                    SELECT tenant_id INTO NEW.tenant_id FROM users WHERE id = NEW.user_id;
                END IF;
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;
        ");
        DB::unprepared("
            CREATE TRIGGER before_insert_user_sessions_tenant
            BEFORE INSERT ON user_sessions
            FOR EACH ROW
            EXECUTE FUNCTION set_user_sessions_tenant_id();
        ");

        // Enable Row Level Security (including for table owner)
        DB::unprepared("ALTER TABLE user_sessions ENABLE ROW LEVEL SECURITY");
        DB::unprepared("ALTER TABLE user_sessions FORCE ROW LEVEL SECURITY");

        // RLS Policy: Tenant isolation via direct tenant_id (no subquery)
        DB::unprepared("
            CREATE POLICY user_sessions_tenant_isolation ON user_sessions
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
            CREATE POLICY user_sessions_service_access ON user_sessions
            FOR ALL
            TO svc_red, ops_admin
            USING (true)
            WITH CHECK (true);
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('user_sessions');
    }
};
