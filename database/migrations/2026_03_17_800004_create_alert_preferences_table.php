<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alert_preferences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('user_id')->index();
            $table->string('category', 50); // billing, messaging, compliance, security, system, campaign
            $table->jsonb('channels')->default('["in_app","email"]'); // override channels for this category
            $table->boolean('is_muted')->default(false);
            $table->timestamp('muted_until')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'user_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alert_preferences');
    }
};
