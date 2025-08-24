<?php

namespace App\Services;

use App\Models\EmailOtp;
use App\Notifications\SendEmailOtp;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Random\RandomException;

class EmailOtpService
{

    /**
     * @param string $email
     * @return bool
     * @throws RandomException
     */
    public function generateOtp(string $email): bool
    {
        $ttl = (int)config('constants.email_otp.token_expiration_minutes');

        $otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // todo - Debug log: temporarily outputs test data to avoid manual DB inspection
        Log::info("Generating OTP for $email", ['OTP' => $otp]);
        
        EmailOtp::create([
            'email' => $email,
            'otp' => $otp,
            'expires_at' => now()->addMinutes($ttl),
            'used_at' => null
        ]);

        Notification::route('mail', $email)->notify(new SendEmailOtp($otp, $ttl));

        return true;
    }

    /**
     * @param string $email
     * @param $otp
     * @return bool
     */
    public function verifyAndBurn(string $email, $otp): bool
    {
        $record = EmailOtp::where('email', $email)
            ->whereNull('used_at')
            ->latest()
            ->first();

        if ($record === null) {
            return false;
        }

        if ($record->isExpired()) {
            return false;
        }
        
        if(!hash_equals((string)$record->otp, (string) $otp)) {
            return false;
        }

        $record->used_at = now();
        $record->save();

        return true;

    }

}