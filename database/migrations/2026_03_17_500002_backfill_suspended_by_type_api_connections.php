<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            UPDATE api_connections 
            SET suspended_by_type = CASE 
                WHEN suspended_reason ILIKE '%by admin%' THEN 'admin'
                WHEN suspended_reason ILIKE '%by user%' THEN 'customer'
                ELSE 'customer'
            END
            WHERE status = 'suspended' AND suspended_by_type IS NULL
        ");
    }

    public function down(): void
    {
    }
};
