<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * GREEN SIDE: Email Verification Tokens
     *
     * DATA CLASSIFICATION: Internal - Email Verification
     * SIDE: GREEN (used by customer email verification flow)
     * TENANT ISOLATION: Scoped via user_id → users.tenant_id
     *
     * SECURITY NOTES:
     * - Tokens expire after 24 hours
     * - One-time use only (deleted after verification)
     * - Can be regenerated (invalidates previous)
     */
    public function up(): void
    {
        Schema::create('email_verification_tokens', function (Blueprint $table) {
            $table->id();

            // User reference
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
    }

    public function down(): void
    {
        Schema::dropIfExists('email_verification_tokens');
    }
};
