<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * GREEN SIDE: Customer Users
 *
 * DATA CLASSIFICATION: Confidential - User Authentication
 * SIDE: GREEN (customer accessible via views for own data only)
 * TENANT ISOLATION: Every user belongs to exactly one account (tenant_id)
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'users';

    protected $keyType = 'string';
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = \Illuminate\Support\Str::uuid()->toString();
            }
        });
    }

    protected $fillable = [
        'tenant_id',
        'user_type',
        'email',
        'password',
        'first_name',
        'last_name',
        'job_title',
        'phone',
        'status',
        'role',
        'mfa_enabled',
        'mfa_secret',
        'mfa_recovery_codes',
        'mobile_number',
        'mobile_verified_at',
        'mobile_verification_code',
        'mobile_verification_expires_at',
        'password_changed_at',
        'force_password_change',
        'failed_login_attempts',
        'locked_until',
        'hubspot_contact_id',
        'last_login_at',
        'last_login_ip',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'mfa_secret',
        'mfa_recovery_codes',
        'mobile_verification_code',
    ];

    protected $casts = [
        'id' => 'string',
        'tenant_id' => 'string',
        'email_verified_at' => 'datetime',
        'password' => 'string',
        'mfa_enabled' => 'boolean',
        'phone_verified' => 'boolean',
        'force_password_change' => 'boolean',
        'mobile_verified_at' => 'datetime',
        'mobile_verification_expires_at' => 'datetime',
        'password_changed_at' => 'datetime',
        'locked_until' => 'datetime',
        'last_login_at' => 'datetime',
        'last_hubspot_sync' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'tenant_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(UserSession::class, 'user_id');
    }

    public function passwordHistory(): HasMany
    {
        return $this->hasMany(PasswordHistory::class, 'user_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['owner', 'admin']);
    }

    public function isLocked(): bool
    {
        return $this->locked_until !== null && $this->locked_until->isFuture();
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function hasVerifiedEmail(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function hasMobileVerified(): bool
    {
        return $this->mobile_verified_at !== null;
    }

    public function hasMfaEnabled(): bool
    {
        return (bool) $this->mfa_enabled;
    }

    public function changePassword(string $newPassword): void
    {
        $this->password = $newPassword;
        $this->password_changed_at = now();
        $this->force_password_change = false;
        $this->save();
    }

    public static function normalizeMobileNumber(string $mobile): string
    {
        $mobile = preg_replace('/[^0-9+]/', '', $mobile);

        if (str_starts_with($mobile, '+44')) {
            $mobile = '44' . substr($mobile, 3);
        } elseif (str_starts_with($mobile, '07')) {
            $mobile = '44' . substr($mobile, 1);
        } elseif (str_starts_with($mobile, '7')) {
            $mobile = '44' . $mobile;
        }

        return $mobile;
    }
}
