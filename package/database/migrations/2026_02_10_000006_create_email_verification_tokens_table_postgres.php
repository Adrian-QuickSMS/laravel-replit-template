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

        // Enable Row Level Security
        DB::unprepared("ALTER TABLE email_verification_tokens ENABLE ROW LEVEL SECURITY");

        // RLS Policy: Users can only access their own verification tokens
        // This works via the FK: email_verification_tokens.user_id → users.tenant_id
        DB::unprepared("
            CREATE POLICY email_verification_tenant_isolation ON email_verification_tokens
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
        Schema::dropIfExists('email_verification_tokens');
    }
};
