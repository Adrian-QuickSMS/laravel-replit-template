<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\AdminUser;

return new class extends Migration
{
    public function up(): void
    {
        $users = [
            [
                'email' => 'admin@quicksms.com',
                'password' => 'QuickSMS2026!',
                'first_name' => 'System',
                'last_name' => 'Administrator',
                'role' => 'super_admin',
                'department' => 'Engineering',
                'status' => 'active',
                'force_password_change' => true,
            ],
            [
                'email' => 'support@quicksms.com',
                'password' => 'QuickSMS2026!',
                'first_name' => 'Support',
                'last_name' => 'Team',
                'role' => 'support',
                'department' => 'Customer Success',
                'status' => 'active',
                'force_password_change' => true,
            ],
            [
                'email' => 'finance@quicksms.com',
                'password' => 'QuickSMS2026!',
                'first_name' => 'Finance',
                'last_name' => 'Team',
                'role' => 'finance',
                'department' => 'Operations',
                'status' => 'active',
                'force_password_change' => true,
            ],
        ];

        foreach ($users as $userData) {
            if (!AdminUser::where('email', $userData['email'])->exists()) {
                AdminUser::create($userData);
            }
        }
    }

    public function down(): void
    {
        AdminUser::whereIn('email', [
            'admin@quicksms.com',
            'support@quicksms.com',
            'finance@quicksms.com',
        ])->forceDelete();
    }
};
