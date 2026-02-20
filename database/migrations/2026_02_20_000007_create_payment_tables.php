<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("CREATE TYPE payment_method_type AS ENUM (
            'stripe_checkout', 'stripe_auto_topup', 'stripe_dd',
            'bank_transfer', 'credit_note_application'
        )");
        DB::statement("CREATE TYPE payment_status AS ENUM ('pending', 'succeeded', 'failed', 'refunded')");

        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('account_id');
            $table->uuid('invoice_id')->nullable();
            $table->string('stripe_payment_intent_id')->nullable()->unique();
            $table->string('stripe_checkout_session_id')->nullable()->unique();
            $table->string('xero_payment_id')->nullable();
            $table->string('currency', 3)->default('GBP');
            $table->decimal('amount', 12, 4);
            $table->timestamp('paid_at')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts');
            $table->foreign('invoice_id')->references('id')->on('invoices');
            $table->index('account_id');
            $table->index(['account_id', 'created_at']);
        });
        DB::statement("ALTER TABLE payments ADD COLUMN payment_method payment_method_type NOT NULL");
        DB::statement("ALTER TABLE payments ADD COLUMN status payment_status NOT NULL DEFAULT 'pending'");

        Schema::create('processed_stripe_events', function (Blueprint $table) {
            $table->string('event_id', 255)->primary();
            $table->string('event_type', 100);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('auto_topup_configs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('account_id')->unique();
            $table->boolean('enabled')->default(false);
            $table->decimal('threshold_amount', 10, 4)->comment('Trigger when balance drops below');
            $table->decimal('topup_amount', 10, 4)->comment('Amount to charge');
            $table->string('stripe_customer_id');
            $table->string('stripe_payment_method_id')->comment('Stored on Stripe, NOT locally');
            $table->integer('max_topups_per_day')->default(3);
            $table->timestamp('last_triggered_at')->nullable();
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts');
        });

        foreach (['payments', 'auto_topup_configs'] as $tbl) {
            DB::unprepared("
                CREATE OR REPLACE FUNCTION generate_uuid_{$tbl}()
                RETURNS TRIGGER AS \$\$
                BEGIN
                    IF NEW.id IS NULL THEN NEW.id = gen_random_uuid(); END IF;
                    RETURN NEW;
                END;
                \$\$ LANGUAGE plpgsql;

                CREATE TRIGGER before_insert_{$tbl}_uuid
                BEFORE INSERT ON {$tbl}
                FOR EACH ROW EXECUTE FUNCTION generate_uuid_{$tbl}();
            ");
        }
    }

    public function down(): void
    {
        foreach (['auto_topup_configs', 'payments'] as $tbl) {
            DB::unprepared("DROP TRIGGER IF EXISTS before_insert_{$tbl}_uuid ON {$tbl}");
            DB::unprepared("DROP FUNCTION IF EXISTS generate_uuid_{$tbl}()");
        }
        Schema::dropIfExists('auto_topup_configs');
        Schema::dropIfExists('processed_stripe_events');
        Schema::dropIfExists('payments');
        DB::statement("DROP TYPE IF EXISTS payment_status CASCADE");
        DB::statement("DROP TYPE IF EXISTS payment_method_type CASCADE");
    }
};
