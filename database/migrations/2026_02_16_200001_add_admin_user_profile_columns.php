<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_users', function (Blueprint $table) {
            if (!Schema::hasColumn('admin_users', 'department')) {
                $table->string('department', 100)->nullable()->after('role');
            }
            if (!Schema::hasColumn('admin_users', 'mfa_method')) {
                $table->string('mfa_method', 20)->nullable()->after('mfa_enabled');
            }
            if (!Schema::hasColumn('admin_users', 'sms_mfa_code')) {
                $table->string('sms_mfa_code', 255)->nullable()->after('mfa_recovery_codes');
            }
            if (!Schema::hasColumn('admin_users', 'sms_mfa_expires_at')) {
                $table->timestamp('sms_mfa_expires_at')->nullable()->after('sms_mfa_code');
            }
            if (!Schema::hasColumn('admin_users', 'sms_mfa_attempts')) {
                $table->integer('sms_mfa_attempts')->default(0)->after('sms_mfa_expires_at');
            }
            if (!Schema::hasColumn('admin_users', 'invite_token')) {
                $table->string('invite_token', 255)->nullable()->unique()->after('sms_mfa_attempts');
            }
            if (!Schema::hasColumn('admin_users', 'invite_sent_at')) {
                $table->timestamp('invite_sent_at')->nullable()->after('invite_token');
            }
            if (!Schema::hasColumn('admin_users', 'invite_expires_at')) {
                $table->timestamp('invite_expires_at')->nullable()->after('invite_sent_at');
            }
        });

        $exists = DB::select("SELECT 1 FROM pg_constraint WHERE conname = 'chk_admin_mfa_method'");
        if (empty($exists)) {
            DB::statement("ALTER TABLE admin_users ADD CONSTRAINT chk_admin_mfa_method CHECK (mfa_method IN ('authenticator', 'sms', 'both') OR mfa_method IS NULL)");
        }

        $exists = DB::select("SELECT 1 FROM pg_constraint WHERE conname = 'chk_admin_email_domain'");
        if (empty($exists)) {
            DB::statement("ALTER TABLE admin_users ADD CONSTRAINT chk_admin_email_domain CHECK (email LIKE '%@quicksms.com')");
        }
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE admin_users DROP CONSTRAINT IF EXISTS chk_admin_mfa_method");
        DB::statement("ALTER TABLE admin_users DROP CONSTRAINT IF EXISTS chk_admin_email_domain");

        Schema::table('admin_users', function (Blueprint $table) {
            $cols = ['department', 'mfa_method', 'sms_mfa_code', 'sms_mfa_expires_at', 'sms_mfa_attempts', 'invite_token', 'invite_sent_at', 'invite_expires_at'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('admin_users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
