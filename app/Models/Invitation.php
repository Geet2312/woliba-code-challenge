<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Invitation extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'token',
        'token_expires_at',
        'token_used_at',
    ];

    public function casts(): array
    {
        return [
            'token_expires_at' => 'datetime',
            'token_used_at' => 'datetime',
        ];
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function isUsed(): bool
    {
        return !is_null($this->token_used_at);
    }

    public function isExpired(): bool
    {
        return $this->token_expires_at->isPast();
    }
    
    public function isPending(): bool
    {
        return !$this->isUsed() && !$this->isExpired();
    }
}
