<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domain_age_cache', function (Blueprint $table) {
            $table->id();
            $table->string('domain', 255)->unique();
            $table->timestamp('first_seen_at')->nullable();
            $table->integer('age_hours')->nullable();
            $table->string('lookup_status', 50)->default('pending');
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();

            $table->index('domain');
            $table->index('lookup_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domain_age_cache');
    }
};
