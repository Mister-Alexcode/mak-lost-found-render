<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@mak.ac.ug'],
            [
                'name'          => 'System Admin',
                'password'      => Hash::make('Admin@1234'),
                'role'          => 'admin',
                'student_id'    => null,
                'phone_number'  => null,
                'reward_points' => 0,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Admin account created:');
        $this->command->info('  Email:    admin@mak.ac.ug');
        $this->command->info('  Password: Admin@1234');
        $this->command->warn('  Change the password after first login!');
    }
}
