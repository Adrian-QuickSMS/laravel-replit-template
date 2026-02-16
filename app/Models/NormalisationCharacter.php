<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NormalisationCharacter extends Model
{
    protected $table = 'normalisation_characters';

    protected $fillable = [
        'base_character',
        'character_type',
        'equivalents',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'equivalents' => 'array',
    ];

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

    public function toLibraryArray(): array
    {
        return [
            'base' => $this->base_character,
            'equivalents' => $this->equivalents ?? [],
            'enabled' => $this->is_active,
        ];
    }
}
