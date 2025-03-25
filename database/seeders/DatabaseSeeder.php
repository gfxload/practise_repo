<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Service;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
            'is_active' => true,
            'points' => 1000,
        ]);

        // Create regular user
        User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'is_active' => true,
            'points' => 100,
        ]);

        // Create some services
        Service::create([
            'name' => 'Basic Download',
            'description' => 'Basic file download service',
            'points_cost' => 10,
            'is_active' => true,
        ]);

        Service::create([
            'name' => 'Premium Download',
            'description' => 'Premium file download service with higher speed',
            'points_cost' => 20,
            'is_active' => true,
        ]);
    }
}
