<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Normalisation Characters - character equivalence library
 *
 * Maps look-alike characters to their base form so spammers can't evade
 * rules by substituting characters (Cyrillic, leet-speak, homoglyphs).
 * 36 immutable base characters: A-Z (26) + 0-9 (10).
 *
 * DATA CLASSIFICATION: Internal - Enforcement Configuration
 * SIDE: RED (admin-only configuration)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('normalisation_characters', function (Blueprint $table) {
            $table->id();
            $table->string('base_character', 1)->unique()
                ->comment('The canonical character: A-Z or 0-9');
            $table->string('character_type', 10)->default('letter')
                ->comment('letter or digit');
            $table->jsonb('equivalents')->default('[]')
                ->comment('Array of equivalent characters that map to this base');
            $table->boolean('is_active')->default(true);
            $table->uuid('updated_by')->nullable()->comment('Admin user who last modified');
            $table->timestamps();

            $table->index('character_type');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('normalisation_characters');
    }
};
