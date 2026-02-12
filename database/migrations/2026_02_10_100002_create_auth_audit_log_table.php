<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * RED SIDE: Authentication Audit Log
     *
     * DATA CLASSIFICATION: Restricted - Security Audit Trail
     * SIDE: RED (never accessible to customer portal)
     * TENANT ISOLATION: Records events for both tenants and admins
     *
     * SECURITY NOTES:
     * - Immutable log (no UPDATE or DELETE)
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
        Schema::create('auth_audit_log', function (Blueprint $table) {
            $table->id();

            // Who (user or admin)
            $table->enum('actor_type', ['customer_user', 'admin_user', 'api_token', 'system'])->default('customer_user');
            $table->binary('actor_id', 16)->nullable()->comment('user.id or admin_user.id');
            $table->string('actor_email')->nullable();

            // For customer users - record tenant for isolation queries
            $table->binary('tenant_id', 16)->nullable()->comment('NULL for admin events');

            // What happened
            $table->enum('event_type', [
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
            ]);

            // Context
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->text('metadata')->nullable()->comment('JSON additional context');

            // Result
            $table->enum('result', ['success', 'failure', 'suspicious'])->default('success');
            $table->text('failure_reason')->nullable();

            // When
            $table->timestamp('created_at');

            // Indexes for audit queries
            $table->index('actor_type');
            $table->index(['actor_type', 'actor_id']);
            $table->index('tenant_id');
            $table->index('event_type');
            $table->index('result');
            $table->index('ip_address');
            $table->index('created_at');
            $table->index(['tenant_id', 'created_at']);
            $table->index(['actor_id', 'event_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_audit_log');
    }
};
