<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Product;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $loginUserId = User::where('email', 'admin@example.com')->first()?->id ?? 1;  //admin user
        // 2. TEMPORARILY LOG IN the admin system user
        // This ensures Auth::id() returns the system user's ID (e.g., 1)
        Auth::loginUsingId($loginUserId);

        // Step 1: Create 20 products
        $products = Product::factory()
            ->count(20)
            ->sequence(fn($sequence) => [
                'image' => 'images/' . ($sequence->index + 1) . '.jpeg',
            ])
            ->create();

        // Step 2: Create 10 users
        $users = User::factory()->count(10)->create();

        // Step 3: For each user, create payments & ratings for all products
        // foreach ($users as $user) {
        //     foreach ($products as $product) {
        //         // Create a rating
        //         Rating::factory()->create([
        //             'product_id' => $product->id,
        //             'user_id' => $user->id,
        //         ]);

        //         // Create a payment
        //         Payment::factory()->create([
        //             'product_id' => $product->id,
        //             'user_id' => $user->id,
        //         ]);
        //     }
        // }
    }
}
