<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuarantineRecipient extends Model
{
    public $timestamps = false;

    protected $table = 'quarantine_recipients';

    protected $fillable = [
        'quarantine_message_id',
        'recipient_number',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(QuarantineMessage::class, 'quarantine_message_id');
    }
}
