<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enforcement_exemptions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->default(DB::raw('gen_random_uuid()'));
            $table->string('engine', 50);
            $table->string('exemption_type', 50);
            $table->string('scope', 50)->default('global');
            $table->string('value', 500);
            $table->string('rule_id', 255)->nullable();
            $table->uuid('account_id')->nullable();
            $table->text('reason')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('created_by', 255)->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('engine');
            $table->index('is_active');
            $table->index('exemption_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enforcement_exemptions');
    }
};
