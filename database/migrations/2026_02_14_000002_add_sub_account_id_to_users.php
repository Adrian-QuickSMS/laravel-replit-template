<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add sub_account_id to users table for Account > Sub-Account > User hierarchy
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('sub_account_id')->nullable()->after('tenant_id')
                ->comment('FK to sub_accounts.id - optional sub-account assignment');
            $table->foreign('sub_account_id')->references('id')->on('sub_accounts')->onDelete('set null');
            $table->index('sub_account_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['sub_account_id']);
            $table->dropIndex(['sub_account_id']);
            $table->dropColumn('sub_account_id');
        });
    }
};
