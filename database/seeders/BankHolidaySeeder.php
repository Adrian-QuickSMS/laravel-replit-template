<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BankHolidaySeeder extends Seeder
{
    public function run(): void
    {
        $holidays = [
            ['date' => '2026-01-01', 'name' => "New Year's Day"],
            ['date' => '2026-04-03', 'name' => 'Good Friday'],
            ['date' => '2026-04-06', 'name' => 'Easter Monday'],
            ['date' => '2026-05-04', 'name' => 'Early May Bank Holiday'],
            ['date' => '2026-05-25', 'name' => 'Spring Bank Holiday'],
            ['date' => '2026-08-31', 'name' => 'Summer Bank Holiday'],
            ['date' => '2026-12-25', 'name' => 'Christmas Day'],
            ['date' => '2026-12-28', 'name' => 'Boxing Day (substitute)'],

            ['date' => '2027-01-01', 'name' => "New Year's Day"],
            ['date' => '2027-03-26', 'name' => 'Good Friday'],
            ['date' => '2027-03-29', 'name' => 'Easter Monday'],
            ['date' => '2027-05-03', 'name' => 'Early May Bank Holiday'],
            ['date' => '2027-05-31', 'name' => 'Spring Bank Holiday'],
            ['date' => '2027-08-30', 'name' => 'Summer Bank Holiday'],
            ['date' => '2027-12-27', 'name' => 'Christmas Day (substitute)'],
            ['date' => '2027-12-28', 'name' => 'Boxing Day (substitute)'],
        ];

        foreach ($holidays as $holiday) {
            DB::table('bank_holidays')->updateOrInsert(
                ['holiday_date' => $holiday['date']],
                [
                    'id' => Str::uuid()->toString(),
                    'name' => $holiday['name'],
                    'region' => 'england-and-wales',
                    'year' => (int) substr($holiday['date'], 0, 4),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
