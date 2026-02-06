<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class FxRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_currency',
        'to_currency',
        'rate',
        'source',
        'rate_date',
    ];

    protected $casts = [
        'rate' => 'decimal:6',
        'rate_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Get FX rate for date
    public static function getRate($fromCurrency, $toCurrency = 'GBP', $date = null)
    {
        if ($fromCurrency === $toCurrency) {
            return 1.0;
        }

        $date = $date ?? Carbon::now()->format('Y-m-d');

        $fxRate = self::where('from_currency', $fromCurrency)
                      ->where('to_currency', $toCurrency)
                      ->where('rate_date', '<=', $date)
                      ->orderBy('rate_date', 'desc')
                      ->first();

        return $fxRate ? $fxRate->rate : null;
    }

    // Convert amount using FX rate
    public static function convert($amount, $fromCurrency, $toCurrency = 'GBP', $date = null)
    {
        $rate = self::getRate($fromCurrency, $toCurrency, $date);

        if (!$rate) {
            throw new \Exception("FX rate not found for {$fromCurrency} to {$toCurrency}");
        }

        return $amount * $rate;
    }
}
