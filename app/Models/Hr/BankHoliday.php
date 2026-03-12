<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Builder;

class BankHoliday extends Model
{
    use HasUuids;

    protected $table = 'bank_holidays';

    protected $fillable = [
        'holiday_date',
        'name',
        'region',
        'year',
    ];

    protected $casts = [
        'holiday_date' => 'date',
        'year' => 'integer',
    ];

    public function scopeForYear(Builder $query, int $year): Builder
    {
        return $query->where('year', $year);
    }

    public function scopeForRegion(Builder $query, string $region): Builder
    {
        return $query->where('region', $region);
    }

    public static function datesForYear(int $year, string $region = 'england-and-wales'): array
    {
        return self::forYear($year)
            ->forRegion($region)
            ->pluck('holiday_date')
            ->map(fn ($d) => $d->format('Y-m-d'))
            ->toArray();
    }

    public static function datesBetween(string $start, string $end, string $region = 'england-and-wales'): array
    {
        return self::where('region', $region)
            ->whereBetween('holiday_date', [$start, $end])
            ->pluck('holiday_date')
            ->map(fn ($d) => $d->format('Y-m-d'))
            ->toArray();
    }
}
