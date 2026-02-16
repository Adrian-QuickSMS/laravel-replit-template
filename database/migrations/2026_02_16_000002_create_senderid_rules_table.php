<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * SenderID Rules - blocking/flagging rules for SenderID enforcement
 *
 * Prevents impersonation of banks, government bodies, delivery companies, etc.
 * Integrates with the Normalisation Library to catch character-substitution evasion.
 *
 * DATA CLASSIFICATION: Internal - Enforcement Configuration
 * SIDE: RED (admin-only configuration)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('senderid_rules', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name', 255)->comment('Human-readable rule name');
            $table->string('pattern', 255)->comment('The SenderID pattern to match against');
            $table->string('match_type', 20)->default('contains')
                ->comment('exact, contains, startswith, endswith, regex');
            $table->string('action', 20)->default('block')
                ->comment('block = silently drop, quarantine = send to admin review');
            $table->string('category', 50)->default('generic')
                ->comment('government_healthcare, banking_finance, delivery_logistics, miscellaneous, generic');
            $table->boolean('use_normalisation')->default(true)
                ->comment('Whether to apply normalisation library before matching');
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0)->comment('Lower = higher priority, evaluated first');
            $table->text('description')->nullable();
            $table->uuid('created_by')->nullable()->comment('Admin user who created the rule');
            $table->uuid('updated_by')->nullable()->comment('Admin user who last modified');
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
            $table->index('category');
            $table->index('action');
            $table->index(['is_active', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('senderid_rules');
    }
};
