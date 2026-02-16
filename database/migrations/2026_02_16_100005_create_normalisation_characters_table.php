<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('normalisation_characters', function (Blueprint $table) {
            $table->id();
            $table->string('base_character', 10)->unique();
            $table->string('character_type', 20);
            $table->json('equivalents')->default('[]');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
            $table->index('character_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('normalisation_characters');
    }
};
