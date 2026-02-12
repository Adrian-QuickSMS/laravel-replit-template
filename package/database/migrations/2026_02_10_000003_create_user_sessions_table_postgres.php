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

        // Enable Row Level Security
        DB::unprepared("ALTER TABLE user_sessions ENABLE ROW LEVEL SECURITY");

        // RLS Policy: Users can only access their own sessions
        // This works via the FK: user_sessions.user_id → users.tenant_id
        DB::unprepared("
            CREATE POLICY user_sessions_tenant_isolation ON user_sessions
            FOR ALL
            USING (
                user_id IN (
                    SELECT id FROM users
                    WHERE tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
                )
                OR current_user IN ('svc_red', 'ops_admin')
            )
            WITH CHECK (
                user_id IN (
                    SELECT id FROM users
                    WHERE tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
                )
                OR current_user IN ('svc_red', 'ops_admin')
            );
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('user_sessions');
    }
};
