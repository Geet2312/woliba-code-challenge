<?php

use App\Actions\VerifyMagicLinkAction;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function signedMagicLink(string $token, int $minutes = 60): string
{
    return URL::temporarySignedRoute(
        'api.magic-link.user',
        now()->addMinutes($minutes),
        ['token' => $token]
    );
}

test('valid token returns limited profile and burns token', function () {

    $firstName = fake()->firstName();
    $lastName = fake()->lastName();
    $email = fake()->unique()->userName() . '@dummy.com';

    $invitation = Invitation::create([
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email,
        'token' => Str::uuid()->toString(),
        'token_expires_at' => now()->addMinutes(60),
        'token_used_at' => null,
    ]);
    
    $link = signedMagicLink($invitation->token);
    $response = test()->getJson($link);
    $response->assertOk()
        ->assertJsonStructure([
            'user' => ['first_name', 'last_name', 'email'],
            'token',
        ])
        ->assertJsonPath('user.email', $email);
    
    $invitation->refresh();
    expect($invitation->token_used_at)->not->toBeNull();
    
    $user = User::where('email', $email)->first();
    expect($user)->not->toBeNull()
        ->and($user->first_name)->toBe($firstName)
        ->and($user->last_name)->toBe($lastName)
        ->and($user->invitation_id)->toBe($invitation->id);
});

test('expire token return 422', function (){
    $firstName = fake()->firstName();
    $lastName = fake()->lastName();
    $email = fake()->unique()->userName() . '@dummy.com';

    $invitation = Invitation::create([
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email,
        'token' => Str::uuid()->toString(),
        'token_expires_at' => now()->subMinutes(),
        'token_used_at' => null,
    ]);

    $link = signedMagicLink($invitation->token);
    
    $response = test()->getJson($link);
    $response->assertStatus(422)
        ->assertJsonPath('message', 'This link is invalid or has expired.');
});

test('used token return 422', function (){
    $firstName = fake()->firstName();
    $lastName = fake()->lastName();
    $email = fake()->unique()->userName() . '@dummy.com';

    $invitation = Invitation::create([
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email,
        'token' => Str::uuid()->toString(),
        'token_expires_at' => now()->addMinutes(60),
        'token_used_at' => now(),
    ]);

    $link = signedMagicLink($invitation->token);
    
    $response = test()->getJson($link);
    $response->assertStatus(422)
        ->assertJsonPath('message', 'This link is invalid or has expired.');
});

test('invalid token return 422', function (){
    $link = signedMagicLink(Str::uuid()->toString());
    
    $response = test()->getJson($link);
    $response->assertStatus(422)
        ->assertJsonPath('message', 'This link is invalid or has expired.');
});

test('unexpected error returns 500 with generic message', function () {

    $url = URL::temporarySignedRoute(
        'api.magic-link.user',
        now()->addMinutes(5),
        ['token' => 'anything']
    );

    // Mock the action to throw a runtime exception
    $mock = Mockery::mock(VerifyMagicLinkAction::class);
    $mock->shouldReceive('execute')->andThrow(new \RuntimeException('boom'));
    app()->instance(VerifyMagicLinkAction::class, $mock);

    Log::shouldReceive('error')->once();

    $this->getJson($url)
        ->assertStatus(500)
        ->assertJson(['message' => 'Something went wrong.']);
});