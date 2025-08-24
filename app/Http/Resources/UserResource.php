<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'dob'  => $this->dob,
            'contact_number' => $this->contact_number,
            'confirmation_flag' => $this->confirmation_flag,
            'registration_complete' => $this->registration_complete,
        ];
    }
}
