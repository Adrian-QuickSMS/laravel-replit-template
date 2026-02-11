<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * GREEN SIDE: Customer Users
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
     * - Composite unique constraints include tenant_id
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            // Primary identifier - UUID stored as BINARY(16)
            $table->binary('id', 16)->primary();

            // MANDATORY tenant isolation
            $table->binary('tenant_id', 16)->comment('FK to accounts.id - MANDATORY for all queries');
            $table->foreign('tenant_id')->references('id')->on('accounts')->onDelete('cascade');

            // User type (customer only on GREEN side)
            $table->enum('user_type', ['customer', 'api'])->default('customer');

            // Authentication credentials
            $table->string('email')->comment('Must be unique per tenant');
            $table->string('password'); // bcrypt hash - NEVER exposed via views
            $table->rememberToken();

            // User details (GREEN - visible to user)
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone')->nullable();

            // Account status
            $table->enum('status', ['pending_verification', 'active', 'suspended', 'locked'])->default('pending_verification');
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('phone_verified')->default(false);

            // Role within account
            $table->enum('role', ['owner', 'admin', 'user', 'readonly'])->default('user');

            // Security settings
            $table->boolean('mfa_enabled')->default(false);
            $table->string('mfa_secret')->nullable(); // Encrypted TOTP secret
            $table->text('mfa_recovery_codes')->nullable(); // JSON encrypted

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
            $table->string('last_login_ip', 45)->nullable();

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
        });

        // Add UUID generation trigger
        DB::unprepared("
            CREATE TRIGGER before_insert_users_uuid
            BEFORE INSERT ON users
            FOR EACH ROW
            BEGIN
                IF NEW.id IS NULL OR NEW.id = '' THEN
                    SET NEW.id = UNHEX(REPLACE(UUID(), '-', ''));
                END IF;
            END
        ");

        // Add validation trigger - tenant_id MUST be set
        DB::unprepared("
            CREATE TRIGGER before_insert_users_tenant_validation
            BEFORE INSERT ON users
            FOR EACH ROW
            BEGIN
                IF NEW.tenant_id IS NULL OR NEW.tenant_id = '' THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'tenant_id is mandatory for all users';
                END IF;
            END
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS before_insert_users_uuid");
        DB::unprepared("DROP TRIGGER IF EXISTS before_insert_users_tenant_validation");
        Schema::dropIfExists('users');
    }
};
