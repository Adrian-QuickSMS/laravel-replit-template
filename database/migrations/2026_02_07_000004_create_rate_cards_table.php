<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rate_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->foreignId('gateway_id')->constrained()->onDelete('cascade');
            $table->foreignId('mcc_mnc_id')->constrained('mcc_mnc_master')->onDelete('cascade');
            $table->string('mcc', 3);
            $table->string('mnc', 3);
            $table->string('country_name');
            $table->string('country_iso', 2);
            $table->string('network_name');
            $table->enum('product_type', ['SMS', 'RCS_BASIC', 'RCS_SINGLE'])->default('SMS');
            $table->enum('billing_method', ['submitted', 'delivered']);
            $table->string('currency', 3);
            $table->decimal('native_rate', 10, 6);
            $table->decimal('gbp_rate', 10, 6);
            $table->decimal('fx_rate', 10, 6)->nullable();
            $table->timestamp('fx_timestamp')->nullable();
            $table->date('valid_from');
            $table->date('valid_to')->nullable();
            $table->boolean('active')->default(true);
            $table->integer('version')->default(1);
            $table->foreignId('previous_version_id')->nullable()->constrained('rate_cards')->onDelete('set null');
            $table->string('created_by')->nullable();
            $table->string('change_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for fast message rating lookups
            $table->index(['mcc', 'mnc', 'gateway_id', 'product_type', 'active']);
            $table->index(['gateway_id', 'active', 'valid_from', 'valid_to']);
            $table->index('supplier_id');
            $table->index('valid_from');
            $table->index('valid_to');
            $table->index('active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_cards');
    }
};
