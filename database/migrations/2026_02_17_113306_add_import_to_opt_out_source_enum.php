<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TYPE opt_out_source ADD VALUE IF NOT EXISTS 'import'");
    }

    public function down(): void
    {
    }
};
