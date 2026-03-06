<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_to_sms_setups', function (Blueprint $table) {
            $table->bigInteger('rcs_agent_id')->nullable()->after('sender_id_label');
        });
    }

    public function down(): void
    {
        Schema::table('email_to_sms_setups', function (Blueprint $table) {
            $table->dropColumn('rcs_agent_id');
        });
    }
};
