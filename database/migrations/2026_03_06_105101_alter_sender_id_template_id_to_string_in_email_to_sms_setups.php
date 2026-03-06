<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE email_to_sms_setups ALTER COLUMN sender_id_template_id TYPE varchar(255) USING sender_id_template_id::varchar');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE email_to_sms_setups ALTER COLUMN sender_id_template_id TYPE uuid USING sender_id_template_id::uuid');
    }
};
