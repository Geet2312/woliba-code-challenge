<?php

namespace App\Services;

use App\Models\EmailOtp;
use App\Models\Invitation;
use App\Notifications\SendEmailOtp;
use Illuminate\Support\Facades\Notification;
use Random\RandomException;

class EmailOtpService
{

    /**
     * @param string $email
     * @return true|null
     * @throws RandomException
     */
    public function generateOtp(string $email): ?true
    {
        $invitation = Invitation::where('email', $email)->first();

        if ($invitation === null) {
            return null;
        }

        $ttl = (int) config('constants.email_otp.token_expiration_minutes');

        $otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        EmailOtp::create([
            'email' => $email,
            'otp' => $otp,
            'expires_at' => now()->addMinutes($ttl),
            'used_at' => null
        ]);

        Notification::route('mail', $email)->notify(new SendEmailOtp($otp, $ttl));

        return true;
    }

}