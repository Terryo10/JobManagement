<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        $roles = ['super_admin', 'manager', 'dept_head', 'staff', 'client'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // Super admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@householdmedia.co.zw'],
            [
                'name'      => 'System Admin',
                'password'  => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $admin->assignRole('super_admin');

        // Manager user
        $manager = User::firstOrCreate(
            ['email' => 'manager@householdmedia.co.zw'],
            [
                'name'      => 'Operations Manager',
                'password'  => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $manager->assignRole('manager');

        // Staff user
        $staff = User::firstOrCreate(
            ['email' => 'staff@householdmedia.co.zw'],
            [
                'name'      => 'Field Staff',
                'password'  => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $staff->assignRole('staff');

        // Client user
        $client = User::firstOrCreate(
            ['email' => 'client@example.com'],
            [
                'name'      => 'Demo Client',
                'password'  => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $client->assignRole('client');

        $this->command->info('Roles and seed users created.');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['super_admin', 'admin@householdmedia.co.zw', 'password'],
                ['manager',     'manager@householdmedia.co.zw', 'password'],
                ['staff',       'staff@householdmedia.co.zw', 'password'],
                ['client',      'client@example.com', 'password'],
            ]
        );
    }
}
