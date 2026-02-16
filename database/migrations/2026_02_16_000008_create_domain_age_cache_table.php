<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Domain Age Cache - cached WHOIS domain registration data
 *
 * Used by URL enforcement to detect freshly registered domains
 * (key phishing indicator). Caches WHOIS lookups to avoid repeated
 * external calls. Ready for WHOIS integration at launch.
 *
 * DATA CLASSIFICATION: Internal - External Lookup Cache
 * SIDE: RED (system-level)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domain_age_cache', function (Blueprint $table) {
            $table->id();
            $table->string('domain', 255)->unique()->comment('The domain name (lowercase)');
            $table->timestamp('registered_at')->nullable()
                ->comment('When the domain was registered (from WHOIS)');
            $table->timestamp('checked_at')->comment('When we last performed the WHOIS lookup');
            $table->integer('age_hours')->nullable()
                ->comment('Calculated age in hours at time of check');
            $table->jsonb('whois_raw')->nullable()
                ->comment('Raw WHOIS response for audit/debugging');
            $table->string('lookup_status', 20)->default('unknown')
                ->comment('success, failed, unknown, rate_limited');
            $table->timestamps();

            $table->index('lookup_status');
            $table->index('checked_at');
            $table->index('age_hours');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domain_age_cache');
    }
};
