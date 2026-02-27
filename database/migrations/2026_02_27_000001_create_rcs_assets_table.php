<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rcs_assets', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique();
            $table->string('account_id', 36)->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->enum('source_type', ['upload', 'url'])->default('upload');
            $table->string('source_url', 2048)->nullable();
            $table->string('original_storage_path', 512)->nullable();
            $table->string('storage_path', 512);
            $table->string('public_url', 2048);
            $table->string('mime_type', 64);
            $table->unsignedInteger('file_size');
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->json('edit_params')->nullable();
            $table->boolean('is_draft')->default(true);
            $table->string('draft_session', 64)->nullable()->index();
            $table->timestamps();

            $table->index('user_id');
            $table->index('created_at');
            $table->index(['account_id', 'is_draft', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rcs_assets');
    }
};
