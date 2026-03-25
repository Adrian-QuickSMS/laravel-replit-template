<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add stripe_customer_id to accounts table
        if (Schema::hasTable('accounts') && !Schema::hasColumn('accounts', 'stripe_customer_id')) {
            Schema::table('accounts', function (Blueprint $table) {
                $table->string('stripe_customer_id')->nullable()->after('currency');
                $table->index('stripe_customer_id');
            });
        }

        // 2. Extend auto_topup_configs with new columns
        if (Schema::hasTable('auto_topup_configs')) {
            DB::statement('ALTER TABLE auto_topup_configs ALTER COLUMN threshold_amount DROP NOT NULL');
            DB::statement('ALTER TABLE auto_topup_configs ALTER COLUMN topup_amount DROP NOT NULL');
            DB::statement('ALTER TABLE auto_topup_configs ALTER COLUMN stripe_customer_id DROP NOT NULL');
            DB::statement('ALTER TABLE auto_topup_configs ALTER COLUMN stripe_payment_method_id DROP NOT NULL');

            Schema::table('auto_topup_configs', function (Blueprint $table) {
                if (!Schema::hasColumn('auto_topup_configs', 'daily_topup_cap')) {
                    $table->decimal('daily_topup_cap', 10, 4)->nullable()->after('max_topups_per_day')
                        ->comment('Maximum total auto top-up value per day (GBP)');
                }
                if (!Schema::hasColumn('auto_topup_configs', 'min_minutes_between_topups')) {
                    $table->integer('min_minutes_between_topups')->default(0)->after('daily_topup_cap')
                        ->comment('Minimum cooldown in minutes between auto top-ups');
                }
                if (!Schema::hasColumn('auto_topup_configs', 'card_brand')) {
                    $table->string('card_brand', 20)->nullable()->after('stripe_payment_method_id')
                        ->comment('e.g. visa, mastercard, amex');
                }
                if (!Schema::hasColumn('auto_topup_configs', 'card_last4')) {
                    $table->string('card_last4', 4)->nullable()->after('card_brand');
                }
                if (!Schema::hasColumn('auto_topup_configs', 'card_exp_month')) {
                    $table->smallInteger('card_exp_month')->nullable()->after('card_last4');
                }
                if (!Schema::hasColumn('auto_topup_configs', 'card_exp_year')) {
                    $table->smallInteger('card_exp_year')->nullable()->after('card_exp_month');
                }

                // Notification preferences
                if (!Schema::hasColumn('auto_topup_configs', 'notify_email_success')) {
                    $table->boolean('notify_email_success')->default(true)->after('card_exp_year');
                }
                if (!Schema::hasColumn('auto_topup_configs', 'notify_email_failure')) {
                    $table->boolean('notify_email_failure')->default(true)->after('notify_email_success');
                }
                if (!Schema::hasColumn('auto_topup_configs', 'notify_inapp_success')) {
                    $table->boolean('notify_inapp_success')->default(true)->after('notify_email_failure');
                }
                if (!Schema::hasColumn('auto_topup_configs', 'notify_inapp_failure')) {
                    $table->boolean('notify_inapp_failure')->default(true)->after('notify_inapp_success');
                }
                if (!Schema::hasColumn('auto_topup_configs', 'notify_requires_action')) {
                    $table->boolean('notify_requires_action')->default(true)->after('notify_inapp_failure');
                }

                // Retry configuration
                if (!Schema::hasColumn('auto_topup_configs', 'retry_attempts')) {
                    $table->integer('retry_attempts')->default(2)->after('notify_requires_action');
                }
                if (!Schema::hasColumn('auto_topup_configs', 'retry_delay_minutes')) {
                    $table->integer('retry_delay_minutes')->default(10)->after('retry_attempts');
                }
                if (!Schema::hasColumn('auto_topup_configs', 'disable_after_consecutive_failures')) {
                    $table->integer('disable_after_consecutive_failures')->default(3)->after('retry_delay_minutes');
                }
                if (!Schema::hasColumn('auto_topup_configs', 'consecutive_failure_count')) {
                    $table->integer('consecutive_failure_count')->default(0)->after('disable_after_consecutive_failures');
                }
                if (!Schema::hasColumn('auto_topup_configs', 'last_successful_topup_at')) {
                    $table->timestamp('last_successful_topup_at')->nullable()->after('last_triggered_at');
                }

                // Admin lock
                if (!Schema::hasColumn('auto_topup_configs', 'admin_locked')) {
                    $table->boolean('admin_locked')->default(false)->after('last_successful_topup_at');
                }
                if (!Schema::hasColumn('auto_topup_configs', 'admin_locked_reason')) {
                    $table->text('admin_locked_reason')->nullable()->after('admin_locked');
                }
                if (!Schema::hasColumn('auto_topup_configs', 'admin_locked_at')) {
                    $table->timestamp('admin_locked_at')->nullable()->after('admin_locked_reason');
                }
                if (!Schema::hasColumn('auto_topup_configs', 'admin_locked_by')) {
                    $table->uuid('admin_locked_by')->nullable()->after('admin_locked_at');
                }
                if (!Schema::hasColumn('auto_topup_configs', 'updated_by_user_id')) {
                    $table->uuid('updated_by_user_id')->nullable()->after('admin_locked_by');
                }
            });
        }

        // 3. Create auto_topup_events table
        if (!Schema::hasTable('auto_topup_events')) {
            Schema::create('auto_topup_events', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('account_id');
                $table->uuid('config_id')->nullable();
                $table->string('event_type', 40);
                $table->string('status', 20)->default('pending');
                $table->decimal('trigger_balance', 12, 4)->nullable();
                $table->decimal('trigger_threshold', 10, 4)->nullable();
                $table->decimal('topup_amount', 10, 4)->nullable();
                $table->decimal('vat_amount', 10, 4)->nullable();
                $table->decimal('total_charge_amount', 10, 4)->nullable();
                $table->integer('daily_count_before')->nullable();
                $table->decimal('daily_value_before', 12, 4)->nullable();
                $table->string('stripe_payment_intent_id')->nullable();
                $table->string('stripe_customer_id')->nullable();
                $table->string('stripe_payment_method_id')->nullable();
                $table->string('failure_code', 100)->nullable();
                $table->text('failure_message')->nullable();
                $table->text('requires_action_url')->nullable();
                $table->string('idempotency_key')->unique();
                $table->uuid('retry_of_event_id')->nullable();
                $table->integer('retry_count')->default(0);
                $table->jsonb('metadata')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('processed_at')->nullable();
                $table->timestamp('completed_at')->nullable();

                $table->foreign('account_id')->references('id')->on('accounts');
                $table->foreign('config_id')->references('id')->on('auto_topup_configs');
                $table->index('account_id');
                $table->index(['account_id', 'created_at']);
                $table->index(['account_id', 'status']);
                $table->index('stripe_payment_intent_id');
            });

            Schema::table('auto_topup_events', function (Blueprint $table) {
                $table->foreign('retry_of_event_id')->references('id')->on('auto_topup_events');
            });

            // UUID auto-generation trigger
            DB::unprepared("
                CREATE OR REPLACE FUNCTION generate_uuid_auto_topup_events()
                RETURNS TRIGGER AS \$\$
                BEGIN
                    IF NEW.id IS NULL THEN NEW.id = gen_random_uuid(); END IF;
                    RETURN NEW;
                END;
                \$\$ LANGUAGE plpgsql;

                CREATE TRIGGER before_insert_auto_topup_events_uuid
                BEFORE INSERT ON auto_topup_events
                FOR EACH ROW EXECUTE FUNCTION generate_uuid_auto_topup_events();
            ");

            // RLS policy for tenant isolation
            DB::unprepared("
                ALTER TABLE auto_topup_events ENABLE ROW LEVEL SECURITY;

                CREATE POLICY auto_topup_events_tenant_isolation ON auto_topup_events
                    FOR ALL
                    USING (account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::UUID);
            ");

            // Grants
            DB::unprepared("
                DO \$\$ BEGIN
                    GRANT SELECT, INSERT, UPDATE ON auto_topup_events TO portal_rw;
                    GRANT SELECT ON auto_topup_events TO portal_ro;
                EXCEPTION WHEN undefined_object THEN
                    RAISE NOTICE 'Roles portal_rw/portal_ro do not exist yet, skipping grants';
                END \$\$;
            ");
        }

        // RLS on auto_topup_configs if not already present
        DB::unprepared("
            DO \$\$ BEGIN
                ALTER TABLE auto_topup_configs ENABLE ROW LEVEL SECURITY;
            EXCEPTION WHEN others THEN NULL;
            END \$\$;
        ");

        DB::unprepared("
            DO \$\$ BEGIN
                CREATE POLICY auto_topup_configs_tenant_isolation ON auto_topup_configs
                    FOR ALL
                    USING (account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::UUID);
            EXCEPTION WHEN duplicate_object THEN NULL;
            END \$\$;
        ");

        DB::unprepared("
            DO \$\$ BEGIN
                GRANT SELECT, UPDATE ON auto_topup_configs TO portal_rw;
                GRANT SELECT ON auto_topup_configs TO portal_ro;
            EXCEPTION WHEN undefined_object THEN
                RAISE NOTICE 'Roles portal_rw/portal_ro do not exist yet, skipping grants';
            END \$\$;
        ");
    }

    public function down(): void
    {
        // Drop auto_topup_events
        DB::unprepared("DROP TRIGGER IF EXISTS before_insert_auto_topup_events_uuid ON auto_topup_events");
        DB::unprepared("DROP FUNCTION IF EXISTS generate_uuid_auto_topup_events()");
        DB::unprepared("DROP POLICY IF EXISTS auto_topup_events_tenant_isolation ON auto_topup_events");
        Schema::dropIfExists('auto_topup_events');

        // Remove added columns from auto_topup_configs
        if (Schema::hasTable('auto_topup_configs')) {
            $columns = [
                'daily_topup_cap', 'min_minutes_between_topups',
                'card_brand', 'card_last4', 'card_exp_month', 'card_exp_year',
                'notify_email_success', 'notify_email_failure',
                'notify_inapp_success', 'notify_inapp_failure', 'notify_requires_action',
                'retry_attempts', 'retry_delay_minutes',
                'disable_after_consecutive_failures', 'consecutive_failure_count',
                'last_successful_topup_at',
                'admin_locked', 'admin_locked_reason', 'admin_locked_at', 'admin_locked_by',
                'updated_by_user_id',
            ];

            Schema::table('auto_topup_configs', function (Blueprint $table) use ($columns) {
                foreach ($columns as $col) {
                    if (Schema::hasColumn('auto_topup_configs', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        // Note: stripe_customer_id on accounts is NOT dropped here because it was
        // created by an earlier migration (2026_02_20_000001_add_billing_fields_to_accounts).
        // This migration only adds it idempotently if missing.

        // Remove RLS policy from auto_topup_configs
        DB::unprepared("DROP POLICY IF EXISTS auto_topup_configs_tenant_isolation ON auto_topup_configs");
    }
};
