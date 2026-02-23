<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * RCS Agent Status History - audit trail for all workflow transitions
 *
 * Follows the SenderIdStatusHistory pattern.
 *
 * DATA CLASSIFICATION: Internal - Audit Trail
 * SIDE: RED (admin-only for review history)
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('rcs_agent_status_histories')) {
            return;
        }
        Schema::create('rcs_agent_status_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rcs_agent_id')->comment('FK to rcs_agents.id');
            $table->string('from_status', 20)->nullable()->comment('null for initial creation');
            $table->string('to_status', 20);
            $table->string('action', 50)->comment('Human-readable action name');
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->jsonb('payload_snapshot')->nullable()->comment('Full agent state at time of transition');

            // Actor information
            $table->uuid('user_id')->nullable()->comment('FK to users.id or admin user');
            $table->string('user_name', 255)->nullable();
            $table->string('user_email', 255)->nullable();
            $table->string('ip_address', 45)->nullable()->comment('IPv4 or IPv6');
            $table->text('user_agent')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->foreign('rcs_agent_id')->references('id')->on('rcs_agents')->onDelete('cascade');

            $table->index('rcs_agent_id');
            $table->index(['rcs_agent_id', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rcs_agent_status_histories');
    }
};
