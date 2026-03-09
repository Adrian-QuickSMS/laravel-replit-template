<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * User invitations table — invitation token created, email sending deferred.
 *
 * Flow: Admin creates invitation → record stored with token → event logged
 * → TODO: Connect to email server to send invitation email
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("CREATE TYPE invitation_status AS ENUM ('pending', 'accepted', 'expired', 'revoked')");

        Schema::create('user_invitations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('account_id')->comment('FK to accounts.id — parent tenant');
            $table->uuid('sub_account_id')->nullable()->comment('FK to sub_accounts.id — NULL = main account level');
            $table->string('email')->comment('Invitee email address');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('token', 64)->unique()->comment('SHA-256 invitation token');
            $table->string('role', 30)->default('user')->comment('Role to assign on acceptance');
            $table->string('sender_capability', 20)->nullable()->comment('Sender capability level');
            $table->jsonb('permission_toggles')->nullable()->comment('Permission overrides to apply');
            $table->timestamp('expires_at')->comment('Token expiry (72h default)');
            $table->timestamp('accepted_at')->nullable();
            $table->uuid('accepted_user_id')->nullable()->comment('FK to users.id after acceptance');
            $table->uuid('invited_by')->comment('FK to users.id — who sent the invite');
            $table->string('invited_by_name')->nullable();
            $table->string('revoked_by')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->string('revoke_reason')->nullable();
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->foreign('sub_account_id')->references('id')->on('sub_accounts')->onDelete('cascade');
            $table->foreign('invited_by')->references('id')->on('users')->onDelete('cascade');

            $table->index('account_id');
            $table->index(['account_id', 'email']);
            $table->index(['account_id', 'sub_account_id']);
            // token already has a unique index from ->unique() above
            $table->index('expires_at');
        });

        // Add enum column
        DB::statement("ALTER TABLE user_invitations ADD COLUMN status invitation_status DEFAULT 'pending'");

        // UUID generation trigger
        DB::unprepared("
            CREATE OR REPLACE FUNCTION generate_uuid_user_invitations()
            RETURNS TRIGGER AS \$\$
            BEGIN
                IF NEW.id IS NULL THEN
                    NEW.id = gen_random_uuid();
                END IF;
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        DB::unprepared("
            CREATE TRIGGER before_insert_user_invitations_uuid
            BEFORE INSERT ON user_invitations
            FOR EACH ROW
            EXECUTE FUNCTION generate_uuid_user_invitations();
        ");

        // Row Level Security
        DB::unprepared("ALTER TABLE user_invitations ENABLE ROW LEVEL SECURITY");
        DB::unprepared("ALTER TABLE user_invitations FORCE ROW LEVEL SECURITY");

        DB::unprepared("
            CREATE POLICY user_invitations_isolation ON user_invitations
            FOR ALL
            USING (
                account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
            )
            WITH CHECK (
                account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
            );
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP POLICY IF EXISTS user_invitations_isolation ON user_invitations");
        DB::unprepared("DROP TRIGGER IF EXISTS before_insert_user_invitations_uuid ON user_invitations");
        DB::unprepared("DROP FUNCTION IF EXISTS generate_uuid_user_invitations()");
        Schema::dropIfExists('user_invitations');
        DB::statement("DROP TYPE IF EXISTS invitation_status CASCADE");
    }
};
