<?php

namespace Database\Factories;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition()
    {
        return [
            // 'user_id'            => User::factory(),
            // 'product_id'         => Product::factory(),
            'payment_intent_id'  => 'pi_' . $this->faker->uuid(),
            'currency'           => 'NGN',
            'quantity'           => $this->faker->numberBetween(1000, 50000),
            'total'              => $this->faker->numberBetween(1000, 50000),
            'status'             => $this->faker->randomElement(['pending', 'completed', 'failed']),
            'payment_method'     => $this->faker->randomElement(['card', 'paypal', 'bank']),
            'metadata'           => [
                'ip'             => $this->faker->ipv4(),
                'device'         => $this->faker->userAgent(),
                'location'       => $this->faker->country(),
            ],
        ];
    }
}
