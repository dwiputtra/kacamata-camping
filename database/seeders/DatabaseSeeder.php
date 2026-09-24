<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    
    {
                $this->call(CampingDataSeeder::class);
        User::updateOrCreate(
            ['email' => 'admin@kacamata.test'],
            [
                'name' => 'Administrator',
                'phone' => '081234567890',
                'password' => 'password',
                'role' => User::ROLE_ADMIN,
            ]
        );

        User::updateOrCreate(
            ['email' => 'customer@kacamata.test'],
            [
                'name' => 'Pelanggan Contoh',
                'phone' => '081298765432',
                'password' => 'password',
                'role' => User::ROLE_CUSTOMER,
            ]
        );
    }
}