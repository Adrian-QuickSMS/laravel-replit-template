<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fx_rates', function (Blueprint $table) {
            $table->id();
            $table->string('from_currency', 3);
            $table->string('to_currency', 3)->default('GBP');
            $table->decimal('rate', 10, 6);
            $table->string('source')->default('ECB');
            $table->date('rate_date');
            $table->timestamps();

            $table->unique(['from_currency', 'to_currency', 'rate_date']);
            $table->index('rate_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fx_rates');
    }
};
