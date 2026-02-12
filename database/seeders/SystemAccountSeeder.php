<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * System Account Seeder
 *
 * Creates the special system account used for sending authentication SMS
 * through QuickSMS platform's own infrastructure
 *
 * SYSTEM ACCOUNT:
 * - ID: 00000000-0000-0000-0000-000000000001
 * - Account Number: SYS-000001
 * - Type: system
 * - Used for: Mobile verification SMS, MFA codes, password resets
 *
 * This account operates outside normal billing and never expires
 */
class SystemAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $systemAccountId = '00000000-0000-0000-0000-000000000001';
        $systemUserId = '00000000-0000-0000-0000-000000000002';

        $exists = DB::table('accounts')
            ->where('id', $systemAccountId)
            ->exists();

        if ($exists) {
            $this->command->info('System account already exists. Skipping...');
            return;
        }

        DB::table('accounts')->insert([
            'id' => $systemAccountId,
            'account_number' => 'SYS-000001',
            'company_name' => 'QuickSMS Platform',
            'phone' => '+44 800 000 0001',
            'country' => 'GB',
            'account_type' => 'system',
            'status' => 'active',
            'email' => 'system@quicksms.internal',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->insert([
            'id' => $systemUserId,
            'tenant_id' => $systemAccountId,
            'user_type' => 'api',
            'email' => 'system@quicksms.internal',
            'password' => bcrypt(Str::random(64)),
            'first_name' => 'System',
            'last_name' => 'Account',
            'role' => 'owner',
            'status' => 'active',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('account_settings')->insert([
            'account_id' => $systemAccountId,
            'timezone' => 'Europe/London',
            'currency' => 'GBP',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('account_flags')->insert([
            'account_id' => $systemAccountId,
            'fraud_risk_level' => 'low',
            'fraud_score' => 0,
            'payment_status' => 'current',
            'daily_message_limit' => 999999,
            'api_rate_limit_per_minute' => 9999,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('System account created successfully!');
        $this->command->info('Account ID: ' . $systemAccountId);
        $this->command->info('Account Number: SYS-000001');
    }
}
