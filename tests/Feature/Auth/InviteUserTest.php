<?php

use App\Models\Invitation;
use App\Notifications\InvitationEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;

uses(RefreshDatabase::class);

beforeEach(function () {
    Notification::fake();
    config()->set('constants.invitation.token_expiration_minutes', 60);
    // Set up any necessary preconditions here
});

function inviteUser(array $overrides = []): TestResponse
{
    $payload = array_merge([
        'first_name' => fake()->firstName(),
        'last_name' => fake()->lastName(),
        'email' => fake()->unique()->userName() . '@dummy.com',
        'is_magic_link' => true,
    ], $overrides);

    return test()->postJson('/api/invite', $payload);
}

test('sends a magic link invitation email when inviting a new user', function () {

    $firstName = fake()->firstName();
    $lastName = fake()->lastName();
    $email = fake()->unique()->userName() . '@dummy.com';

    $response = inviteUser([
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email,
    ]);

    $response->assertStatus(202);
    $invitation = Invitation::where('email', $email)->first();
    expect($invitation)->not->toBeNull()
        ->and($invitation->first_name)->toBe($firstName)
        ->and($invitation->last_name)->toBe($lastName)
        ->and($invitation->email)->toBe($email);

    Notification::assertSentOnDemand(InvitationEmail::class, function ($notification, $channels, $notifiable) use ($invitation) {
        return $notifiable->routes['mail'] === $invitation->email
            && $notification->invitation->is($invitation);
    });
});

test('invites as user without sending a magic link', function () {
    $firstName = fake()->firstName();
    $lastName = fake()->lastName();
    $email = fake()->unique()->userName() . '@dummy.com';

    $response = inviteUser([
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email,
        'is_magic_link' => false,
    ]);

    $response->assertStatus(202);
    $invitation = Invitation::where('email', $email)->first();
    expect($invitation)->not->toBeNull()
        ->and($invitation->first_name)->toBe($firstName)
        ->and($invitation->last_name)->toBe($lastName)
        ->and($invitation->email)->toBe($email);

    Notification::assertNothingSent();
    
});

test('does not resend invitation if already used', function () {
    $email = fake()->unique()->userName() . '@dummy.com';

    Invitation::create([
        'first_name' => fake()->firstName(),
        'last_name' => fake()->lastName(),
        'email' => $email,
        'token' => Str::uuid()->toString(),
        'token_expires_at' => now()->addMinutes(60),
        'token_used_at' => now(), // Mark as used
    ]);

    $response = inviteUser(['email' => $email]);
    $response->assertStatus(202);

    Notification::assertNothingSent();
});

test('does not resend invitation if token is unused and still valid', function () {
    $email = fake()->unique()->userName() . '@dummy.com';

    Invitation::create([
        'first_name' => fake()->firstName(),
        'last_name' => fake()->lastName(),
        'email' => $email,
        'token' => Str::uuid()->toString(),
        'token_expires_at' => now()->addMinutes(60),
        'token_used_at' => null, // Not used
    ]);

    $response = inviteUser(['email' => $email]);
    $response->assertStatus(202);

    Notification::assertNothingSent();
});

test('resends invitation with new token if previous token expired and is unused', function () {
    
    $newFirstName = fake()->firstName();
    $newLastName = fake()->lastName();
    $email = fake()->unique()->userName() . '@dummy.com';

    $oldInvitation = Invitation::create([
        'first_name' => 'OldFirst',
        'last_name' => 'OldLast',
        'email' => $email,
        'token' => Str::uuid()->toString(),
        'token_expires_at' => now()->subMinutes(10), // Expired
        'token_used_at' => null, // Not used
    ]);

    $response = inviteUser([
        'first_name' => $newFirstName,
        'last_name' => $newLastName,
        'email' => $email,
    ]);
    $response->assertStatus(202);

    $updatedInvitation = Invitation::where('email', $email)->first();
    expect($updatedInvitation)->not->toBeNull()
        ->and($updatedInvitation->id)->toBe($oldInvitation->id) // Same record
        ->and($updatedInvitation->first_name)->toBe($newFirstName)
        ->and($updatedInvitation->last_name)->toBe($newLastName)
        ->and($updatedInvitation->token)->not->toBe($oldInvitation->token)
        ->and($updatedInvitation->token_expires_at->isFuture())->toBeTrue();

    Notification::assertSentOnDemand(InvitationEmail::class, function ($notification, $channels, $notifiable) use ($updatedInvitation) {
        return $notifiable->routes['mail'] === $updatedInvitation->email
            && $notification->invitation->is($updatedInvitation);
    });
});

test('normalizes email addresses by trimming and lowercasing', function () {
    $rawEmail = '   '.strtoupper(fake()->unique()->userName()).'@DUMMY.com   ';
    $normalizedEmail = trim(strtolower($rawEmail));
    
    $response = inviteUser(['email' => $rawEmail]);
    $response->assertStatus(202);
    $invitation = Invitation::where('email', $normalizedEmail)->first();
    expect($invitation)->not->toBeNull()
        ->and($invitation->email)->toBe($normalizedEmail);


    Notification::assertSentOnDemand(InvitationEmail::class, function ($notification, $channels, $notifiable) use ($normalizedEmail) {
        return isset($notifiable->routes['mail'])
            && in_array('mail', $channels, true)
            && $notifiable->routes['mail'] === $normalizedEmail;
    });
});