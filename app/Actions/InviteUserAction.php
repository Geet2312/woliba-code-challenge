<?php

namespace App\Actions;

use App\Notifications\InvitationEmail;
use App\Services\InvitationService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

class InviteUserAction
{
    public function __construct(private readonly InvitationService $service)
    {
    }

    /**
     * Sends an invitation email if shouldSend is true.
     *
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     */
    public function execute(string $firstName, string $lastName, string $email): void
    {
        try {
            ['invitation' => $invitation, 'shouldSend' => $shouldSend] = $this->service->refreshOrCreate($firstName, $lastName, $email);

            if ($shouldSend) {
                $signedLink = $this->service->makeSignedLink($invitation);

                // Route to invitee's email
                Notification::route('mail', $invitation->email)
                    ->notify(new InvitationEmail($invitation, $signedLink));
            }
        } catch (QueryException $e) {
            Log::error('Database error during invitation process: ' . $e->getMessage());
        } catch (Throwable $e) {
            Log::error('Unexpected error during invitation process: ' . $e->getMessage());
        }

    }
}