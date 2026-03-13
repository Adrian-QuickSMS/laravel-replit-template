<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountryControlSeeder extends Seeder
{
    public function run(): void
    {
        $countries = DB::table('mcc_mnc_master')
            ->select(
                'country_iso',
                'country_name',
                'country_prefix',
                DB::raw('COUNT(*) as network_count')
            )
            ->whereNotNull('country_iso')
            ->where('country_iso', '!=', '')
            ->groupBy('country_iso', 'country_name', 'country_prefix')
            ->orderBy('country_name')
            ->get();

        $grouped = $countries->groupBy('country_iso');

        foreach ($grouped as $iso => $rows) {
            $first = $rows->first();
            $totalNetworks = $rows->sum('network_count');
            $prefix = $first->country_prefix;

            $existing = DB::table('country_controls')
                ->where('country_iso', $iso)
                ->first();

            if ($existing) {
                DB::table('country_controls')
                    ->where('id', $existing->id)
                    ->update([
                        'network_count' => $totalNetworks,
                        'country_prefix' => $prefix ?: $existing->country_prefix,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('country_controls')->insert([
                    'country_iso' => $iso,
                    'country_name' => $first->country_name,
                    'country_prefix' => $prefix,
                    'default_status' => $iso === 'GB' ? 'allowed' : 'restricted',
                    'risk_level' => 'low',
                    'network_count' => $totalNetworks,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
