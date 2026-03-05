<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE message_templates DROP CONSTRAINT IF EXISTS chk_message_templates_status");
        DB::statement("ALTER TABLE message_templates ADD CONSTRAINT chk_message_templates_status CHECK (status::text = ANY (ARRAY['draft', 'active', 'suspended', 'archived']::text[]))");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE message_templates DROP CONSTRAINT IF EXISTS chk_message_templates_status");
        DB::statement("ALTER TABLE message_templates ADD CONSTRAINT chk_message_templates_status CHECK (status::text = ANY (ARRAY['draft', 'active', 'archived']::text[]))");
    }
};
