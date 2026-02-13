<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * GREEN SIDE: API Tokens (PostgreSQL Version)
     *
     * DATA CLASSIFICATION: Restricted - API Authentication
     * SIDE: GREEN (customer can manage own API tokens)
     * TENANT ISOLATION: Direct tenant_id + RLS policies
     *
     * POSTGRESQL ENHANCEMENTS:
     * - Native UUID type (not BINARY(16))
     * - JSONB for scopes, ip_whitelist, webhook_urls (queryable)
     * - INET type for IP addresses
     * - PostgreSQL ENUM types for status and access_level
     * - Row Level Security (RLS) for tenant isolation
     * - PL/pgSQL trigger functions
     *
     * SECURITY NOTES:
     * - Plain token shown ONCE on creation, never again
     * - Only hash stored in database
     * - Portal users access via api_tokens_view (shows hash only, never plain token)
     * - Supports token rotation and expiry
     * - IP whitelist enforcement
     * - RLS ensures users only see their own tenant's tokens
     */
    public function up(): void
    {
        // Create ENUM types
        DB::statement("
            DO $$ BEGIN
                CREATE TYPE api_token_status AS ENUM ('active', 'suspended', 'revoked');
            EXCEPTION
                WHEN duplicate_object THEN null;
            END $$;
        ");

        DB::statement("
            DO $$ BEGIN
                CREATE TYPE api_token_access_level AS ENUM ('readonly', 'write', 'admin');
            EXCEPTION
                WHEN duplicate_object THEN null;
            END $$;
        ");

        Schema::create('api_tokens', function (Blueprint $table) {
            // Primary identifier - native UUID
            $table->uuid('id')->primary();

            // MANDATORY tenant isolation
            $table->uuid('tenant_id')->comment('FK to accounts.id - MANDATORY');
            $table->foreign('tenant_id')->references('id')->on('accounts')->onDelete('cascade');

            // Created by user (optional - can be system-generated)
            $table->uuid('created_by_user_id')->nullable();
            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('set null');

            // Token identification
            $table->string('name')->comment('Human-readable token name');
            $table->string('token_hash', 64)->unique()->comment('SHA-256 hash of API token');
            $table->string('token_prefix', 8)->comment('First 8 chars for identification');

            // Token permissions - JSONB for queryable JSON
            $table->jsonb('scopes')->nullable()->comment('Array of allowed scopes');
            // access_level will be added as PostgreSQL ENUM via raw SQL below

            // Security constraints - JSONB for queryable arrays
            $table->jsonb('ip_whitelist')->nullable()->comment('Array of allowed IPs/CIDRs');
            $table->jsonb('webhook_urls')->nullable()->comment('Array of allowed callback URLs');

            // Token lifecycle - status will be added as PostgreSQL ENUM via raw SQL below
            $table->timestamp('last_used_at')->nullable();
            // last_used_ip will be added as INET type via raw SQL below
            $table->timestamp('expires_at')->nullable();

            // Audit
            $table->string('revoked_by')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->text('revocation_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('tenant_id');
            $table->index('token_hash');
            $table->index('token_prefix');
            $table->unique(['tenant_id', 'name']); // Token name unique per tenant
        });

        // Add ENUM columns via raw SQL (Blueprint doesn't support PostgreSQL enums well)
        DB::statement("ALTER TABLE api_tokens ADD COLUMN access_level api_token_access_level DEFAULT 'readonly' NOT NULL");
        DB::statement("ALTER TABLE api_tokens ADD COLUMN status api_token_status DEFAULT 'active' NOT NULL");

        // Add INET column for IP address
        DB::statement("ALTER TABLE api_tokens ADD COLUMN last_used_ip INET");

        // Create composite index on tenant_id + status
        DB::statement("CREATE INDEX idx_api_tokens_tenant_status ON api_tokens (tenant_id, status)");

        // UUID generation trigger function
        DB::unprepared("
            CREATE OR REPLACE FUNCTION generate_uuid_api_tokens()
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
            CREATE TRIGGER before_insert_api_tokens_uuid
            BEFORE INSERT ON api_tokens
            FOR EACH ROW
            EXECUTE FUNCTION generate_uuid_api_tokens();
        ");

        // Tenant validation trigger function
        DB::unprepared("
            CREATE OR REPLACE FUNCTION validate_api_tokens_tenant_id()
            RETURNS TRIGGER AS $$
            BEGIN
                IF NEW.tenant_id IS NULL THEN
                    RAISE EXCEPTION 'tenant_id is mandatory for all API tokens';
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        ");

        DB::unprepared("
            CREATE TRIGGER before_insert_api_tokens_tenant_validation
            BEFORE INSERT ON api_tokens
            FOR EACH ROW
            EXECUTE FUNCTION validate_api_tokens_tenant_id();
        ");

        // Enable Row Level Security
        DB::unprepared("ALTER TABLE api_tokens ENABLE ROW LEVEL SECURITY");

        // RLS Policy: Users can only access tokens for their tenant
        DB::unprepared("
            CREATE POLICY api_tokens_tenant_isolation ON api_tokens
            FOR ALL
            USING (
                tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
                OR current_user IN ('svc_red', 'ops_admin')
            )
            WITH CHECK (
                tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
                OR current_user IN ('svc_red', 'ops_admin')
            );
        ");
    }

    public function down(): void
    {
        // Drop triggers
        DB::unprepared("DROP TRIGGER IF EXISTS before_insert_api_tokens_uuid ON api_tokens");
        DB::unprepared("DROP TRIGGER IF EXISTS before_insert_api_tokens_tenant_validation ON api_tokens");

        // Drop functions
        DB::unprepared("DROP FUNCTION IF EXISTS generate_uuid_api_tokens()");
        DB::unprepared("DROP FUNCTION IF EXISTS validate_api_tokens_tenant_id()");

        // Drop table
        Schema::dropIfExists('api_tokens');

        // Drop ENUM types
        DB::statement("DROP TYPE IF EXISTS api_token_status CASCADE");
        DB::statement("DROP TYPE IF EXISTS api_token_access_level CASCADE");
    }
};
