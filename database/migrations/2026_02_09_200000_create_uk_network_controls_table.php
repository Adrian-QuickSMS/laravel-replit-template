<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uk_network_controls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mcc_mnc_id')->unique();
            $table->string('default_status', 20)->default('allowed');
            $table->string('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('mcc_mnc_id')
                ->references('id')
                ->on('mcc_mnc_master')
                ->onDelete('cascade');

            $table->index('default_status');
        });

        Schema::create('uk_network_overrides', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mcc_mnc_id');
            $table->unsignedBigInteger('account_id');
            $table->string('override_status', 20);
            $table->string('reason')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('mcc_mnc_id')
                ->references('id')
                ->on('mcc_mnc_master')
                ->onDelete('cascade');

            $table->unique(['mcc_mnc_id', 'account_id']);
            $table->index('account_id');
            $table->index('override_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uk_network_overrides');
        Schema::dropIfExists('uk_network_controls');
    }
};
