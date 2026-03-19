<?php

namespace Tests\Unit;

use App\Events\Alerting\AccountSecuritySettingChanged;
use App\Events\Alerting\AccountStatusOverridden;
use App\Events\Alerting\ApiConnectionStateChanged;
use App\Events\Alerting\IpAllowlistChanged;
use App\Events\Alerting\SpamFilterModeChanged;
use Tests\TestCase;

class SecurityAlertingTest extends TestCase
{
    // ==========================================================
    // TRIGGER KEY / CONFIG CONSISTENCY
    // ==========================================================

    public function test_security_event_trigger_keys_match_config(): void
    {
        $defaults = config('alerting.defaults');
        $configKeys = array_column($defaults, 'trigger_key');

        $events = [
            new AccountSecuritySettingChanged(null, 'retention', [], []),
            new IpAllowlistChanged(null, 'added'),
            new ApiConnectionStateChanged(null, 'conn1', 'active', 'suspended'),
        ];

        foreach ($events as $event) {
            $this->assertContains(
                $event->getTriggerKey(),
                $configKeys,
                "Event trigger_key '{$event->getTriggerKey()}' has no matching config rule"
            );
        }
    }

    public function test_admin_event_trigger_keys_match_config(): void
    {
        $adminDefaults = config('alerting.admin_defaults');
        $configKeys = array_column($adminDefaults, 'trigger_key');

        $events = [
            new AccountStatusOverridden(null, 'active_standard', 'suspended'),
            new SpamFilterModeChanged(null, 'enforced', 'off'),
        ];

        foreach ($events as $event) {
            $this->assertContains(
                $event->getTriggerKey(),
                $configKeys,
                "Admin event trigger_key '{$event->getTriggerKey()}' has no matching config rule"
            );
            $this->assertTrue($event->isAdminAlert(), "{$event->getTriggerKey()} should be admin-only");
        }
    }

    // ==========================================================
    // SEVERITY ESCALATION
    // ==========================================================

    public function test_ip_allowlist_removal_escalates_to_critical(): void
    {
        $event = new IpAllowlistChanged(null, 'removed', '10.0.0.1');
        $this->assertEquals('critical', $event->getSeverity());
    }

    public function test_ip_allowlist_add_stays_warning(): void
    {
        $event = new IpAllowlistChanged(null, 'added', '10.0.0.1');
        $this->assertEquals('warning', $event->getSeverity());
    }

    public function test_ip_allowlist_enabled_stays_warning(): void
    {
        $event = new IpAllowlistChanged(null, 'enabled');
        $this->assertEquals('warning', $event->getSeverity());
        $this->assertNull($event->getMetadata()['ip_address']);
    }

    public function test_api_connection_suspended_escalates_to_critical(): void
    {
        $event = new ApiConnectionStateChanged(null, 'api-v2', 'active', 'suspended');
        $this->assertEquals('critical', $event->getSeverity());
    }

    public function test_api_connection_archived_escalates_to_critical(): void
    {
        $event = new ApiConnectionStateChanged(null, 'api-v2', 'active', 'archived');
        $this->assertEquals('critical', $event->getSeverity());
    }

    public function test_api_connection_reactivated_stays_warning(): void
    {
        $event = new ApiConnectionStateChanged(null, 'api-v2', 'suspended', 'active');
        $this->assertEquals('warning', $event->getSeverity());
    }

    public function test_spam_filter_off_escalates_to_critical(): void
    {
        $event = new SpamFilterModeChanged(null, 'enforced', 'off');
        $this->assertEquals('critical', $event->getSeverity());
    }

    public function test_spam_filter_monitoring_stays_warning(): void
    {
        $event = new SpamFilterModeChanged(null, 'enforced', 'monitoring');
        $this->assertEquals('warning', $event->getSeverity());
    }

    public function test_account_status_override_is_always_critical(): void
    {
        $event = new AccountStatusOverridden(null, 'active_standard', 'suspended', 'Test reason', 'Admin User');
        $this->assertEquals('critical', $event->getSeverity());
        $this->assertStringContainsString('overridden', $event->getTitle());
    }

    // ==========================================================
    // METADATA SHAPE
    // ==========================================================

    public function test_security_setting_changed_metadata_shape(): void
    {
        $event = new AccountSecuritySettingChanged(
            'tenant-123',
            'retention',
            ['message_retention_days' => 180],
            ['message_retention_days' => 90],
            'admin@example.com'
        );

        $meta = $event->getMetadata();
        $this->assertEquals('retention', $meta['setting_type']);
        $this->assertEquals(['message_retention_days' => 180], $meta['old_value']);
        $this->assertEquals(['message_retention_days' => 90], $meta['new_value']);
        $this->assertEquals('admin@example.com', $meta['changed_by']);
    }

    public function test_security_setting_unknown_type_uses_fallback_label(): void
    {
        $event = new AccountSecuritySettingChanged(null, 'unknown_setting', [], []);
        $this->assertEquals('Security setting changed', $event->getTitle());
        $this->assertStringContainsString('security setting has been changed', $event->getBody());
    }

    public function test_ip_allowlist_changed_metadata_includes_remaining_count(): void
    {
        $event = new IpAllowlistChanged(null, 'removed', '10.0.0.1', 'Office', 3, 'admin@test.com');
        $meta = $event->getMetadata();
        $this->assertEquals(3, $meta['entries_remaining']);
        $this->assertEquals('admin@test.com', $meta['changed_by']);
        $this->assertStringContainsString('3 entries remaining', $event->getBody());
    }

    public function test_account_status_overridden_metadata_shape(): void
    {
        $event = new AccountStatusOverridden('tenant-456', 'active_standard', 'suspended', 'Fraud detected', 'Admin Bob');
        $meta = $event->getMetadata();
        $this->assertEquals('tenant-456', $meta['account_id']);
        $this->assertEquals('active_standard', $meta['previous_status']);
        $this->assertEquals('suspended', $meta['new_status']);
        $this->assertEquals('Fraud detected', $meta['reason']);
        $this->assertEquals('Admin Bob', $meta['admin_name']);
    }

    public function test_spam_filter_off_includes_warning_in_body(): void
    {
        $event = new SpamFilterModeChanged(null, 'enforced', 'off', 'Admin User');
        $this->assertStringContainsString('completely disabled', $event->getBody());
    }

    // ==========================================================
    // SUBSCRIBER REGISTRATION COUNT
    // ==========================================================

    public function test_subscriber_has_expected_event_count(): void
    {
        $subscriber = new \App\Listeners\Alerting\AlertEventSubscriber();
        $subscriptions = $subscriber->subscribe(app(\Illuminate\Events\Dispatcher::class));
        // 31 original + 3 new security + 2 new admin + 6 sub-account = 42
        $this->assertGreaterThanOrEqual(42, count($subscriptions), 'Expected at least 42 registered alert events');
    }
}
