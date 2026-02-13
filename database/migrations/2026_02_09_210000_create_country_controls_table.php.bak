<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('country_controls', function (Blueprint $table) {
            $table->id();
            $table->string('country_iso', 3)->unique();
            $table->string('country_name', 255);
            $table->string('country_prefix', 10)->nullable();
            $table->string('default_status', 20)->default('allowed');
            $table->string('risk_level', 20)->default('low');
            $table->integer('network_count')->default(0);
            $table->timestamps();

            $table->index('default_status');
            $table->index('risk_level');
        });

        Schema::create('country_control_overrides', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('country_control_id');
            $table->unsignedBigInteger('account_id');
            $table->string('override_status', 20);
            $table->string('reason', 500)->nullable();
            $table->string('created_by', 255)->nullable();
            $table->timestamps();

            $table->foreign('country_control_id')->references('id')->on('country_controls')->onDelete('cascade');
            $table->unique(['country_control_id', 'account_id']);
            $table->index('account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('country_control_overrides');
        Schema::dropIfExists('country_controls');
    }
};
