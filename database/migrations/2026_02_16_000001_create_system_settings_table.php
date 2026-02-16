<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * System Settings - key-value store for feature flags, anti-spam config,
 * domain age settings, and other platform-wide configuration.
 *
 * DATA CLASSIFICATION: Internal - System Configuration
 * SIDE: RED (admin-only)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->jsonb('value');
            $table->string('group', 50)->default('general')->comment('Logical grouping: enforcement, anti_spam, domain_age, feature_flags');
            $table->text('description')->nullable();
            $table->uuid('updated_by')->nullable()->comment('FK to admin users.id');
            $table->timestamps();

            $table->index('group');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
