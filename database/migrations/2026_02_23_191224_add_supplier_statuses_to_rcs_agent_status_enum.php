<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TYPE rcs_agent_status ADD VALUE IF NOT EXISTS 'sent_to_supplier' BEFORE 'approved'");
        DB::statement("ALTER TYPE rcs_agent_status ADD VALUE IF NOT EXISTS 'supplier_approved' BEFORE 'approved'");
    }

    public function down(): void
    {
        // PostgreSQL does not support removing enum values directly.
        // To reverse, you would need to recreate the type, which is destructive.
    }
};
