<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailOtp extends Model
{
    protected $fillable = ['email', 'otp', 'expires_at', 'used_at'];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    /**
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expires_at?->isPast() === true;
    }

    /**
     * @return bool
     */
    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }
}
