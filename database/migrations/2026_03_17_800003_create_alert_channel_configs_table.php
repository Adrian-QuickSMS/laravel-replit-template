<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alert_channel_configs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable()->index();
            $table->uuid('user_id')->nullable();
            $table->string('channel', 30); // email, webhook, slack, teams, sms
            $table->jsonb('config')->default('{}'); // {webhook_url, slack_webhook_url, teams_webhook_url, email, hmac_secret}
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'user_id', 'channel']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alert_channel_configs');
    }
};
