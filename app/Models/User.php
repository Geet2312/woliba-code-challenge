<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'dob',
        'contact_number',
        'confirmation_flag',
        'registration_complete',
        'invitation_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'dob' => 'date:Y-m-d',
            'confirmation_flag' => 'boolean',
            'registration_complete' => 'boolean',
        ];
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    public function isRegistrationComplete(): bool
    {
        return $this->registration_complete;
    }

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }

    /**
     * @param $value
     * @return string|null
     */
    public function getDobAttribute($value): ?string
    {
        return $value ? Carbon::parse($value)->format('m/d/Y') : null;
    }

    /**
     * @param $value
     * @return void
     */
    public function setDobAttribute($value): void
    {
        $this->attributes['dob'] = $value ? Carbon::createFromFormat('m/d/Y', $value)->toDateString() : null;
    }

    /**
     * @return BelongsToMany
     */
    public function wellnessInterests(): BelongsToMany
    {
        return $this->belongsToMany(WellnessInterest::class);
    }

    /**
     * @return BelongsToMany
     */
    public function wellbeingPillars(): BelongsToMany
    {
        return $this->belongsToMany(WellbeingPillar::class);
    }
}
