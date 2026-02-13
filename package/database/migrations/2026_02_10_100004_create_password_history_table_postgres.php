<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * RED SIDE: Password History (PostgreSQL Version)
     *
     * DATA CLASSIFICATION: Restricted - Security Control
     * SIDE: RED (password hashes are security-sensitive)
     * TENANT ISOLATION: Scoped via user_id → users.tenant_id
     *
     * POSTGRESQL ENHANCEMENTS:
     * - Native UUID type for user_id
     * - NO Row Level Security (RLS) - RED side, access controlled by grants
     *
     * SECURITY NOTES:
     * - Prevents password reuse (last 12 passwords)
     * - Password hashes never exposed anywhere
     * - Portal roles: NO ACCESS
     * - Only accessed by authentication service
     * - Retention: 2 years or last 12 passwords (whichever is longer)
     */
    public function up(): void
    {
        Schema::create('password_history', function (Blueprint $table) {
            // Auto-incrementing ID
            $table->id();

            // User reference - native UUID
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Password hash (Argon2id)
            $table->string('password_hash');

            // When was this password set
            $table->timestamp('set_at');

            // Indexes
            $table->index('user_id');
            $table->index(['user_id', 'set_at']);
        });

        // NO Row Level Security on RED side password history
        // Access control is enforced via database grants (see 01_create_roles_and_grants.sql)
        // This table should ONLY be accessible to internal authentication services
    }

    public function down(): void
    {
        Schema::dropIfExists('password_history');
    }
};
