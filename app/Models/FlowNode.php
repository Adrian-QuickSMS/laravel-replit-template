<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlowNode extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'flow_id',
        'node_uid',
        'type',
        'label',
        'config',
        'position_x',
        'position_y',
    ];

    protected $casts = [
        'config' => 'array',
        'position_x' => 'float',
        'position_y' => 'float',
    ];

    public function flow()
    {
        return $this->belongsTo(Flow::class);
    }

    public function outgoingConnections()
    {
        return $this->hasMany(FlowConnection::class, 'source_node_uid', 'node_uid')
            ->where('flow_id', $this->flow_id);
    }

    public function incomingConnections()
    {
        return $this->hasMany(FlowConnection::class, 'target_node_uid', 'node_uid')
            ->where('flow_id', $this->flow_id);
    }

    public function getCategory(): string
    {
        $map = [
            'trigger_api' => 'trigger',
            'trigger_sms_keyword' => 'trigger',
            'trigger_rcs_button' => 'trigger',
            'trigger_schedule' => 'trigger',
            'send_message' => 'action',
            'send_sms' => 'action',
            'send_rcs' => 'action',
            'webhook' => 'action',
            'tag' => 'action',
            'wait' => 'logic',
            'decision' => 'logic',
            'inbox_handoff' => 'end',
            'end' => 'end',
        ];

        return $map[$this->type] ?? 'action';
    }
}
