<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        $user = User::firstOrCreate(
            ['email' => 'michaeltererai@gmail.com'],
            [
                'name'             => 'Michael Tererai',
                'password'         => Hash::make('password'),
                'phone_number'     => '+27762562799',
                'whatsapp_number'  => '+27762562799',
                'is_active'        => true,
            ]
        );

        $user->assignRole('super_admin');

        $this->command->info("✅  User ready: {$user->email} — role: super_admin");
        $this->command->warn('   Remember to set a strong password after first login.');
    }
}
