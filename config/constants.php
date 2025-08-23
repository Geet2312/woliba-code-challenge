<?php

return [
    'invitation' =>[
        'token_expiration_minutes' => env('INVITATION_TOKEN_EXPIRATION_MINUTES', 60)
    ],
    'email_otp' =>[
        'token_expiration_minutes' => env('EMAIL_OTP_TOKEN_EXPIRATION_MINUTES', 10)
    ]
];