<?php

namespace Database\Seeders;

use App\Models\Alerting\AlertRule;
use Illuminate\Database\Seeder;

class AlertDefaultsSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCustomerDefaults();
        $this->seedAdminDefaults();
    }

    private function seedCustomerDefaults(): void
    {
        $defaults = config('alerting.defaults', []);

        foreach ($defaults as $default) {
            AlertRule::updateOrCreate(
                [
                    'trigger_key' => $default['trigger_key'],
                    'is_system_default' => true,
                    'tenant_id' => null, // System-level defaults
                ],
                [
                    'category' => $default['category'],
                    'trigger_type' => $default['trigger_type'],
                    'condition_operator' => $default['condition_operator'],
                    'condition_value' => $default['condition_value'],
                    'channels' => $default['channels'],
                    'frequency' => $default['frequency'],
                    'cooldown_minutes' => $default['cooldown_minutes'],
                    'is_enabled' => true,
                    'is_system_default' => true,
                    'metadata' => [
                        'default_severity' => $default['severity'] ?? 'info',
                        'default_title' => $default['title'] ?? null,
                        'escalation_rules' => $default['escalation_rules'] ?? null,
                    ],
                    'escalation_rules' => $default['escalation_rules'] ?? null,
                ]
            );
        }
    }

    private function seedAdminDefaults(): void
    {
        $defaults = config('alerting.admin_defaults', []);

        foreach ($defaults as $default) {
            AlertRule::updateOrCreate(
                [
                    'trigger_key' => $default['trigger_key'],
                    'is_system_default' => true,
                    'tenant_id' => null,
                    'category' => $default['category'],
                ],
                [
                    'trigger_type' => $default['trigger_type'],
                    'condition_operator' => $default['condition_operator'],
                    'condition_value' => $default['condition_value'],
                    'channels' => $default['channels'],
                    'frequency' => $default['frequency'],
                    'cooldown_minutes' => $default['cooldown_minutes'],
                    'is_enabled' => true,
                    'is_system_default' => true,
                    'metadata' => [
                        'default_severity' => $default['severity'] ?? 'info',
                        'default_title' => $default['title'] ?? null,
                        'is_admin_rule' => true,
                    ],
                ]
            );
        }
    }
}
