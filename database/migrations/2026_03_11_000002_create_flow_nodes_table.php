<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flow_nodes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('flow_id');
            $table->string('node_uid', 64); // client-side unique ID (e.g. node_abc123)
            $table->string('type', 50); // trigger_api, trigger_sms_keyword, send_sms, send_rcs, wait, decision, webhook, tag, inbox_handoff, end
            $table->string('label')->nullable();
            $table->json('config')->nullable(); // node-specific configuration
            $table->decimal('position_x', 10, 2)->default(0);
            $table->decimal('position_y', 10, 2)->default(0);
            $table->timestamps();

            $table->foreign('flow_id')->references('id')->on('flows')->onDelete('cascade');
            $table->unique(['flow_id', 'node_uid']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flow_nodes');
    }
};
