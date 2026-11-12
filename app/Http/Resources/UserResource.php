<?php

namespace App\Http\Resources;

use App\Services\ImageStorage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'phone'       => $this->phone,
            'email'       => $this->email,
            'type'        => (int) $this->type,
            'image'       => ImageStorage::url($this->image),
            'bio'         => $this->bio,
            'address'     => $this->address,
            'location_id' => $this->location_id,
            'location'    => $this->whenLoaded('location', fn () => $this->location ? [
                'id'   => $this->location->id,
                'name' => $this->location->name,
            ] : null),
            'is_verified' => (bool) $this->is_verified,
            'status'      => (bool) $this->status,
            'created_at'  => $this->created_at?->toIso8601String(),
        ];
    }
}
