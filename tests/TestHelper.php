<?php

use App\Models\User;

function jwtHeader(User $user): array
{
    $token = JWTAuth::fromUser($user);

    return [
        'Authorization' => "Bearer {$token}",
        'Accept'        => 'application/json',
        'Content-Type'  => 'application/json',
    ];
}