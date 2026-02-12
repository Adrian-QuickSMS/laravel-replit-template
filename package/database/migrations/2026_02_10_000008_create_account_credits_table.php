<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * GREEN SIDE: Account Credits (Promotional & Purchased)
     *
     * DATA CLASSIFICATION: Internal - Credit Tracking
     * SIDE: GREEN (customer can view own credits)
     * TENANT ISOLATION: References tenant via account_id
     *
     * SECURITY NOTES:
     * - Tracks all credit awards (signup, mobile verification, referrals, purchases)
     * - Credits expire when account transitions from trial to live
     * - Portal users can view via safe view
     * - Usage tracked for billing
     */
    public function up(): void
    {
        Schema::create('account_credits', function (Blueprint $table) {
            $table->id();

            // Account reference
            $table->binary('account_id', 16);
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');

            // Credit type
            $table->enum('type', [
                'signup_promo',         // Free credits for signing up
                'mobile_verification',  // 100 credits for mobile verify + marketing opt-in
                'referral',             // Referral bonuses
                'purchased',            // Paid credits
                'bonus',                // Admin-awarded bonus
                'compensation'          // Service credits for issues
            ]);

            // Credit amounts
            $table->integer('credits_awarded')->default(0)->comment('Initial amount awarded');
            $table->integer('credits_used')->default(0)->comment('Amount consumed');
            $table->integer('credits_remaining')->default(0)->comment('Available balance');

            // Metadata
            $table->string('reason')->nullable()->comment('Description of why credits awarded');
            $table->string('reference_id')->nullable()->comment('External reference (order ID, promo code)');

            // Expiry
            $table->timestamp('expires_at')->nullable()->comment('NULL = valid during trial');
            $table->timestamp('expired_at')->nullable()->comment('When credits actually expired');

            // Audit
            $table->string('awarded_by')->nullable()->comment('Admin user who awarded (for manual bonuses)');
            $table->timestamps();

            // Indexes
            $table->index('account_id');
            $table->index('type');
            $table->index(['account_id', 'type']);
            $table->index('expires_at');
            $table->index(['account_id', 'expires_at', 'credits_remaining']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_credits');
    }
};
