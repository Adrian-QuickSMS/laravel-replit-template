<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * GREEN SIDE: User Invitations
 *
 * DATA CLASSIFICATION: Internal - User Management
 * SIDE: GREEN (customer portal accessible)
 * TENANT ISOLATION: MANDATORY account_id on every row + RLS
 *
 * Flow: Admin creates invitation → record stored with token → event logged
 * → TODO: Connect to email server to send invitation email
 */
class UserInvitation extends Model
{
    protected $table = 'user_invitations';

    protected $keyType = 'string';
    public $incrementing = false;

    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_EXPIRED = 'expired';
    const STATUS_REVOKED = 'revoked';

    const TOKEN_EXPIRY_HOURS = 72;

    protected $fillable = [
        'account_id',
        'sub_account_id',
        'email',
        'first_name',
        'last_name',
        'token',
        'role',
        'sender_capability',
        'permission_toggles',
        'status',
        'expires_at',
        'invited_by',
        'invited_by_name',
        'accepted_at',
        'accepted_user_id',
        'revoked_by',
        'revoked_at',
        'revoke_reason',
    ];

    protected $casts = [
        'id' => 'string',
        'account_id' => 'string',
        'sub_account_id' => 'string',
        'invited_by' => 'string',
        'accepted_user_id' => 'string',
        'permission_toggles' => 'array',
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
        'revoked_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('tenant', function (Builder $builder) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $builder->where('user_invitations.account_id', auth()->user()->tenant_id);
            }
        });
    }

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function subAccount(): BelongsTo
    {
        return $this->belongsTo(SubAccount::class, 'sub_account_id');
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function acceptedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_user_id');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING)
            ->where('expires_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_PENDING)
            ->where('expires_at', '<=', now());
    }

    // =====================================================
    // METHODS
    // =====================================================

    /**
     * Generate a secure invitation token.
     * Returns the raw token (to be included in invitation URL).
     * Stores the SHA-256 hash in the database.
     */
    public static function generateToken(): array
    {
        $rawToken = Str::random(48);
        $hashedToken = hash('sha256', $rawToken);

        return [
            'raw' => $rawToken,
            'hash' => $hashedToken,
        ];
    }

    /**
     * Find an invitation by its raw token.
     */
    public static function findByToken(string $rawToken): ?self
    {
        $hash = hash('sha256', $rawToken);
        return static::withoutGlobalScope('tenant')
            ->where('token', $hash)
            ->first();
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING && $this->expires_at->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_PENDING && $this->expires_at->isPast();
    }

    public function isAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    public function isRevoked(): bool
    {
        return $this->status === self::STATUS_REVOKED;
    }

    /**
     * Accept the invitation and create the user.
     * Returns the newly created user.
     */
    public function accept(string $password): User
    {
        if (!$this->isPending()) {
            throw new \InvalidArgumentException('Invitation is no longer valid');
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($password) {
            // Set PostgreSQL tenant context for RLS — required since this runs without auth
            \Illuminate\Support\Facades\DB::statement(
                "SELECT set_config('app.current_tenant_id', ?, true)",
                [$this->account_id]
            );

            $user = User::withoutGlobalScope('tenant')->create([
                'tenant_id' => $this->account_id,
                'sub_account_id' => $this->sub_account_id,
                'user_type' => 'customer',
                'email' => $this->email,
                'first_name' => $this->first_name ?? '',
                'last_name' => $this->last_name ?? '',
                'role' => $this->role,
                'sender_capability' => $this->sender_capability,
                'permission_toggles' => $this->permission_toggles,
                'status' => 'active',
                'email_verified_at' => now(),
            ]);

            // Set password explicitly — not mass-assignable for security
            $user->password = \Illuminate\Support\Facades\Hash::make($password);
            $user->save();

            $this->update([
                'status' => self::STATUS_ACCEPTED,
                'accepted_at' => now(),
                'accepted_user_id' => $user->id,
            ]);

            return $user;
        });
    }

    /**
     * Revoke the invitation.
     */
    public function revoke(string $revokedBy, ?string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_REVOKED,
            'revoked_by' => $revokedBy,
            'revoked_at' => now(),
            'revoke_reason' => $reason,
        ]);
    }

    public function toPortalArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'role' => $this->role,
            'role_label' => User::ROLE_LABELS[$this->role] ?? ucfirst($this->role),
            'sub_account_id' => $this->sub_account_id,
            'status' => $this->isExpired() ? self::STATUS_EXPIRED : $this->status,
            'invited_by_name' => $this->invited_by_name,
            'expires_at' => $this->expires_at->toIso8601String(),
            'accepted_at' => $this->accepted_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
