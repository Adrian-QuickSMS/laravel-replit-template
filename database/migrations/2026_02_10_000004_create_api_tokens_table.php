<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * GREEN SIDE: API Tokens
     *
     * DATA CLASSIFICATION: Restricted - API Authentication
     * SIDE: GREEN (customer can manage own API tokens)
     * TENANT ISOLATION: Direct tenant_id + unique constraints scoped by tenant
     *
     * SECURITY NOTES:
     * - Plain token shown ONCE on creation, never again
     * - Only hash stored in database
     * - Portal users access via api_tokens_view (shows hash only, never plain token)
     * - Supports token rotation and expiry
     * - IP whitelist enforcement
     */
    public function up(): void
    {
        Schema::create('api_tokens', function (Blueprint $table) {
            $table->binary('id', 16)->primary();

            // MANDATORY tenant isolation
            $table->binary('tenant_id', 16)->comment('FK to accounts.id');
            $table->foreign('tenant_id')->references('id')->on('accounts')->onDelete('cascade');

            // Created by user (optional - can be system-generated)
            $table->binary('created_by_user_id', 16)->nullable();
            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('set null');

            // Token identification
            $table->string('name')->comment('Human-readable token name');
            $table->string('token_hash', 64)->unique()->comment('SHA-256 hash of API token');
            $table->string('token_prefix', 8)->comment('First 8 chars for identification');

            // Token permissions
            $table->text('scopes')->nullable()->comment('JSON array of allowed scopes');
            $table->enum('access_level', ['readonly', 'write', 'admin'])->default('readonly');

            // Security constraints
            $table->text('ip_whitelist')->nullable()->comment('JSON array of allowed IPs/CIDRs');
            $table->text('webhook_urls')->nullable()->comment('JSON array of allowed callback URLs');

            // Token lifecycle
            $table->enum('status', ['active', 'suspended', 'revoked'])->default('active');
            $table->timestamp('last_used_at')->nullable();
            $table->string('last_used_ip', 45)->nullable();
            $table->timestamp('expires_at')->nullable();

            // Audit
            $table->string('revoked_by')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->text('revocation_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('tenant_id');
            $table->index(['tenant_id', 'status']);
            $table->index('token_hash');
            $table->index('token_prefix');
            $table->unique(['tenant_id', 'name']); // Token name unique per tenant
        });

        // UUID generation trigger
        DB::unprepared("
            CREATE TRIGGER before_insert_api_tokens_uuid
            BEFORE INSERT ON api_tokens
            FOR EACH ROW
            BEGIN
                IF NEW.id IS NULL OR NEW.id = '' THEN
                    SET NEW.id = UNHEX(REPLACE(UUID(), '-', ''));
                END IF;
            END
        ");

        // Tenant validation trigger
        DB::unprepared("
            CREATE TRIGGER before_insert_api_tokens_tenant_validation
            BEFORE INSERT ON api_tokens
            FOR EACH ROW
            BEGIN
                IF NEW.tenant_id IS NULL OR NEW.tenant_id = '' THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'tenant_id is mandatory for all API tokens';
                END IF;
            END
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS before_insert_api_tokens_uuid");
        DB::unprepared("DROP TRIGGER IF EXISTS before_insert_api_tokens_tenant_validation");
        Schema::dropIfExists('api_tokens');
    }
};
