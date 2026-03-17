<?php

namespace App\Models\Alerting;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AlertChannelConfig extends Model
{
    use HasUuids;

    protected $table = 'alert_channel_configs';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'channel',
        'config',
        'is_enabled',
    ];

    protected $casts = [
        'config' => 'array',
        'is_enabled' => 'boolean',
    ];

    protected $hidden = [
        'config', // Contains sensitive data like webhook URLs and HMAC secrets
    ];

    // --- Scopes ---

    public function scopeForTenant(Builder $query, string $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeForChannel(Builder $query, string $channel): Builder
    {
        return $query->where('channel', $channel);
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    // --- Accessors ---

    public function getWebhookUrlAttribute(): ?string
    {
        return $this->config['webhook_url'] ?? null;
    }

    public function getHmacSecretAttribute(): ?string
    {
        return $this->config['hmac_secret'] ?? null;
    }

    public function getSlackWebhookUrlAttribute(): ?string
    {
        return $this->config['slack_webhook_url'] ?? null;
    }

    public function getTeamsWebhookUrlAttribute(): ?string
    {
        return $this->config['teams_webhook_url'] ?? null;
    }

    public function getSafeConfigAttribute(): array
    {
        $safe = $this->config ?? [];
        // Mask sensitive values
        if (isset($safe['hmac_secret'])) {
            $safe['hmac_secret'] = '***' . substr($safe['hmac_secret'], -4);
        }
        if (isset($safe['webhook_url'])) {
            $safe['webhook_url_set'] = true;
            unset($safe['webhook_url']);
        }
        return $safe;
    }
}
