<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            INSERT INTO ledger_accounts (id, code, name, account_type, is_system, created_at)
            VALUES (gen_random_uuid(), 'VAT_OUTPUT', 'VAT Output (Sales Tax)', 'liability', true, NOW())
            ON CONFLICT (code) DO NOTHING
        ");
    }

    public function down(): void
    {
        DB::statement("DELETE FROM ledger_accounts WHERE code = 'VAT_OUTPUT'");
    }
};
