<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('number_inventory', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['vmn', 'keyword']);
            $table->string('identifier')->unique();
            $table->string('country_code')->nullable();
            $table->enum('status', ['available', 'reserved', 'sold', 'suspended'])->default('available');
            $table->string('locked_by_session')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->timestamp('lock_expires_at')->nullable();
            $table->string('owner_sub_account_id')->nullable();
            $table->unsignedBigInteger('owner_user_id')->nullable();
            $table->string('hubspot_product_id')->nullable();
            $table->decimal('setup_fee', 10, 2)->default(0);
            $table->decimal('monthly_fee', 10, 2)->default(0);
            $table->timestamps();

            $table->index(['type', 'status']);
            $table->index('locked_by_session');
            $table->index('lock_expires_at');
            $table->index('owner_sub_account_id');
        });

        Schema::create('inventory_locks', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique();
            $table->unsignedBigInteger('user_id');
            $table->json('locked_items');
            $table->timestamp('created_at');
            $table->timestamp('expires_at');

            $table->index('expires_at');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_locks');
        Schema::dropIfExists('number_inventory');
    }
};
