<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * GREEN SIDE: Password Reset Tokens
     *
     * DATA CLASSIFICATION: Restricted - Password Recovery
     * SIDE: GREEN (used by customer password reset flow)
     * TENANT ISOLATION: Scoped via email (which is unique per tenant in users table)
     *
     * SECURITY NOTES:
     * - Tokens expire after 60 minutes
     * - One-time use only (deleted after use)
     * - Rate limited at application layer
     * - Token never exposed in logs
     */
    public function up(): void
    {
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token', 64)->comment('SHA-256 hash of reset token');
            $table->timestamp('created_at');

            $table->index('token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
    }
};
