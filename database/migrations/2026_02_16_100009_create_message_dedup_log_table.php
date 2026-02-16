<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_dedup_log', function (Blueprint $table) {
            $table->id();
            $table->uuid('account_id');
            $table->string('message_hash', 64);
            $table->string('normalised_hash', 64)->nullable();
            $table->string('recipient_hash', 64)->nullable();
            $table->timestamp('first_seen_at')->useCurrent();
            $table->timestamp('expires_at');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['account_id', 'message_hash']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_dedup_log');
    }
};
