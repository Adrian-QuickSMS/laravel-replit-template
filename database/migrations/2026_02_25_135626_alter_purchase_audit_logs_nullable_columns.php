<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared('ALTER TABLE purchase_audit_logs ALTER COLUMN sub_account_id DROP NOT NULL');
    }

    public function down(): void
    {
        DB::unprepared('ALTER TABLE purchase_audit_logs ALTER COLUMN sub_account_id SET NOT NULL');
    }
};
