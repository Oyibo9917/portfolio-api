<?php

namespace Database\Factories;

use App\Enums\ProductCategories;
use App\Enums\InventoryStatus;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
       return [
            'code' => Str::upper(Str::random(10)), // Generate a random 10-character code
            'name' => fake()->name() . ' Watch', // Generate a product name
            'description' => fake()->sentence(10), // Fake description
            'image' => 'images/' . Str::random(20) . '.jpg',
            'price' => fake()->numberBetween(200, 500), // Random price between 20 and 100
            'category' => fake()->randomElement(ProductCategories::cases()),
            'quantity' => fake()->numberBetween(1, 50), // Random quantity
            'inventory_status' => fake()->randomElement(InventoryStatus::cases()),
        ];
    }
}
