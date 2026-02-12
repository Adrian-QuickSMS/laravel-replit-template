<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * RED SIDE: Password History
     *
     * DATA CLASSIFICATION: Restricted - Security Control
     * SIDE: RED (password hashes are security-sensitive)
     * TENANT ISOLATION: Scoped via user_id → users.tenant_id
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
            $table->id();

            // User reference
            $table->binary('user_id', 16);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Password hash (bcrypt)
            $table->string('password_hash');

            // When was this password set
            $table->timestamp('set_at');

            // Indexes
            $table->index('user_id');
            $table->index(['user_id', 'set_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_history');
    }
};
