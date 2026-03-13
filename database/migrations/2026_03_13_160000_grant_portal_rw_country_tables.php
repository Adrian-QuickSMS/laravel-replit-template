<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('GRANT SELECT ON country_controls TO portal_rw');
        DB::statement('GRANT SELECT, INSERT ON country_requests TO portal_rw');
        DB::statement('GRANT SELECT ON country_control_overrides TO portal_rw');
    }

    public function down(): void
    {
        DB::statement('REVOKE SELECT ON country_controls FROM portal_rw');
        DB::statement('REVOKE SELECT, INSERT ON country_requests FROM portal_rw');
        DB::statement('REVOKE SELECT ON country_control_overrides FROM portal_rw');
    }
};
