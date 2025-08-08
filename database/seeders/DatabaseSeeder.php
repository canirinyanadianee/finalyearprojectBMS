<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create default admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@bms.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // You can add more seeders here if needed
        // $this->call([
        //     DonorSeeder::class,
        //     HospitalSeeder::class,
        //     BloodBankSeeder::class,
        // ]);
    }
}
