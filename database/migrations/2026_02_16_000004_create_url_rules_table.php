<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * URL Rules - URL/domain enforcement for phishing & scam prevention
 *
 * Governs URLs embedded in SMS messages. Key attack vector for phishing.
 * Supports exact domain, wildcard, and regex matching.
 *
 * DATA CLASSIFICATION: Internal - Enforcement Configuration
 * SIDE: RED (admin-only configuration)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('url_rules', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name', 255)->comment('Human-readable rule name');
            $table->text('pattern')->comment('Domain/URL pattern to match');
            $table->string('match_type', 20)->default('exact_domain')
                ->comment('exact_domain, wildcard, regex');
            $table->string('action', 20)->default('block')
                ->comment('block = silently drop, quarantine = send to admin review');
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0)->comment('Lower = higher priority');
            $table->text('description')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
            $table->index('action');
            $table->index(['is_active', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('url_rules');
    }
};
