<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\Rating;
use App\Models\User;
use App\Repositories\ProductRepository;
use Tests\TestCase;

class CalculateProductRatingsTest extends TestCase
{
     /**
     * Test product average rating calculation.
     */
    public function test_it_calculates_average_product_rating(): void
    {
        $repo = new ProductRepository();

        // Step 1: Create a product
        $product = Product::factory()->create();

        // Step 2: Create 5 users and their ratings
        $ratings = [5, 4, 3, 5, 2]; // Example ratings
        foreach ($ratings as $score) {
            $user = User::factory()->create();

            Rating::factory()->create([
                'product_id' => $product->id,
                'user_id'    => $user->id,
                'rating'     => $score,
            ]);
        }

        // Step 3: Calculate average using repository
        $average = $repo->getProductRatings($product->id); // Should return average

        // Step 4: Assert the average is correct
        $expectedAverage = array_sum($ratings) / count($ratings);
        $this->assertEquals($expectedAverage, $average['average']);
    }
}
