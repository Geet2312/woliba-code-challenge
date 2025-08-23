<?php

namespace Database\Factories;

use App\Models\Invitation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invitation>
 */
class InvitationFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'token' => fake()->unique()->uuid(),
            'token_expires_at' => now()->addMinutes(60),
            'token_used_at' => null,
        ];
    }

    /**
     * Indicate that the invitation has expired.
     */
    public function expired(): static
    {
        return $this->state(fn() => [
            'token_expires_at' => now()->subMinute(),
        ]);
    }

    /**
     * Indicate that the invitation has been used.
     */
    public function used(): static
    {
        return $this->state(fn() => [
            'token_used_at' => now(),
        ]);
    }
}
