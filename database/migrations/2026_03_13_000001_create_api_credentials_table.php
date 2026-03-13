<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_credentials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id')->index();
            $table->string('name', 100);
            $table->enum('auth_type', ['none', 'basic', 'bearer', 'api_key', 'custom_header'])->default('bearer');
            $table->text('credentials'); // Encrypted JSON via Laravel Crypt
            $table->text('description')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_credentials');
    }
};
