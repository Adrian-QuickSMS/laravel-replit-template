<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * GREEN SIDE: User Sessions (Laravel Sanctum tokens)
     *
     * DATA CLASSIFICATION: Confidential - Session Management
     * SIDE: GREEN (customer can see own active sessions only)
     * TENANT ISOLATION: Scoped via user_id → users.tenant_id
     *
     * SECURITY NOTES:
     * - Portal users access via user_sessions_view
     * - Token hash stored, never plain token
     * - Session timeout enforced at application and database level
     */
    public function up(): void
    {
        Schema::create('user_sessions', function (Blueprint $table) {
            $table->id();

            // User reference (tenant isolation via FK)
            $table->binary('user_id', 16);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Session token (hashed)
            $table->string('token', 64)->unique()->comment('SHA-256 hash of bearer token');
            $table->text('abilities')->nullable()->comment('JSON array of token permissions');

            // Session metadata
            $table->string('name')->nullable()->comment('Device/app name');
            $table->string('ip_address', 45);
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
    }

    public function down(): void
    {
        Schema::dropIfExists('user_sessions');
    }
};
