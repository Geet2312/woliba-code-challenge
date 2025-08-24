<?php

use App\Models\User;
use App\Models\WellbeingPillar;
use App\Services\WellbeingPillarService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makePillars(array $names): array
{
    return collect($names)
        ->map(fn(string $name) => WellbeingPillar::create(['name' => $name]))
        ->all();
}

test('list all wellbeing pillars', function () {
    makePillars(['Physical', 'Mental', 'Social']);

    $user = User::factory()->create();

    $response = test()->getJson('/api/wellbeing-pillars', jwtHeader($user));

    $response->assertOk()
        ->assertJsonStructure(['data' => [['id', 'name']]]);
});

test('list api returns 500 on unexpected error', function () {
    $user = User::factory()->create();

    $mock = Mockery::mock(WellbeingPillarService::class);
    $mock->shouldReceive('getPillars')->andThrow(new RuntimeException('exception'));
    app()->instance(WellbeingPillarService::class, $mock);

    Log::shouldReceive('error')->once();

    $response = test()->getJson('/api/wellbeing-pillars', jwtHeader($user));

    $response->assertStatus(500)
        ->assertJson(['message' => 'Something went wrong.']);
});

test('returns 401 when missing token', function () {
    test()->postJson('/api/wellbeing-pillars', ['pillars' => [1, 2, 3]])
        ->assertStatus(401);

    test()->getJson('/api/wellbeing-pillars')
        ->assertStatus(401);
});

test('save api return 422 with when id does not exist or if payload is not an array', function () {
    $user = User::factory()->create();

    test()->postJson('/api/wellbeing-pillars', ['pillars' => 'not-an-array'], jwtHeader($user))
        ->assertStatus(422);

    test()->postJson('/api/wellbeing-pillars', ['pillars' => [9999]], jwtHeader($user))
        ->assertStatus(422);
});

test('save api return 422 with when it does not meet the 3 pillars requirement or when it exceed', function () {
    $user = User::factory()->create();
    makePillars(['Physical', 'Mental', 'Social', 'Emotional']);


    test()->postJson('/api/wellbeing-pillars', ['pillars' => '1'], jwtHeader($user))
        ->assertStatus(422);

    test()->postJson('/api/wellbeing-pillars', ['pillars' => [1, 2, 3, 4]], jwtHeader($user))
        ->assertStatus(422);
});

test('returns user and pillars on successfully save', function () {
    $user = User::factory()->create();
    makePillars(['Physical', 'Mental', 'Social']);

    $response = test()->postJson('/api/wellbeing-pillars', [
        'pillars' => [1, 2, 3],
    ], jwtHeader($user));

    $response->assertOk()
        ->assertJsonPath('message', 'Wellbeing Pillars updated successfully.')
        ->assertJsonStructure([
            'data' => [
                'wellbeing_pillars' => [['id', 'name']],
                'user' => ['first_name', 'last_name', 'email'],
            ],
        ]);
});

test('on successfully save it updates user registration complete', function () {
    $user = User::factory()->create();

    makePillars(['Physical', 'Mental', 'Social']);

    $response = test()->postJson('/api/wellbeing-pillars', [
        'pillars' => [1, 2, 3],
    ], jwtHeader($user));

    $response->assertOk()
        ->assertJsonPath('message', 'Wellbeing Pillars updated successfully.')
        ->assertJsonStructure([
            'data' => [
                'wellbeing_pillars' => [['id', 'name']],
                'user' => ['first_name', 'last_name', 'email', 'registration_complete'],
            ],
        ]);

    $user->refresh();
    expect($user->registration_complete)->toBeTrue();
});

test('save api returns 500 on unexpected error', function () {
    $user = User::factory()->create();
    makePillars(['Physical', 'Mental', 'Social']);

    $mock = Mockery::mock(WellbeingPillarService::class);
    $mock->shouldReceive('setUserPillars')->andThrow(new RuntimeException('exception'));
    app()->instance(WellbeingPillarService::class, $mock);

    Log::shouldReceive('error')->once();

    test()->postJson('/api/wellbeing-pillars', ['pillars' => [1, 2, 3]], jwtHeader($user))
        ->assertStatus(500)
        ->assertJson(['message' => 'Something went wrong.']);
});