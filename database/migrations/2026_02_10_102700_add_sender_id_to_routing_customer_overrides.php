<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('routing_customer_overrides', function (Blueprint $table) {
            $table->string('sender_id', 15)->nullable()->after('product_type');
            $table->string('sub_account_name')->nullable()->after('sub_account_id');
            $table->string('account_name')->nullable()->after('account_id');
        });
    }

    public function down(): void
    {
        Schema::table('routing_customer_overrides', function (Blueprint $table) {
            $table->dropColumn(['sender_id', 'sub_account_name', 'account_name']);
        });
    }
};
