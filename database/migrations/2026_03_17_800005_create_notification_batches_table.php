<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_batches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable()->index();
            $table->uuid('user_id')->nullable();
            $table->string('batch_type', 30); // batched_15m, batched_1h, daily_digest
            $table->string('channel', 30); // email, in_app, webhook, etc.
            $table->jsonb('items')->default('[]'); // array of notification payloads
            $table->timestamp('scheduled_for')->index();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['batch_type', 'scheduled_for', 'dispatched_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_batches');
    }
};
