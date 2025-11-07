<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create initial administrator account
        $admin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@sweep.local',
            'password' => Hash::make('ChangeMe123!'),
            'email_verified_at' => now(),
        ]);

        // Assign administrator role
        $admin->assignRole('administrator');

        $this->command->info('Initial administrator account created:');
        $this->command->info('Email: admin@sweep.local');
        $this->command->info('Password: ChangeMe123!');
        $this->command->warn('IMPORTANT: Please change this password immediately after first login!');
    }
}
