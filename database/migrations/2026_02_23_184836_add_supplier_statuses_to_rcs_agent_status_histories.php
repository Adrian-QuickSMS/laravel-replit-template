<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE rcs_agent_status_histories DROP CONSTRAINT IF EXISTS rcs_agent_status_histories_from_status_check');
        DB::statement('ALTER TABLE rcs_agent_status_histories DROP CONSTRAINT IF EXISTS rcs_agent_status_histories_to_status_check');

        DB::statement("ALTER TABLE rcs_agent_status_histories ADD CONSTRAINT rcs_agent_status_histories_from_status_check CHECK (from_status::text = ANY (ARRAY['draft','submitted','in_review','pending_info','info_provided','sent_to_supplier','supplier_approved','approved','rejected','suspended','revoked']::text[]))");
        DB::statement("ALTER TABLE rcs_agent_status_histories ADD CONSTRAINT rcs_agent_status_histories_to_status_check CHECK (to_status::text = ANY (ARRAY['draft','submitted','in_review','pending_info','info_provided','sent_to_supplier','supplier_approved','approved','rejected','suspended','revoked']::text[]))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE rcs_agent_status_histories DROP CONSTRAINT IF EXISTS rcs_agent_status_histories_from_status_check');
        DB::statement('ALTER TABLE rcs_agent_status_histories DROP CONSTRAINT IF EXISTS rcs_agent_status_histories_to_status_check');

        DB::statement("ALTER TABLE rcs_agent_status_histories ADD CONSTRAINT rcs_agent_status_histories_from_status_check CHECK (from_status::text = ANY (ARRAY['draft','submitted','in_review','pending_info','info_provided','approved','rejected','suspended','revoked']::text[]))");
        DB::statement("ALTER TABLE rcs_agent_status_histories ADD CONSTRAINT rcs_agent_status_histories_to_status_check CHECK (to_status::text = ANY (ARRAY['draft','submitted','in_review','pending_info','info_provided','approved','rejected','suspended','revoked']::text[]))");
    }
};
