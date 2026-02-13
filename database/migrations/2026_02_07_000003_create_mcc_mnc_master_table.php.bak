<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mcc_mnc_master', function (Blueprint $table) {
            $table->id();
            $table->string('mcc', 3);
            $table->string('mnc', 3);
            $table->string('country_name');
            $table->string('country_iso', 2);
            $table->string('network_name');
            $table->enum('network_type', ['mobile', 'fixed', 'virtual'])->default('mobile');
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['mcc', 'mnc']);
            $table->index('country_iso');
            $table->index('active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mcc_mnc_master');
    }
};
