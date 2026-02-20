<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_credit_wallets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('account_id');
            $table->integer('credits_total')->default(0);
            $table->integer('credits_used')->default(0);
            $table->integer('credits_remaining')->default(0);
            $table->uuid('awarded_by')->nullable();
            $table->string('awarded_reason', 255)->default('signup');
            $table->timestamp('expires_at')->nullable();
            $table->boolean('expired')->default(false);
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts');
            $table->index('account_id');
            $table->index(['account_id', 'expired']);
        });

        Schema::create('test_credit_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('wallet_id');
            $table->uuid('message_log_id')->nullable();
            $table->integer('credits_consumed');
            $table->string('destination_type', 20)->comment('uk or international');
            $table->string('product_type', 20)->comment('sms, rcs_basic, rcs_single');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('wallet_id')->references('id')->on('test_credit_wallets');
            $table->index('wallet_id');
        });

        Schema::create('test_number_allowlist', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('account_id');
            $table->string('mobile_number', 20);
            $table->string('label', 100)->nullable();
            $table->uuid('added_by')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('account_id')->references('id')->on('accounts');
            $table->unique(['account_id', 'mobile_number']);
            $table->index('account_id');
        });

        foreach (['test_credit_wallets', 'test_credit_transactions', 'test_number_allowlist'] as $tbl) {
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
        foreach (['test_number_allowlist', 'test_credit_transactions', 'test_credit_wallets'] as $tbl) {
            DB::unprepared("DROP TRIGGER IF EXISTS before_insert_{$tbl}_uuid ON {$tbl}");
            DB::unprepared("DROP FUNCTION IF EXISTS generate_uuid_{$tbl}()");
            Schema::dropIfExists($tbl);
        }
    }
};
