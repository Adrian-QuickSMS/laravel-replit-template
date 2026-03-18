<?php

namespace Tests\Unit;

use App\Events\Alerting\SubAccountDailyLimitApproaching;
use App\Events\Alerting\SubAccountDailyLimitBreached;
use App\Events\Alerting\SubAccountSpendCapApproaching;
use App\Events\Alerting\SubAccountSpendCapBreached;
use App\Events\Alerting\SubAccountVolumeCapApproaching;
use App\Events\Alerting\SubAccountVolumeCapBreached;
use App\Models\SubAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class SubAccountAlertingTest extends TestCase
{
    use RefreshDatabase;

    private function makeSubAccount(array $overrides = []): SubAccount
    {
        // Bypass global tenant scope for testing
        return SubAccount::withoutGlobalScopes()->create(array_merge([
            'id' => fake()->uuid(),
            'account_id' => fake()->uuid(),
            'name' => 'Test Sub-Account',
            'is_active' => true,
            'sub_account_status' => SubAccount::STATUS_LIVE,
            'enforcement_type' => SubAccount::ENFORCEMENT_WARN,
            'hard_stop_enabled' => false,
            'monthly_spending_cap' => null,
            'monthly_message_cap' => null,
            'daily_send_limit' => null,
            'monthly_spend_used' => 0,
            'monthly_messages_used' => 0,
            'daily_sends_used' => 0,
        ], $overrides));
    }

    // ==========================================================
    // SPEND CAP BREACH — fires exactly once on transition
    // ==========================================================

    public function test_spend_cap_breached_fires_on_transition(): void
    {
        Event::fake([SubAccountSpendCapBreached::class, SubAccountSpendCapApproaching::class]);

        $sub = $this->makeSubAccount([
            'monthly_spending_cap' => 100.00,
            'monthly_spend_used' => 99.50,
            'monthly_usage_reset_date' => today()->startOfMonth()->toDateString(),
        ]);

        $sub->recordMessageSent(1, 1.00);

        Event::assertDispatched(SubAccountSpendCapBreached::class, 1);
    }

    public function test_spend_cap_breached_does_not_fire_when_already_exceeded(): void
    {
        Event::fake([SubAccountSpendCapBreached::class, SubAccountSpendCapApproaching::class]);

        $sub = $this->makeSubAccount([
            'monthly_spending_cap' => 100.00,
            'monthly_spend_used' => 105.00, // already exceeded
            'monthly_usage_reset_date' => today()->startOfMonth()->toDateString(),
        ]);

        $sub->recordMessageSent(1, 1.00);

        Event::assertNotDispatched(SubAccountSpendCapBreached::class);
    }

    // ==========================================================
    // SPEND CAP APPROACHING — fires exactly once on transition
    // ==========================================================

    public function test_spend_cap_approaching_fires_on_threshold_crossing(): void
    {
        Event::fake([SubAccountSpendCapBreached::class, SubAccountSpendCapApproaching::class]);

        $sub = $this->makeSubAccount([
            'monthly_spending_cap' => 100.00,
            'monthly_spend_used' => 79.50, // below 80%
            'monthly_usage_reset_date' => today()->startOfMonth()->toDateString(),
        ]);

        $sub->recordMessageSent(1, 1.00); // now 80.50 — crosses threshold

        Event::assertDispatched(SubAccountSpendCapApproaching::class, 1);
        Event::assertNotDispatched(SubAccountSpendCapBreached::class);
    }

    public function test_spend_cap_approaching_does_not_fire_when_already_in_approaching_zone(): void
    {
        Event::fake([SubAccountSpendCapBreached::class, SubAccountSpendCapApproaching::class]);

        $sub = $this->makeSubAccount([
            'monthly_spending_cap' => 100.00,
            'monthly_spend_used' => 85.00, // already above 80%
            'monthly_usage_reset_date' => today()->startOfMonth()->toDateString(),
        ]);

        $sub->recordMessageSent(1, 1.00); // 86.00 — still approaching but not new

        Event::assertNotDispatched(SubAccountSpendCapApproaching::class);
    }

    // ==========================================================
    // VOLUME (MESSAGE) CAP
    // ==========================================================

    public function test_volume_cap_breached_fires_on_transition(): void
    {
        Event::fake([SubAccountVolumeCapBreached::class, SubAccountVolumeCapApproaching::class]);

        $sub = $this->makeSubAccount([
            'monthly_message_cap' => 100,
            'monthly_messages_used' => 99,
            'monthly_usage_reset_date' => today()->startOfMonth()->toDateString(),
        ]);

        $sub->recordMessageSent(1, 0.50);

        Event::assertDispatched(SubAccountVolumeCapBreached::class, 1);
    }

    public function test_volume_cap_breached_does_not_fire_when_already_exceeded(): void
    {
        Event::fake([SubAccountVolumeCapBreached::class, SubAccountVolumeCapApproaching::class]);

        $sub = $this->makeSubAccount([
            'monthly_message_cap' => 100,
            'monthly_messages_used' => 110,
            'monthly_usage_reset_date' => today()->startOfMonth()->toDateString(),
        ]);

        $sub->recordMessageSent(1, 0.50);

        Event::assertNotDispatched(SubAccountVolumeCapBreached::class);
    }

    public function test_volume_cap_approaching_fires_on_threshold_crossing(): void
    {
        Event::fake([SubAccountVolumeCapBreached::class, SubAccountVolumeCapApproaching::class]);

        $sub = $this->makeSubAccount([
            'monthly_message_cap' => 100,
            'monthly_messages_used' => 79, // below 80%
            'monthly_usage_reset_date' => today()->startOfMonth()->toDateString(),
        ]);

        $sub->recordMessageSent(1, 0.50); // now 80

        Event::assertDispatched(SubAccountVolumeCapApproaching::class, 1);
    }

    public function test_volume_cap_approaching_does_not_fire_when_already_approaching(): void
    {
        Event::fake([SubAccountVolumeCapBreached::class, SubAccountVolumeCapApproaching::class]);

        $sub = $this->makeSubAccount([
            'monthly_message_cap' => 100,
            'monthly_messages_used' => 85,
            'monthly_usage_reset_date' => today()->startOfMonth()->toDateString(),
        ]);

        $sub->recordMessageSent(1, 0.50);

        Event::assertNotDispatched(SubAccountVolumeCapApproaching::class);
    }

    // ==========================================================
    // DAILY SEND LIMIT
    // ==========================================================

    public function test_daily_limit_breached_fires_on_transition(): void
    {
        Event::fake([SubAccountDailyLimitBreached::class, SubAccountDailyLimitApproaching::class]);

        $sub = $this->makeSubAccount([
            'daily_send_limit' => 50,
            'daily_sends_used' => 49,
            'daily_sends_reset_date' => today()->toDateString(),
        ]);

        $sub->recordMessageSent(1, 0.50);

        Event::assertDispatched(SubAccountDailyLimitBreached::class, 1);
    }

    public function test_daily_limit_breached_does_not_fire_when_already_exceeded(): void
    {
        Event::fake([SubAccountDailyLimitBreached::class, SubAccountDailyLimitApproaching::class]);

        $sub = $this->makeSubAccount([
            'daily_send_limit' => 50,
            'daily_sends_used' => 55,
            'daily_sends_reset_date' => today()->toDateString(),
        ]);

        $sub->recordMessageSent(1, 0.50);

        Event::assertNotDispatched(SubAccountDailyLimitBreached::class);
    }

    public function test_daily_limit_approaching_fires_on_threshold_crossing(): void
    {
        Event::fake([SubAccountDailyLimitBreached::class, SubAccountDailyLimitApproaching::class]);

        $sub = $this->makeSubAccount([
            'daily_send_limit' => 50,
            'daily_sends_used' => 39, // below 80% (40)
            'daily_sends_reset_date' => today()->toDateString(),
        ]);

        $sub->recordMessageSent(1, 0.50); // now 40 = 80%

        Event::assertDispatched(SubAccountDailyLimitApproaching::class, 1);
    }

    public function test_daily_limit_approaching_does_not_fire_when_already_approaching(): void
    {
        Event::fake([SubAccountDailyLimitBreached::class, SubAccountDailyLimitApproaching::class]);

        $sub = $this->makeSubAccount([
            'daily_send_limit' => 50,
            'daily_sends_used' => 42,
            'daily_sends_reset_date' => today()->toDateString(),
        ]);

        $sub->recordMessageSent(1, 0.50);

        Event::assertNotDispatched(SubAccountDailyLimitApproaching::class);
    }

    // ==========================================================
    // EDGE CASES
    // ==========================================================

    public function test_no_events_when_caps_are_null(): void
    {
        Event::fake();

        $sub = $this->makeSubAccount([
            'monthly_spending_cap' => null,
            'monthly_message_cap' => null,
            'daily_send_limit' => null,
        ]);

        $sub->recordMessageSent(1, 1.00);

        Event::assertNotDispatched(SubAccountSpendCapBreached::class);
        Event::assertNotDispatched(SubAccountSpendCapApproaching::class);
        Event::assertNotDispatched(SubAccountVolumeCapBreached::class);
        Event::assertNotDispatched(SubAccountVolumeCapApproaching::class);
        Event::assertNotDispatched(SubAccountDailyLimitBreached::class);
        Event::assertNotDispatched(SubAccountDailyLimitApproaching::class);
    }

    public function test_hard_stop_sets_severity_to_critical(): void
    {
        Event::fake([SubAccountSpendCapBreached::class]);

        $sub = $this->makeSubAccount([
            'monthly_spending_cap' => 100.00,
            'monthly_spend_used' => 99.50,
            'hard_stop_enabled' => true,
            'monthly_usage_reset_date' => today()->startOfMonth()->toDateString(),
        ]);

        $sub->recordMessageSent(1, 1.00);

        Event::assertDispatched(SubAccountSpendCapBreached::class, function ($event) {
            return $event->getSeverity() === 'critical'
                && str_contains($event->getTitle(), 'hard-stopped');
        });
    }

    public function test_no_hard_stop_sets_severity_to_warning(): void
    {
        Event::fake([SubAccountSpendCapBreached::class]);

        $sub = $this->makeSubAccount([
            'monthly_spending_cap' => 100.00,
            'monthly_spend_used' => 99.50,
            'hard_stop_enabled' => false,
            'monthly_usage_reset_date' => today()->startOfMonth()->toDateString(),
        ]);

        $sub->recordMessageSent(1, 1.00);

        Event::assertDispatched(SubAccountSpendCapBreached::class, function ($event) {
            return $event->getSeverity() === 'warning'
                && str_contains($event->getTitle(), 'spend cap reached');
        });
    }

    public function test_daily_counter_reset_does_not_false_trigger_breach(): void
    {
        Event::fake([SubAccountDailyLimitBreached::class, SubAccountDailyLimitApproaching::class]);

        // Sub-account was at 49/50 yesterday, day rolls over, counter resets to 1
        $sub = $this->makeSubAccount([
            'daily_send_limit' => 50,
            'daily_sends_used' => 49,
            'daily_sends_reset_date' => today()->subDay()->toDateString(), // yesterday — will reset
        ]);

        $sub->recordMessageSent(1, 0.50);

        // Counter reset to 1, nowhere near breach or approaching
        Event::assertNotDispatched(SubAccountDailyLimitBreached::class);
        Event::assertNotDispatched(SubAccountDailyLimitApproaching::class);
    }

    public function test_event_trigger_key_matches_config_rule(): void
    {
        // Verify trigger keys are consistent between events and config defaults
        $defaults = config('alerting.defaults');
        $subAccountRules = array_filter($defaults, fn ($r) => $r['category'] === 'sub_account');
        $configKeys = array_column($subAccountRules, 'trigger_key');

        $eventKeys = [
            (new SubAccountSpendCapApproaching(null, 'x', 'x', 0, 1))->getTriggerKey(),
            (new SubAccountSpendCapBreached(null, 'x', 'x', 0, 1, false))->getTriggerKey(),
            (new SubAccountVolumeCapApproaching(null, 'x', 'x', 0, 1))->getTriggerKey(),
            (new SubAccountVolumeCapBreached(null, 'x', 'x', 0, 1, false))->getTriggerKey(),
            (new SubAccountDailyLimitApproaching(null, 'x', 'x', 0, 1))->getTriggerKey(),
            (new SubAccountDailyLimitBreached(null, 'x', 'x', 0, 1, false))->getTriggerKey(),
        ];

        foreach ($eventKeys as $key) {
            $this->assertContains($key, $configKeys, "Event trigger_key '{$key}' has no matching config rule");
        }
    }

    public function test_get_trigger_value_returns_correct_percentage(): void
    {
        $event = new SubAccountSpendCapBreached(null, 'x', 'Test', 75.0, 100.0, false);
        $this->assertEquals(75.0, $event->getTriggerValue());

        $event = new SubAccountVolumeCapBreached(null, 'x', 'Test', 50, 200, false);
        $this->assertEquals(25.0, $event->getTriggerValue());

        // Zero cap — fallback to 100
        $event = new SubAccountSpendCapBreached(null, 'x', 'Test', 50.0, 0.0, false);
        $this->assertEquals(100.0, $event->getTriggerValue());
    }
}
