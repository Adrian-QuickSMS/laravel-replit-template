<?php

namespace App\Services;

class VatService
{
    private const VAT_RATE = 0.20;

    public function calculateVat(float $netAmount, bool $vatApplicable): array
    {
        if (!$vatApplicable) {
            return [
                'net' => round($netAmount, 2),
                'vat_rate' => 0,
                'vat_amount' => 0,
                'total' => round($netAmount, 2),
                'vat_applicable' => false,
            ];
        }

        $vatAmount = $netAmount * self::VAT_RATE;

        return [
            'net' => round($netAmount, 2),
            'vat_rate' => self::VAT_RATE * 100,
            'vat_amount' => round($vatAmount, 2),
            'total' => round($netAmount + $vatAmount, 2),
            'vat_applicable' => true,
        ];
    }

    public function getVatRate(): float
    {
        return self::VAT_RATE;
    }

    public function getVatRatePercentage(): int
    {
        return (int) (self::VAT_RATE * 100);
    }
}
