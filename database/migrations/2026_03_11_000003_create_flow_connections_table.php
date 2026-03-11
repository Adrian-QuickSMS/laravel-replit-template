<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flow_connections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('flow_id');
            $table->string('source_node_uid', 64);
            $table->string('target_node_uid', 64);
            $table->string('source_handle', 50)->default('default'); // output port (e.g. 'yes', 'no', 'timeout', 'default')
            $table->string('label')->nullable();
            $table->timestamps();

            $table->foreign('flow_id')->references('id')->on('flows')->onDelete('cascade');
            $table->index(['flow_id', 'source_node_uid']);
            $table->index(['flow_id', 'target_node_uid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flow_connections');
    }
};
