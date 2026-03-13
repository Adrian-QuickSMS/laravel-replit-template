<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlowNode extends Model
{
    use HasFactory;

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

    /**
     * Get the node type category (trigger, action, logic, end).
     */
    public function getCategory(): string
    {
        $map = [
            'trigger_api' => 'trigger',
            'trigger_webhook' => 'trigger',
            'trigger_sms_inbound' => 'trigger',
            'trigger_rcs_inbound' => 'trigger',
            'trigger_campaign' => 'trigger',
            'trigger_contact_event' => 'trigger',
            'trigger_schedule' => 'trigger',
            'send_message' => 'action',
            'contact' => 'action',
            'tag_action' => 'action',
            'list_action' => 'action',
            'optout_action' => 'action',
            'webhook' => 'action',
            'action_group' => 'action',
            'wait' => 'logic',
            'decision' => 'logic',
            'decision_contact' => 'logic',
            'decision_webhook' => 'logic',
            'inbox_handoff' => 'end',
            'flow_handoff' => 'end',
            'end' => 'end',
        ];

        return $map[$this->type] ?? 'action';
    }
}
