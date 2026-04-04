<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password'
        ]);

        // Create Bot/Normal Users
        User::factory()
            ->count(1)
            ->user()
            ->create(
                [
                    'password' => 'password'
                ]
            );
    }
}
