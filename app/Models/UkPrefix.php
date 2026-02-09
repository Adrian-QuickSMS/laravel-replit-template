<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UkPrefix extends Model
{
    use HasFactory;

    protected $table = 'uk_prefixes';

    protected $fillable = [
        'prefix',
        'number_block_raw',
        'status',
        'cp_name',
        'number_length',
        'mcc_mnc_id',
        'match_status',
        'active',
        'allocation_date',
    ];

    protected $casts = [
        'active' => 'boolean',
        'allocation_date' => 'date',
    ];

    public function mccMnc()
    {
        return $this->belongsTo(MccMnc::class, 'mcc_mnc_id');
    }
}
