<?php

namespace App\Events\Alerting;

class AccountSecuritySettingChanged extends BaseAlertEvent
{
    public function __construct(
        ?string $tenantId,
        protected string $settingType,
        protected array $oldValue,
        protected array $newValue,
        protected ?string $changedBy = null,
        string $severity = 'warning',
        array $metadata = [],
    ) {
        parent::__construct($tenantId, $severity, array_merge($metadata, [
            'setting_type' => $settingType,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'changed_by' => $changedBy,
        ]));
    }

    public function getTriggerKey(): string
    {
        return 'security_setting_changed';
    }

    public function getCategory(): string
    {
        return 'security';
    }

    public function getTitle(): string
    {
        $labels = [
            'retention' => 'Message retention policy',
            'masking' => 'Data masking settings',
            'anti_flood' => 'Anti-flood protection',
            'out_of_hours' => 'Out-of-hours restriction',
        ];

        return sprintf('%s changed', $labels[$this->settingType] ?? 'Security setting');
    }

    public function getBody(): string
    {
        $labels = [
            'retention' => 'Message retention policy has been updated.',
            'masking' => 'Data masking configuration has been updated.',
            'anti_flood' => 'Anti-flood protection settings have been updated.',
            'out_of_hours' => 'Out-of-hours sending restriction has been updated.',
        ];

        $body = $labels[$this->settingType] ?? 'A security setting has been changed.';
        if ($this->changedBy) {
            $body .= sprintf(' Changed by: %s.', $this->changedBy);
        }

        return $body;
    }
}
