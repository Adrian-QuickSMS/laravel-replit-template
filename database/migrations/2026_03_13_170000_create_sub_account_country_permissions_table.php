<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sub_account_country_permissions', function (Blueprint $table) {
            $table->id();
            $table->uuid('sub_account_id');
            $table->unsignedBigInteger('country_control_id');
            $table->string('permission_status', 20); // allowed, blocked
            $table->string('reason', 500)->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();

            $table->foreign('sub_account_id')->references('id')->on('sub_accounts')->onDelete('cascade');
            $table->foreign('country_control_id')->references('id')->on('country_controls')->onDelete('cascade');
            $table->unique(['sub_account_id', 'country_control_id']);
            $table->index('sub_account_id');
        });

        // Grant access to portal role
        DB::statement('GRANT SELECT ON sub_account_country_permissions TO portal_rw');
    }

    public function down(): void
    {
        DB::statement('REVOKE SELECT ON sub_account_country_permissions FROM portal_rw');
        Schema::dropIfExists('sub_account_country_permissions');
    }
};
