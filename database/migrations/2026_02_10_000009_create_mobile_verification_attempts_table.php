<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * RED SIDE: Mobile Verification Attempts (Rate Limiting)
     *
     * DATA CLASSIFICATION: Internal - Anti-Abuse
     * SIDE: RED (internal rate limiting only)
     * TENANT ISOLATION: None (tracks by mobile number for fraud detection)
     *
     * SECURITY NOTES:
     * - Tracks SMS verification code send attempts
     * - Rate limit: Max 3 codes per phone number per hour
     * - Used to prevent SMS flooding/abuse
     * - Portal roles: NO ACCESS
     * - Auto-cleanup: Records older than 24 hours can be purged
     */
    public function up(): void
    {
        Schema::create('mobile_verification_attempts', function (Blueprint $table) {
            $table->id();

            // Mobile number (normalized format: 447XXXXXXXXX)
            $table->string('mobile_number', 12);

            // When attempt was made
            $table->timestamp('attempted_at');

            // IP address (for fraud detection)
            $table->string('ip_address', 45);

            // Optional user/account reference
            $table->uuid('user_id')->nullable();
            $table->uuid('account_id')->nullable();

            // Result
            $table->enum('result', ['sent', 'rate_limited', 'failed'])->default('sent');
            $table->string('failure_reason')->nullable();

            // Indexes for rate limit queries
            $table->index(['mobile_number', 'attempted_at']);
            $table->index('ip_address');
            $table->index('attempted_at'); // For cleanup
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_verification_attempts');
    }
};
