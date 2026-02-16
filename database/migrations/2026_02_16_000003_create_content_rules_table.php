<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Content Rules - message body text enforcement
 *
 * Scans message content for prohibited keywords, phrases, or patterns.
 * Supports keyword-based matching (simple text) and regex for complex matching.
 *
 * DATA CLASSIFICATION: Internal - Enforcement Configuration
 * SIDE: RED (admin-only configuration)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_rules', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name', 255)->comment('Human-readable rule name');
            $table->text('pattern')->comment('Keyword text or regex pattern');
            $table->string('match_type', 20)->default('keyword')
                ->comment('keyword = simple text match, regex = regular expression');
            $table->string('action', 20)->default('block')
                ->comment('block = silently drop, quarantine = send to admin review');
            $table->boolean('use_normalisation')->default(true)
                ->comment('Whether to apply normalisation library before matching');
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
        Schema::dropIfExists('content_rules');
    }
};
