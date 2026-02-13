<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * GREEN SIDE: Email Verification Tokens (PostgreSQL Version)
     *
     * DATA CLASSIFICATION: Internal - Email Verification
     * SIDE: GREEN (used by customer email verification flow)
     * TENANT ISOLATION: Scoped via user_id → users.tenant_id (FK chain)
     *
     * POSTGRESQL ENHANCEMENTS:
     * - Native UUID type for user_id
     * - Row Level Security (RLS) via FK to users table
     *
     * SECURITY NOTES:
     * - Tokens expire after 24 hours
     * - One-time use only (deleted after verification)
     * - Can be regenerated (invalidates previous)
     * - RLS automatically scopes to tenant via users FK
     */
    public function up(): void
    {
        Schema::create('email_verification_tokens', function (Blueprint $table) {
            // Auto-incrementing ID
            $table->id();

            // User reference - native UUID
            $table->uuid('user_id')->unique();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Token
            $table->string('token', 64)->unique()->comment('SHA-256 hash of verification token');
            $table->string('email')->comment('Email being verified');

            // Lifecycle
            $table->timestamp('expires_at');
            $table->timestamp('created_at');

            $table->index('token');
            $table->index(['user_id', 'expires_at']);
        });

        // Add tenant_id directly for efficient RLS (avoids subquery)
        DB::statement("ALTER TABLE email_verification_tokens ADD COLUMN tenant_id UUID REFERENCES accounts(id)");
        DB::statement("CREATE INDEX idx_email_verification_tokens_tenant ON email_verification_tokens (tenant_id)");

        // Trigger to auto-populate tenant_id from user record
        DB::unprepared("
            CREATE OR REPLACE FUNCTION set_email_verification_tokens_tenant_id()
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
            CREATE TRIGGER before_insert_email_verification_tenant
            BEFORE INSERT ON email_verification_tokens
            FOR EACH ROW
            EXECUTE FUNCTION set_email_verification_tokens_tenant_id();
        ");

        // Enable Row Level Security (including for table owner)
        DB::unprepared("ALTER TABLE email_verification_tokens ENABLE ROW LEVEL SECURITY");
        DB::unprepared("ALTER TABLE email_verification_tokens FORCE ROW LEVEL SECURITY");

        // RLS Policy: Tenant isolation via direct tenant_id (no subquery)
        DB::unprepared("
            CREATE POLICY email_verification_tenant_isolation ON email_verification_tokens
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
            CREATE POLICY email_verification_service_access ON email_verification_tokens
            FOR ALL
            TO svc_red, ops_admin
            USING (true)
            WITH CHECK (true);
        ");

        // Postgres superuser bypass (app connects as postgres)
        DB::unprepared("
            CREATE POLICY email_verification_tokens_postgres_bypass ON email_verification_tokens
            FOR ALL
            TO postgres
            USING (true)
            WITH CHECK (true);
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('email_verification_tokens');
    }
};
