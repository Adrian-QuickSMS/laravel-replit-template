<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Flow extends Model
{
    use HasFactory, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'account_id',
        'created_by',
        'name',
        'description',
        'status',
        'version',
        'canvas_meta',
        'last_activated_at',
    ];

    protected $casts = [
        'canvas_meta' => 'array',
        'last_activated_at' => 'datetime',
    ];

    public function nodes()
    {
        return $this->hasMany(FlowNode::class);
    }

    public function connections()
    {
        return $this->hasMany(FlowConnection::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForAccount($query, $accountId)
    {
        return $query->where('account_id', $accountId);
    }
}
