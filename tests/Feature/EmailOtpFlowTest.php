<?php

use App\Actions\VerifyEmailOtpAction;
use App\Models\EmailOtp;
use App\Models\Invitation;
use App\Models\User;
use App\Notifications\SendEmailOtp;
use App\Services\EmailOtpService;
use App\Services\InvitationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function invitedEmail(?string $email = null): array
{
    $email = $email ?: fake()->unique()->userName() . '@dummy.com';

    $invitation = Invitation::create([
        'first_name' => fake()->firstName(),
        'last_name' => fake()->lastName(),
        'email' => $email,
        'token' => (string)Str::uuid(),
        'token_expires_at' => now()->addMinutes(60),
        'token_used_at' => null,
    ]);
    return [$email, $invitation];
}

function latestOtpFor(string $email): ?EmailOtp
{
    return EmailOtp::where('email', $email)->latest('id')->first();
}

test('verify-email returns profile data', function () {
    [$email] = invitedEmail();

    $response = test()->getJson('/api/verify-email?email=' . urlencode($email));

    $response->assertStatus(200)
        ->assertJsonPath('data.user.email', $email)
        ->assertJsonStructure(
            [
                'data' => [
                    'user' => ['first_name', 'last_name', 'email']
                ]
            ]);

});

test('verify-email returns 404 with invalid email', function () {
    $email = fake()->unique()->userName() . '@dummy.com';

    $response = test()->getJson('/api/verify-email?email=' . urlencode($email));
    $response->assertStatus(404)
        ->assertJson(['data'=> ['message' => 'No invitation found for the provided email.']]);
});

test('verify-email returns 500 on unexpected error', function () {

    $email = fake()->unique()->userName() . '@dummy.com';

    // Mock the action to throw a runtime exception
    $mock = Mockery::mock(InvitationService::class);
    $mock->shouldReceive('findByEmail')->andThrow(new RuntimeException('exception'));
    app()->instance(InvitationService::class, $mock);

    Log::shouldReceive('error')->once();

    $response = test()->getJson('/api/verify-email?email=' . urlencode($email));

    $response->assertStatus(500)
        ->assertJson(['message' => 'Something went wrong.']);

});

test('send-otp stores otp and sends email for invited email', function () {
    Notification::fake();

    [$email] = invitedEmail();

    $response = test()->getJson('/api/send-otp?email=' . urlencode($email));

    $response->assertStatus(202);

    Notification::assertSentOnDemand(SendEmailOtp::class, function ($notification, $channels, $notifiable) use ($email) {
        return isset($notifiable->routes['mail'])
            && in_array('mail', $channels, true)
            && $notifiable->routes['mail'] === $email;
    });

    expect(latestOtpFor($email))->not->toBeNull();

});

test('send-otp does not send otp for non-invited email', function () {
    Notification::fake();

    $email = fake()->unique()->userName() . '@dummy.com';

    $response = test()->getJson('/api/send-otp?email=' . urlencode($email));

    $response->assertStatus(202);
    Notification::assertNothingSent();

    expect(EmailOtp::where('email', $email)->exists())->toBeFalse();
});

test('send-otp returns 500 on unexpected error', function () {
    $email = fake()->unique()->userName() . '@dummy.com';

    invitedEmail(email: $email);

    $mock = Mockery::mock(EmailOtpService::class);
    $mock->shouldReceive('generateOtp')->andThrow(new RuntimeException('exception'));
    app()->instance(EmailOtpService::class, $mock);

    Log::shouldReceive('error')->once();

    $response = $this->getJson('/api/send-otp?email=' . urlencode($email));

    $response->assertStatus(500)
        ->assertJson(['message' => 'Something went wrong.']);
});

test('verify-otp  finds invitee, burns otp and returns JWT', function () {

    [$email, $invitation] = invitedEmail();

    // Seed OTP record (fresh, unexpired)
    $otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    EmailOtp::create([
        'email' => $email,
        'otp' => $otp,
        'expires_at' => now()->addMinutes(10),
        'used_at' => null,
    ]);

    $response = test()->getJson('/api/verify-otp?email=' . urlencode($email) . '&otp=' . $otp);

    $response->assertOk()
        ->assertJsonStructure([
                'data' => [
                    'user' => ['first_name', 'last_name', 'email'],
                    'token'
                ]
            ]
        )
        ->assertJsonPath('data.user.email', $email);

    // OTP burned
    $record = latestOtpFor($email);
    expect($record?->used_at)->not->toBeNull();

    // User created/linked
    $user = User::where('email', $email)->first();
    expect($user)->not->toBeNull()
        ->and($user->invitation_id)->toBe($invitation->id);
});

test('verify-otp returns 422 for wrong otp', function () {
    [$email] = invitedEmail();

    EmailOtp::create([
        'email' => $email,
        'otp' => '123456',
        'expires_at' => now()->addMinutes(10),
        'used_at' => null,
    ]);

    $response = test()->getJson('/api/verify-otp?email=' . urlencode($email) . '&otp=654321');

    $response->assertStatus(422)
        ->assertJson(['message' => 'Invalid or expired OTP.']);

    $record = latestOtpFor($email);
    expect($record?->used_at)->toBeNull();
});

test('verify-otp returns 422 for expired otp', function () {
    [$email] = invitedEmail();

    EmailOtp::create([
        'email' => $email,
        'otp' => '000111',
        'expires_at' => now()->subMinute(),
        'used_at' => null,
    ]);

    $response = test()->getJson('/api/verify-otp?email=' . urlencode($email) . '&otp=000111');

    $response->assertStatus(422)
        ->assertJson(['message' => 'Invalid or expired OTP.']);

    $record = latestOtpFor($email);
    expect($record?->used_at)->toBeNull();
});

test('verify-otp  return 422 when reused otp', function () {
    [$email] = invitedEmail();

    EmailOtp::create([
        'email' => $email,
        'otp' => '777888',
        'expires_at' => now()->addMinutes(10),
        'used_at' => null,
    ]);


    test()->getJson('/api/verify-otp?email=' . urlencode($email) . '&otp=777888')
        ->assertOk();

    test()->getJson('/api/verify-otp?email=' . urlencode($email) . '&otp=777888')
        ->assertStatus(422)
        ->assertJson(['message' => 'Invalid or expired OTP.']);
});

test('verify-otp returns 500 on unexpected error', function () {

    $email = fake()->unique()->userName() . '@dummy.com';
    $otp = '123456';

    // Mock the action to throw a runtime exception
    $mock = Mockery::mock(VerifyEmailOtpAction::class);
    $mock->shouldReceive('execute')->andThrow(new RuntimeException('exception'));
    app()->instance(VerifyEmailOtpAction::class, $mock);

    Log::shouldReceive('error')->once();

    $response = test()->getJson('/api/verify-otp?email=' . urlencode($email) . "&otp=$otp");

    $response->assertStatus(500)
        ->assertJson(['message' => 'Something went wrong.']);

});