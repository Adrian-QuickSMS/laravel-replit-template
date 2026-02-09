<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uk_prefixes', function (Blueprint $table) {
            $table->id();
            $table->string('prefix', 20)->index();
            $table->string('number_block_raw', 30)->nullable();
            $table->string('status', 30)->default('allocated');
            $table->string('cp_name', 255);
            $table->string('number_length', 50)->nullable();
            $table->unsignedBigInteger('mcc_mnc_id')->nullable();
            $table->string('match_status', 20)->default('unmatched');
            $table->boolean('active')->default(true);
            $table->date('allocation_date')->nullable();
            $table->timestamps();

            $table->foreign('mcc_mnc_id')->references('id')->on('mcc_mnc_master')->onDelete('set null');
            $table->index(['cp_name']);
            $table->index(['match_status']);
            $table->unique(['prefix']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uk_prefixes');
    }
};
