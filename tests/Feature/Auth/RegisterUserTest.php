<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;


class RegisterUserTest extends TestCase
{
    use WithFaker;

    public $email;
    public $password;
    public $name;

    public function setUp(): void
    {
        parent::setUp();

        $this->email = $this->faker->email;
        $this->password = $this->faker->password;
        $this->name = $this->faker->name;

    }

    /** @test */
    public function it_registers_a_new_user_successfully()
    {


        $payload = [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('users', [
            'email' => $this->email,
        ]);

        $user = User::where('email', $this->email)->first();
        $this->assertTrue(password_verify($this->password, $user->password));
    }

    /** @test */
    public function registration_fails_with_invalid_data()
    {
        // Missing name, invalid email, weak password
        $payload = [
            'name' => '',
            'email' => $this->email,
            'password' => $this->password,
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'The name field is required.',
            ]);
    }
}
