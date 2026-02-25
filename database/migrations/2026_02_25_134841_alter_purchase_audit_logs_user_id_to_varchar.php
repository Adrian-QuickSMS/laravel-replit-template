<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared('ALTER TABLE purchase_audit_logs ALTER COLUMN user_id TYPE varchar(36) USING user_id::varchar');
    }

    public function down(): void
    {
        DB::unprepared('ALTER TABLE purchase_audit_logs ALTER COLUMN user_id TYPE bigint USING 0');
    }
};
