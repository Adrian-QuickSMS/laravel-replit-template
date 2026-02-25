<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // =====================================================
        // 1. Add opt-out configuration columns to campaigns table
        // =====================================================
        Schema::table('campaigns', function (Blueprint $table) {
            $table->boolean('opt_out_enabled')->default(false)->after('metadata');
            $table->string('opt_out_method', 20)->nullable()->after('opt_out_enabled')
                ->comment('reply, url, both');
            $table->uuid('opt_out_number_id')->nullable()->after('opt_out_method')
                ->comment('FK to purchased_numbers — the VMN/shortcode for reply opt-out');
            $table->string('opt_out_keyword', 10)->nullable()->after('opt_out_number_id')
                ->comment('4-10 alphanumeric chars, e.g. STOP, QUIT');
            $table->string('opt_out_text', 500)->nullable()->after('opt_out_keyword')
                ->comment('Text appended to messages, e.g. Reply STOP to +447... to opt out');
            $table->uuid('opt_out_list_id')->nullable()->after('opt_out_text')
                ->comment('FK to opt_out_lists — where to store opt-outs');
            $table->boolean('opt_out_url_enabled')->default(false)->after('opt_out_list_id');
        });

        // =====================================================
        // 2. Campaign opt-out URLs — unique URL per MSISDN per campaign
        // =====================================================
        Schema::create('campaign_opt_out_urls', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('account_id');
            $table->uuid('campaign_id');
            $table->string('mobile_number', 20)->comment('E.164 recipient number');
            $table->string('token', 8)->unique()->comment('Opaque URL token, e.g. Ab3Kf9xZ');
            $table->timestamp('clicked_at')->nullable()->comment('First click timestamp');
            $table->string('click_ip', 45)->nullable()->comment('IP address of first click');
            $table->boolean('unsubscribed')->default(false)->comment('True after landing page confirm');
            $table->timestamp('unsubscribed_at')->nullable();
            $table->string('unsubscribe_ip', 45)->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('account_id')->references('id')->on('accounts');
            $table->foreign('campaign_id')->references('id')->on('campaigns');
            $table->index(['campaign_id', 'mobile_number']);
            $table->index('token');
            $table->index('expires_at');
        });

        // UUID trigger for campaign_opt_out_urls
        DB::unprepared("
            CREATE OR REPLACE FUNCTION generate_uuid_campaign_opt_out_urls()
            RETURNS TRIGGER AS \$\$
            BEGIN
                IF NEW.id IS NULL THEN NEW.id = gen_random_uuid(); END IF;
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;

            CREATE TRIGGER before_insert_campaign_opt_out_urls_uuid
            BEFORE INSERT ON campaign_opt_out_urls
            FOR EACH ROW EXECUTE FUNCTION generate_uuid_campaign_opt_out_urls();
        ");

        // RLS for campaign_opt_out_urls
        DB::unprepared("
            ALTER TABLE campaign_opt_out_urls ENABLE ROW LEVEL SECURITY;

            CREATE POLICY campaign_opt_out_urls_tenant_isolation ON campaign_opt_out_urls
                USING (account_id::text = current_setting('app.current_tenant_id', true));
        ");

        // =====================================================
        // 3. Add campaign_sms_reply and campaign_url_click to opt_out_source ENUM
        // =====================================================
        DB::statement("ALTER TYPE opt_out_source ADD VALUE IF NOT EXISTS 'campaign_sms_reply'");
        DB::statement("ALTER TYPE opt_out_source ADD VALUE IF NOT EXISTS 'campaign_url_click'");

        // =====================================================
        // 4. Database-level unique constraint: one keyword per number per in-flight campaign
        //
        // Prevents two active campaigns from using the same keyword on the same number.
        // Uses a partial unique index filtered to non-terminal campaign statuses.
        // =====================================================
        DB::unprepared("
            CREATE UNIQUE INDEX idx_campaign_opt_out_keyword_inflight
            ON campaigns (opt_out_number_id, opt_out_keyword)
            WHERE opt_out_keyword IS NOT NULL
              AND opt_out_number_id IS NOT NULL
              AND status NOT IN ('completed', 'cancelled', 'failed');
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP INDEX IF EXISTS idx_campaign_opt_out_keyword_inflight");

        DB::unprepared("DROP POLICY IF EXISTS campaign_opt_out_urls_tenant_isolation ON campaign_opt_out_urls");
        DB::unprepared("ALTER TABLE campaign_opt_out_urls DISABLE ROW LEVEL SECURITY");
        DB::unprepared("DROP TRIGGER IF EXISTS before_insert_campaign_opt_out_urls_uuid ON campaign_opt_out_urls");
        DB::unprepared("DROP FUNCTION IF EXISTS generate_uuid_campaign_opt_out_urls()");
        Schema::dropIfExists('campaign_opt_out_urls');

        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn([
                'opt_out_enabled',
                'opt_out_method',
                'opt_out_number_id',
                'opt_out_keyword',
                'opt_out_text',
                'opt_out_list_id',
                'opt_out_url_enabled',
            ]);
        });
    }
};
