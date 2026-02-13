<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_code', 50)->unique();
            $table->string('name');
            $table->enum('status', ['active', 'suspended'])->default('active');
            $table->string('default_currency', 3)->default('GBP');
            $table->enum('default_billing_method', ['submitted', 'delivered'])->default('delivered');
            $table->text('notes')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('supplier_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
