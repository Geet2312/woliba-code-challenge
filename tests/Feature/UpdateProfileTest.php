<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);


test('user can update profile with valid token', function () {
    $user = User::factory()->create();

    $password = fake()->password();

    $payload = [
        'password' => $password,
        'password_confirmation' => $password,
        'dob' => '08/22/2025',
        'contact_number' => '1234567890',
        'confirmation_flag' => true,
    ];

    $response = $this->withHeaders(jwtHeader($user))
        ->putJson('/api/user/profile', $payload);

    $response->assertStatus(200)
        ->assertJsonFragment([
            'dob' => '08/22/2025',
            'contact_number' => '1234567890',
            'confirmation_flag' => true,
        ]);

});

test('user cannot update profile with invalid token', function () {
    $user = User::factory()->create();

    $payload = [
        'password' => fake()->password(),
        'password_confirmation' => fake()->password(),
        'dob' => '08/22/2025',
        'contact_number' => '1234567890',
        'confirmation_flag' => true,
    ];

    $response = $this->withHeaders([
        'Authorization' => 'Bearer invalidtoken',
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
    ])->putJson('/api/user/profile', $payload);

    $response->assertStatus(401);
});

test('user cannot update profile with missing token', function () {
    $user = User::factory()->create();

    $payload = [
        'password' => fake()->password(),
        'password_confirmation' => fake()->password(),
        'dob' => '08/22/2025',
        'contact_number' => '1234567890',
        'confirmation_flag' => true,
    ];

    $response = $this->withHeaders([
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
    ])->putJson('/api/user/profile', $payload);

    $response->assertStatus(401);
});

test('user cannot update profile with invalid data', function () {
    $user = User::factory()->create();

    $payload = [
        'password' => 'short',
        'password_confirmation' => 'short',
        'dob' => 'invalid-date',
        'contact_number' => '1234567890',
        'confirmation_flag' => true,
    ];

    $response = $this->withHeaders(jwtHeader($user))
        ->putJson('/api/user/profile', $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password', 'dob']);
});

test('user cannot update profile with mismatched password confirmation', function () {
    $user = User::factory()->create();

    $payload = [
        'password' => 'validPassword123',
        'password_confirmation' => 'differentPassword123',
        'dob' => '08/22/2025',
        'contact_number' => '1234567890',
        'confirmation_flag' => true,
    ];

    $response = $this->withHeaders(jwtHeader($user))
        ->putJson('/api/user/profile', $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('user dob must be a valid date format', function () {
    $user = User::factory()->create();

    $payload = [
        'password' => fake()->password(),
        'password_confirmation' => fake()->password(),
        'dob' => '31/31/2025', // Invalid date
        'contact_number' => '1234567890',
        'confirmation_flag' => true,
    ];

    $response = $this->withHeaders(jwtHeader($user))
        ->putJson('/api/user/profile', $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['dob']);
});

test('user password is hashed when updated', function () {
    $user = User::factory()->create();

    $newPassword = 'NewPassword123';
    $payload = [
        'password' => $newPassword,
        'password_confirmation' => $newPassword,
        'dob' => '08/22/2025',
        'contact_number' => '1234567890',
        'confirmation_flag' => true,
    ];
    $this->withHeaders(jwtHeader($user))->putJson('/api/user/profile', $payload);

    $user->refresh();

    expect(Hash::check($newPassword, $user->password))->toBeTrue();

});
    