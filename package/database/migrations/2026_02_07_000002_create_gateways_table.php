<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gateways', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->string('gateway_code', 50)->unique();
            $table->string('name');
            $table->string('currency', 3)->default('GBP');
            $table->enum('billing_method', ['submitted', 'delivered'])->default('delivered');
            $table->string('fx_source')->default('ECB');
            $table->boolean('active')->default(true);
            $table->timestamp('last_rate_update')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('supplier_id');
            $table->index('gateway_code');
            $table->index('active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gateways');
    }
};
