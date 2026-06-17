<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds ready-to-use accounts so the deployed app can be tested
 * immediately without going through registration/OTP.
 *
 *   Admin:  admin@mak.ac.ug / Admin@1234
 *   User 1: john@mak.ac.ug  / Password@123
 *   User 2: mary@mak.ac.ug  / Password@123
 *
 * Idempotent (updateOrCreate keyed on email) — safe to run on every deploy.
 */
class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $accounts = [
            [
                'name'         => 'System Admin',
                'email'        => 'admin@mak.ac.ug',
                'password'     => 'Admin@1234',
                'role'         => 'admin',
                'phone_number' => '+256770000001',
                'student_id'   => null,
            ],
            [
                'name'         => 'John Okello',
                'email'        => 'john@mak.ac.ug',
                'password'     => 'Password@123',
                'role'         => 'user',
                'phone_number' => '+256771000002',
                'student_id'   => '2200100001',
            ],
            [
                'name'         => 'Mary Nakato',
                'email'        => 'mary@mak.ac.ug',
                'password'     => 'Password@123',
                'role'         => 'user',
                'phone_number' => '+256772000003',
                'student_id'   => '2200100002',
            ],
        ];

        foreach ($accounts as $a) {
            User::updateOrCreate(
                ['email' => $a['email']],
                [
                    'name'                     => $a['name'],
                    'password'                 => Hash::make($a['password']),
                    'role'                     => $a['role'],
                    'phone_number'             => $a['phone_number'],
                    'student_id'               => $a['student_id'],
                    'reward_points'            => 0,
                    'is_blocked'               => false,
                    'email_verified_at'        => $now,
                    'phone_verified_at'        => $now,
                    'notification_preferences' => ['email' => true, 'whatsapp' => true],
                ]
            );
        }

        $this->command->info('Demo accounts ready:');
        $this->command->info('  Admin:  admin@mak.ac.ug / Admin@1234');
        $this->command->info('  User 1: john@mak.ac.ug  / Password@123');
        $this->command->info('  User 2: mary@mak.ac.ug  / Password@123');
    }
}
