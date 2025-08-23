<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

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
            'password' => static::$password ??= Hash::make('password'),
            'dob' => fake()->date(),
            'contact_number' => fake()->phoneNumber(),
            'confirmation_flag' => false,
            'registration_complete' => false,
            'invitation_id' => null,
            'email_verified_at' => now(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model's email address should be verified.
     */
    public function verified(): static
    {
        return $this->state(fn() => [
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Indicate that the user is associated with an invitation.
     */
    public function withInvitation(int $invitationId = null): static
    {
        return $this->state(fn() => [
            'invitation_id' => $invitationId ?? InvitationFactory::factory()->create()->id,
        ]);
    }
}
