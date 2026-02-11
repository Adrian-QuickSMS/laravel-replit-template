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

        // Check if system account already exists
        $exists = DB::table('accounts')
            ->where('id', hex2bin(str_replace('-', '', $systemAccountId)))
            ->exists();

        if ($exists) {
            $this->command->info('System account already exists. Skipping...');
            return;
        }

        // Create System Account
        DB::table('accounts')->insert([
            'id' => hex2bin(str_replace('-', '', $systemAccountId)),
            'account_number' => 'SYS-000001',
            'company_name' => 'QuickSMS Platform',
            'phone' => '+44 800 000 0001',
            'country' => 'GB',
            'account_type' => 'system',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create System User
        DB::table('users')->insert([
            'id' => hex2bin(str_replace('-', '', $systemUserId)),
            'tenant_id' => hex2bin(str_replace('-', '', $systemAccountId)),
            'user_type' => 'system',
            'email' => 'system@quicksms.internal',
            'password' => bcrypt(Str::random(64)), // Random unguessable password
            'first_name' => 'System',
            'last_name' => 'Account',
            'role' => 'system',
            'status' => 'active',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('System account created successfully!');
        $this->command->info('Account ID: ' . $systemAccountId);
        $this->command->info('Account Number: SYS-000001');
    }
}
