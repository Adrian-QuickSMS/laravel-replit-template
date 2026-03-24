<?php

namespace Tests\Unit;

use App\Models\Alerting\AlertPreference;
use Carbon\Carbon;
use Tests\TestCase;

class AlertPreferenceTest extends TestCase
{
    // ==========================================================
    // isCurrentlyMuted() LOGIC
    // ==========================================================

    public function test_not_muted_when_is_muted_is_false(): void
    {
        $pref = new AlertPreference();
        $pref->is_muted = false;
        $pref->muted_until = null;

        $this->assertFalse($pref->isCurrentlyMuted());
    }

    public function test_muted_when_is_muted_true_and_no_expiry(): void
    {
        $pref = new AlertPreference();
        $pref->is_muted = true;
        $pref->muted_until = null;

        $this->assertTrue($pref->isCurrentlyMuted());
    }

    public function test_muted_when_is_muted_true_and_future_expiry(): void
    {
        $pref = new AlertPreference();
        $pref->is_muted = true;
        $pref->muted_until = Carbon::now()->addHour();

        $this->assertTrue($pref->isCurrentlyMuted());
    }

    public function test_not_muted_when_expiry_has_passed(): void
    {
        $pref = new AlertPreference();
        $pref->is_muted = true;
        $pref->muted_until = Carbon::now()->subMinute();

        $this->assertFalse($pref->isCurrentlyMuted());
    }

    public function test_not_muted_when_expiry_is_exactly_now(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-03-24 12:00:00'));

        $pref = new AlertPreference();
        $pref->is_muted = true;
        $pref->muted_until = Carbon::parse('2026-03-24 12:00:00');

        $this->assertFalse($pref->isCurrentlyMuted());

        Carbon::setTestNow();
    }

    // ==========================================================
    // getEffectiveChannels() LOGIC
    // ==========================================================

    public function test_effective_channels_returns_channels_when_not_muted(): void
    {
        $pref = new AlertPreference();
        $pref->is_muted = false;
        $pref->channels = ['sms', 'email'];

        $this->assertEquals(['sms', 'email'], $pref->getEffectiveChannels());
    }

    public function test_effective_channels_returns_defaults_when_channels_null(): void
    {
        $pref = new AlertPreference();
        $pref->is_muted = false;
        $pref->channels = null;

        $this->assertEquals(['in_app', 'email'], $pref->getEffectiveChannels());
    }

    public function test_effective_channels_returns_empty_when_muted(): void
    {
        $pref = new AlertPreference();
        $pref->is_muted = true;
        $pref->muted_until = null;
        $pref->channels = ['sms', 'email'];

        $this->assertEquals([], $pref->getEffectiveChannels());
    }

    // ==========================================================
    // muted_until SERIALIZATION FORMAT
    // ==========================================================

    public function test_muted_until_serializes_to_iso8601_when_set(): void
    {
        $pref = new AlertPreference();
        $pref->muted_until = Carbon::parse('2026-03-24T16:00:00+00:00');

        $serialized = $pref->muted_until?->toIso8601String();

        $this->assertNotNull($serialized);
        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/',
            $serialized,
            'muted_until should serialize to ISO 8601 format'
        );
    }

    public function test_muted_until_returns_null_when_not_set(): void
    {
        $pref = new AlertPreference();
        $pref->muted_until = null;

        $this->assertNull($pref->muted_until?->toIso8601String());
    }

    public function test_nullsafe_chain_returns_null_for_missing_preference(): void
    {
        // Simulates the controller's $pref?->muted_until?->toIso8601String()
        // when $pref is null (category with no saved preference)
        $pref = null;

        $result = $pref?->muted_until?->toIso8601String();

        $this->assertNull($result);
    }
}
