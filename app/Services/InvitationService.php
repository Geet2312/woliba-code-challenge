<?php

namespace App\Services;

use App\Models\Invitation;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class InvitationService
{

    /**
     * - email isn't found: create new invitation, return shouldSend = true
     * - email is found and used: return shouldSend = false
     * - email is found, not used, not expired: return shouldSend = false
     * - email is found, not used, expired: rotate token, update names, return shouldSend = true
     *
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @return array ['invitation' => Invitation, 'shouldSend' => bool]
     */
    public function refreshOrCreate(string $firstName, string $lastName, string $email): array
    {
        $email = $this->normalizeEmail($email);
        $ttl = (int)config('constants.invitation.token_expiration_minutes', 60);

        $invitation = Invitation::where('email', $email)->first();

        if (!$invitation) {
            $invitation = Invitation::create([
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'token' => Str::uuid()->toString(),
                'token_expires_at' => now()->addMinutes($ttl),
                'token_used_at' => null,
            ]);

            return ['invitation' => $invitation, 'shouldSend' => true];
        }

        // token has been used
        if ($invitation->isUsed()) {
            return ['invitation' => $invitation, 'shouldSend' => false];
        }

        // the token is still valid and unused
        if (!$invitation->isExpired()) {
            return ['invitation' => $invitation, 'shouldSend' => false];
        }
        
        // token expired and unused, rotate it and update names
        $invitation->first_name = $firstName;
        $invitation->last_name = $lastName;
        $invitation->token = Str::uuid()->toString();
        $invitation->token_expires_at = now()->addMinutes($ttl);
        $invitation->save();

        return ['invitation' => $invitation, 'shouldSend' => true];

    }

    /**
     * Generate a temporary signed URL for the invitation.
     */
    public function makeSignedLink(Invitation $invitation): string
    {
        $ttl = (int)config('constants.invitation.token_expiration_minutes', 60);

        return URL::temporarySignedRoute(
            'api.magic-link.user',
            now()->addMinutes($ttl),
            ['token' => $invitation->token]
        );
    }
    
    /**
     * Find a valid (not used, not expired) invitation by its token.
     */
    public function findValidByToken(String $token): ?Invitation
    {
        return Invitation::where('token', $token)
            ->whereNull('token_used_at')
            ->where('token_expires_at', '>', now())
            ->first();
    }

    /**
     * Mark the invitation as used by setting the token_used_at timestamp.
     */
    public function markUsed(Invitation $invitation): void
    {
        $invitation->token_used_at = now();
        $invitation->save();
    }

    /**
     * Find invitation by email 
     * @param string $email
     * @return Invitation|null
     */
    public function findByEmail(string $email): ?Invitation
    {
        return Invitation::where('email', $email)
            ->whereNull('token_used_at')
            ->where('token_expires_at', '>', now())
            ->first();
    }

    /**
     * Normalize email by trimming whitespace and converting to lowercase.
     */
    private function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

}