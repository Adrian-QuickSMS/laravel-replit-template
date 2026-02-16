<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * RED SIDE: Individual recipients for quarantined messages
 *
 * Separated from quarantine_messages to handle campaigns with
 * thousands of recipients without bloating the main review table.
 *
 * DATA CLASSIFICATION: Internal - Message Metadata
 * SIDE: RED (admin-only)
 */
class QuarantineRecipient extends Model
{
    protected $table = 'quarantine_recipients';

    public $timestamps = false;

    protected $fillable = [
        'quarantine_message_id',
        'recipient_number',
    ];

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    public function quarantineMessage(): BelongsTo
    {
        return $this->belongsTo(QuarantineMessage::class, 'quarantine_message_id');
    }
}
