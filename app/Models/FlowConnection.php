<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlowConnection extends Model
{
    use HasFactory;

    protected $fillable = [
        'flow_id',
        'source_node_uid',
        'target_node_uid',
        'source_handle',
        'label',
    ];

    public function flow()
    {
        return $this->belongsTo(Flow::class);
    }
}
