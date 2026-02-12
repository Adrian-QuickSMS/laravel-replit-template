<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * GREEN SIDE: Account Settings
     *
     * DATA CLASSIFICATION: Internal - Account Configuration
     * SIDE: GREEN (customer can view/edit own settings)
     * TENANT ISOLATION: Primary key IS tenant_id (one row per account)
     *
     * SECURITY NOTES:
     * - Portal users access via account_settings_view
     * - All writes via sp_update_account_settings stored procedure
     * - Sensitive settings (API rate limits, fraud flags) stored on RED side
     */
    public function up(): void
    {
        Schema::create('account_settings', function (Blueprint $table) {
            // Primary key IS the tenant_id (one settings row per account)
            $table->binary('account_id', 16)->primary();
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');

            // Notification preferences (GREEN - customer configurable)
            $table->boolean('notify_low_balance')->default(true);
            $table->decimal('low_balance_threshold', 10, 2)->default(10.00);
            $table->boolean('notify_failed_messages')->default(true);
            $table->boolean('notify_monthly_summary')->default(true);

            // Communication preferences
            $table->boolean('marketing_emails')->default(false);
            $table->boolean('product_updates')->default(true);

            // Portal preferences
            $table->string('timezone', 50)->default('Europe/London');
            $table->string('date_format', 20)->default('d/m/Y');
            $table->string('currency', 3)->default('GBP');

            // Webhook settings (GREEN)
            $table->text('webhook_urls')->nullable()->comment('JSON array of webhook endpoints');
            $table->string('webhook_secret')->nullable()->comment('Encrypted webhook signing secret');

            // Session security (GREEN - customer configurable)
            $table->integer('session_timeout_minutes')->default(120);
            $table->boolean('require_mfa_for_api')->default(false);

            // Audit
            $table->string('updated_by')->nullable();
            $table->timestamps();

            $table->index('account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_settings');
    }
};
