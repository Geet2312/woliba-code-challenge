<?php

use App\Actions\SetUserWellnessInterestAction;
use App\Models\User;
use App\Models\WellnessInterest;
use App\Services\WellnessInterestService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function jwtHeader(User $user): array
{
    $token = JWTAuth::fromUser($user);

    return [
        'Authorization' => "Bearer $token",
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
    ];
}

function makeInterests(array $names): array
{
    return collect($names)
        ->map(fn(string $name) => WellnessInterest::create(['name' => $name]))
        ->all();
}


test('list all wellness interests', function () {
    makeInterests(['Yoga', 'Meditation', 'Fitness']);

    $user = User::factory()->create();

    $response = test()->getJson('/api/wellness-interests', jwtHeader($user));

    $response->assertOk()
        ->assertJsonStructure(['data' => [['id', 'name']]]);
});

test('list api returns 500 on unexpected error', function () {
    $user = User::factory()->create();

    $mock = Mockery::mock(WellnessInterestService::class);
    $mock->shouldReceive('getInterests')->andThrow(new RuntimeException('boom'));
    app()->instance(WellnessInterestService::class, $mock);

    Log::shouldReceive('error')->once();

    $response = test()->getJson('/api/wellness-interests', jwtHeader($user));

    $response->assertStatus(500)
        ->assertJson(['message' => 'Something went wrong.']);
});

test('returns 401 when missing token', function () {
    test()->postJson('/api/wellness-interests', ['interests' => [1, 2, 3]])
        ->assertStatus(401);

    test()->getJson('/api/wellness-interests')
        ->assertStatus(401);
});

test('save api return 422 with when id does not exist or if payload is not an array', function () {
    $user = User::factory()->create();

    test()->postJson('/api/wellness-interests', ['interests' => 'not-an-array'], headers: jwtHeader($user))
        ->assertStatus(422);

    test()->postJson('/api/wellness-interests', ['interests' => [9999]], headers: jwtHeader($user))
        ->assertStatus(422);
});

test('returns user and interest on successfully save', function () {
    $user = User::factory()->create();

    makeInterests(['Yoga', 'Meditation', 'Fitness']);

    $response = test()->postJson('/api/wellness-interests', [
        'interests' => [1, 2],
    ], headers: jwtHeader($user));

    $response->assertOk()
        ->assertJsonPath('message', 'Wellness interests updated successfully.')
        ->assertJsonStructure([
            'data' => [
                'wellness_interests' => [['id', 'name']],
                'user' => ['first_name', 'last_name', 'email'],
            ],
        ]);
});

test('save api returns 500 on unexpected error', function () {
    $user = User::factory()->create();
    makeInterests(['Yoga', 'Meditation', 'Fitness']);

    $mock = Mockery::mock(SetUserWellnessInterestAction::class);
    $mock->shouldReceive('execute')->andThrow(new RuntimeException('kaboom'));
    app()->instance(SetUserWellnessInterestAction::class, $mock);

    Log::shouldReceive('error')->once();

    test()->postJson('/api/wellness-interests', ['interests' => [1, 2, 3]], headers: jwtHeader($user))
        ->assertStatus(500)
        ->assertJson(['message' => 'Something went wrong.']);
});