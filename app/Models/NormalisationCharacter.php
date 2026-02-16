<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * RED SIDE: Character equivalence library for normalisation
 *
 * Maps look-alike characters (Cyrillic, leet-speak, homoglyphs) to their
 * canonical Latin/digit form. 36 base characters: A-Z + 0-9.
 *
 * DATA CLASSIFICATION: Internal - Enforcement Configuration
 * SIDE: RED (admin-only)
 */
class NormalisationCharacter extends Model
{
    protected $table = 'normalisation_characters';

    protected $fillable = [
        'base_character',
        'character_type',
        'equivalents',
        'is_active',
        'updated_by',
    ];

    protected $casts = [
        'equivalents' => 'array',
        'is_active' => 'boolean',
    ];

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLetters($query)
    {
        return $query->where('character_type', 'letter');
    }

    public function scopeDigits($query)
    {
        return $query->where('character_type', 'digit');
    }

    // =====================================================
    // HELPERS
    // =====================================================

    /**
     * Convert to the array format used by MessageEnforcementService normalisation.
     */
    public function toLibraryArray(): array
    {
        return [
            'base' => $this->base_character,
            'equivalents' => $this->equivalents ?? [],
            'enabled' => $this->is_active,
        ];
    }
}
