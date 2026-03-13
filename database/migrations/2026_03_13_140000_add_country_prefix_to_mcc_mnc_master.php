<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const ISO_TO_PREFIX = [
        'US' => '1',
        'CA' => '1',
        'GB' => '44',
        'IE' => '353',
        'FR' => '33',
        'DE' => '49',
        'ES' => '34',
        'IT' => '39',
        'NL' => '31',
        'BE' => '32',
        'CH' => '41',
        'AT' => '43',
        'DK' => '45',
        'SE' => '46',
        'NO' => '47',
        'PL' => '48',
        'PT' => '351',
        'FI' => '358',
        'GR' => '30',
        'HU' => '36',
        'CZ' => '420',
        'SK' => '421',
        'RO' => '40',
        'BG' => '359',
        'HR' => '385',
        'SI' => '386',
        'LT' => '370',
        'LV' => '371',
        'EE' => '372',
        'MT' => '356',
        'CY' => '357',
        'LU' => '352',
        'IS' => '354',
        'AE' => '971',
        'SA' => '966',
        'QA' => '974',
        'BH' => '973',
        'OM' => '968',
        'KW' => '965',
        'IL' => '972',
        'TR' => '90',
        'JO' => '962',
        'LB' => '961',
        'IN' => '91',
        'CN' => '86',
        'JP' => '81',
        'KR' => '82',
        'AU' => '61',
        'NZ' => '64',
        'SG' => '65',
        'MY' => '60',
        'TH' => '66',
        'ID' => '62',
        'PH' => '63',
        'VN' => '84',
        'HK' => '852',
        'TW' => '886',
        'BD' => '880',
        'PK' => '92',
        'LK' => '94',
        'ZA' => '27',
        'NG' => '234',
        'KE' => '254',
        'GH' => '233',
        'UG' => '256',
        'TZ' => '255',
        'EG' => '20',
        'MA' => '212',
        'TN' => '216',
        'BR' => '55',
        'MX' => '52',
        'AR' => '54',
        'CL' => '56',
        'CO' => '57',
        'PE' => '51',
    ];

    public function up(): void
    {
        Schema::table('mcc_mnc_master', function (Blueprint $table) {
            $table->string('country_prefix', 10)->nullable()->after('country_iso');
        });

        foreach (self::ISO_TO_PREFIX as $iso => $prefix) {
            DB::table('mcc_mnc_master')
                ->where('country_iso', $iso)
                ->whereNull('country_prefix')
                ->update(['country_prefix' => $prefix]);
        }
    }

    public function down(): void
    {
        Schema::table('mcc_mnc_master', function (Blueprint $table) {
            $table->dropColumn('country_prefix');
        });
    }
};
