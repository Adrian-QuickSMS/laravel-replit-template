<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApiCredential extends Model
{
    use HasFactory, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'account_id',
        'name',
        'auth_type',
        'credentials',
        'description',
        'last_used_at',
        'created_by',
    ];

    protected $casts = [
        'credentials' => 'encrypted:array',
        'last_used_at' => 'datetime',
    ];

    /**
     * Never include raw credentials in serialization.
     */
    protected $hidden = [
        'credentials',
    ];

    public function scopeForAccount($query, $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
