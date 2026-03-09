<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Expand user roles to include messaging_manager, finance, developer, readonly.
 * Add sender_capability_level, permission_toggles JSON, and user-level caps.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Expand the user_role enum to include new roles
        // PostgreSQL ALTER TYPE ... ADD VALUE is not transactional, so run outside transaction
        DB::statement("ALTER TYPE user_role ADD VALUE IF NOT EXISTS 'messaging_manager'");
        DB::statement("ALTER TYPE user_role ADD VALUE IF NOT EXISTS 'finance'");
        DB::statement("ALTER TYPE user_role ADD VALUE IF NOT EXISTS 'developer'");

        // Create sender capability level enum
        DB::statement("CREATE TYPE sender_capability_level AS ENUM ('advanced', 'restricted', 'none')");

        Schema::table('users', function (Blueprint $table) {
            // User-level caps (must be ≤ sub-account caps)
            $table->decimal('monthly_spending_cap', 12, 4)->nullable()->after('sub_account_id')
                ->comment('User monthly spending cap. NULL = inherits sub-account cap');
            $table->integer('monthly_message_cap')->nullable()->after('monthly_spending_cap')
                ->comment('User monthly message cap. NULL = inherits sub-account cap');
            $table->integer('daily_send_limit')->nullable()->after('monthly_message_cap')
                ->comment('User daily send limit. NULL = inherits sub-account limit');

            // Usage tracking (aggregated from actual messages)
            $table->decimal('monthly_spend_used', 12, 4)->default(0)->after('daily_send_limit')
                ->comment('Aggregated monthly spend from actual messages sent');
            $table->integer('monthly_messages_used')->default(0)->after('monthly_spend_used')
                ->comment('Aggregated monthly messages sent');
            $table->integer('daily_sends_used')->default(0)->after('monthly_messages_used')
                ->comment('Aggregated daily sends count');

            // Permission toggles — JSON object with boolean flags per feature
            $table->jsonb('permission_toggles')->nullable()->after('daily_sends_used')
                ->comment('Granular permission toggles. NULL = role defaults apply');

            // Ownership transfer tracking
            $table->boolean('is_account_owner')->default(false)->after('permission_toggles')
                ->comment('True for the main account owner. Only one per account.');
            $table->timestamp('owner_since')->nullable()->after('is_account_owner');
        });

        // Add sender capability level column
        DB::statement("ALTER TABLE users ADD COLUMN sender_capability sender_capability_level DEFAULT 'none'");

        // Index for permission queries
        DB::statement("CREATE INDEX idx_users_sender_capability ON users (tenant_id, sender_capability)");
        DB::statement("CREATE INDEX idx_users_account_owner ON users (tenant_id, is_account_owner) WHERE is_account_owner = true");
    }

    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS idx_users_account_owner");
        DB::statement("DROP INDEX IF EXISTS idx_users_sender_capability");
        DB::statement("ALTER TABLE users DROP COLUMN IF EXISTS sender_capability");

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'monthly_spending_cap', 'monthly_message_cap', 'daily_send_limit',
                'monthly_spend_used', 'monthly_messages_used', 'daily_sends_used',
                'permission_toggles', 'is_account_owner', 'owner_since',
            ]);
        });

        DB::statement("DROP TYPE IF EXISTS sender_capability_level CASCADE");

        // Note: Cannot remove values from PostgreSQL enums. The added roles
        // (messaging_manager, finance, developer) will remain in the enum.
    }
};
