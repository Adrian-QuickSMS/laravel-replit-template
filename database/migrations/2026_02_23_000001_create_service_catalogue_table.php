<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Service Catalogue — flexible reference table for all billable services
 *
 * Decouples service definitions from ENUMs so new services (WhatsApp, Email, etc.)
 * can be added via the admin UI without requiring a database migration.
 *
 * The `slug` field maps to the `billable_product_type` ENUM for backward compatibility
 * with existing billing code, but the catalogue is the source of truth for the
 * pricing management UI.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_catalogue', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 50)->unique()->comment('Machine name, maps to billable_product_type where applicable');
            $table->string('display_name', 100)->comment('Human-readable name for UI');
            $table->text('description')->nullable();
            $table->string('unit_label', 30)->comment('per message, per token, per month, one-off, per keyword');
            $table->string('display_format', 10)->default('pence')->comment('pence or pounds');
            $table->integer('decimal_places')->default(3)->comment('3 for pence, 0 for pounds');
            $table->boolean('is_per_message')->default(false);
            $table->boolean('is_recurring')->default(false);
            $table->boolean('is_one_off')->default(false);
            $table->boolean('available_on_starter')->default(true);
            $table->boolean('available_on_enterprise')->default(true);
            $table->boolean('bespoke_only')->default(false);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('slug');
            $table->index('is_active');
            $table->index('sort_order');
        });

        // Seed with all current and new services
        DB::table('service_catalogue')->insert([
            [
                'slug' => 'sms',
                'display_name' => 'SMS',
                'description' => 'UK SMS messages',
                'unit_label' => 'per message',
                'display_format' => 'pence',
                'decimal_places' => 3,
                'is_per_message' => true,
                'is_recurring' => false,
                'is_one_off' => false,
                'available_on_starter' => true,
                'available_on_enterprise' => true,
                'bespoke_only' => false,
                'sort_order' => 10,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'rcs_basic',
                'display_name' => 'RCS Basic',
                'description' => 'RCS Basic messages',
                'unit_label' => 'per message',
                'display_format' => 'pence',
                'decimal_places' => 3,
                'is_per_message' => true,
                'is_recurring' => false,
                'is_one_off' => false,
                'available_on_starter' => true,
                'available_on_enterprise' => true,
                'bespoke_only' => false,
                'sort_order' => 20,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'rcs_single',
                'display_name' => 'RCS Single',
                'description' => 'RCS Single messages',
                'unit_label' => 'per message',
                'display_format' => 'pence',
                'decimal_places' => 3,
                'is_per_message' => true,
                'is_recurring' => false,
                'is_one_off' => false,
                'available_on_starter' => true,
                'available_on_enterprise' => true,
                'bespoke_only' => false,
                'sort_order' => 30,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'sms_international',
                'display_name' => 'International SMS',
                'description' => 'International SMS messages (bespoke pricing per country)',
                'unit_label' => 'per message',
                'display_format' => 'pence',
                'decimal_places' => 3,
                'is_per_message' => true,
                'is_recurring' => false,
                'is_one_off' => false,
                'available_on_starter' => true,
                'available_on_enterprise' => true,
                'bespoke_only' => false,
                'sort_order' => 40,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'ai_query',
                'display_name' => 'AI Token',
                'description' => 'AI query tokens',
                'unit_label' => 'per token',
                'display_format' => 'pence',
                'decimal_places' => 3,
                'is_per_message' => false,
                'is_recurring' => false,
                'is_one_off' => false,
                'available_on_starter' => true,
                'available_on_enterprise' => true,
                'bespoke_only' => false,
                'sort_order' => 50,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'virtual_number_setup',
                'display_name' => 'UK Virtual Mobile Number Set Up',
                'description' => 'One-off setup fee for UK virtual mobile number',
                'unit_label' => 'one-off',
                'display_format' => 'pounds',
                'decimal_places' => 0,
                'is_per_message' => false,
                'is_recurring' => false,
                'is_one_off' => true,
                'available_on_starter' => true,
                'available_on_enterprise' => true,
                'bespoke_only' => false,
                'sort_order' => 60,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'virtual_number_monthly',
                'display_name' => 'UK Virtual Mobile Number Monthly',
                'description' => 'Monthly recurring charge for UK virtual mobile number',
                'unit_label' => 'per month',
                'display_format' => 'pounds',
                'decimal_places' => 0,
                'is_per_message' => false,
                'is_recurring' => true,
                'is_one_off' => false,
                'available_on_starter' => true,
                'available_on_enterprise' => true,
                'bespoke_only' => false,
                'sort_order' => 70,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'shortcode_setup',
                'display_name' => 'Dedicated UK Short Code Set Up',
                'description' => 'One-off setup fee for dedicated UK short code',
                'unit_label' => 'one-off',
                'display_format' => 'pounds',
                'decimal_places' => 0,
                'is_per_message' => false,
                'is_recurring' => false,
                'is_one_off' => true,
                'available_on_starter' => true,
                'available_on_enterprise' => true,
                'bespoke_only' => false,
                'sort_order' => 80,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'shortcode_monthly',
                'display_name' => 'Dedicated UK Short Code Monthly',
                'description' => 'Monthly recurring charge for dedicated UK short code',
                'unit_label' => 'per month',
                'display_format' => 'pounds',
                'decimal_places' => 0,
                'is_per_message' => false,
                'is_recurring' => true,
                'is_one_off' => false,
                'available_on_starter' => true,
                'available_on_enterprise' => true,
                'bespoke_only' => false,
                'sort_order' => 90,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'shortcode_inbound_sms',
                'display_name' => 'Dedicated Short Code Inbound SMS',
                'description' => 'Inbound SMS on dedicated short codes',
                'unit_label' => 'per message',
                'display_format' => 'pence',
                'decimal_places' => 3,
                'is_per_message' => true,
                'is_recurring' => false,
                'is_one_off' => false,
                'available_on_starter' => true,
                'available_on_enterprise' => true,
                'bespoke_only' => false,
                'sort_order' => 100,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'shortcode_keyword',
                'display_name' => 'UK Short Code Keyword',
                'description' => 'Short code keyword rental',
                'unit_label' => 'per keyword',
                'display_format' => 'pounds',
                'decimal_places' => 0,
                'is_per_message' => false,
                'is_recurring' => false,
                'is_one_off' => false,
                'available_on_starter' => true,
                'available_on_enterprise' => true,
                'bespoke_only' => false,
                'sort_order' => 110,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'support',
                'display_name' => 'Support',
                'description' => 'Monthly support charge',
                'unit_label' => 'per month',
                'display_format' => 'pounds',
                'decimal_places' => 0,
                'is_per_message' => false,
                'is_recurring' => true,
                'is_one_off' => false,
                'available_on_starter' => false,
                'available_on_enterprise' => false,
                'bespoke_only' => true,
                'sort_order' => 120,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('service_catalogue');
    }
};
