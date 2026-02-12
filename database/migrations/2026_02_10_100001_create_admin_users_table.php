<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * RED SIDE: Admin Users (QuickSMS Staff)
     *
     * DATA CLASSIFICATION: Restricted - Internal Staff Only
     * SIDE: RED (never accessible to customer portal)
     * TENANT ISOLATION: No tenant_id - these are platform administrators
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
        Schema::create('admin_users', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));

            // Authentication
            $table->string('email')->unique();
            $table->string('password'); // bcrypt hash
            $table->rememberToken();

            // Personal details
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone')->nullable();

            // Admin role and permissions
            $table->enum('role', ['super_admin', 'admin', 'support', 'finance', 'readonly'])->default('readonly');
            $table->text('permissions')->nullable()->comment('JSON array of granular permissions');

            // Account status
            $table->enum('status', ['active', 'suspended', 'locked'])->default('active');

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

            // IP whitelist (mandatory for admin access)
            $table->text('ip_whitelist')->nullable()->comment('JSON array of allowed IPs/CIDRs');

            // Session tracking
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();

            // Audit
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('email');
            $table->index('status');
            $table->index('role');
        });

        // MFA enforcement trigger (prevent disabling MFA without approval)
        DB::unprepared("
            CREATE OR REPLACE FUNCTION enforce_admin_mfa() RETURNS TRIGGER AS $$
            BEGIN
                IF OLD.mfa_enabled = TRUE AND NEW.mfa_enabled = FALSE THEN
                    RAISE EXCEPTION 'MFA cannot be disabled for admin users without security approval';
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER before_update_admin_users_mfa
            BEFORE UPDATE ON admin_users
            FOR EACH ROW
            EXECUTE FUNCTION enforce_admin_mfa();
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS before_update_admin_users_mfa ON admin_users");
        DB::unprepared("DROP FUNCTION IF EXISTS enforce_admin_mfa()");
        Schema::dropIfExists('admin_users');
    }
};
