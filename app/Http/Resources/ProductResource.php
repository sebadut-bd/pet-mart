<?php

namespace App\Http\Resources;

use App\Services\ImageStorage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $primary = $this->relationLoaded('primaryImage') ? $this->primaryImage?->image_path : null;
        if (! $primary && $this->relationLoaded('images')) {
            $primary = $this->images->first()?->image_path;
        }

        return [
            'id'               => $this->id,
            'title'            => $this->title,
            'slug'             => $this->slug,
            'price'            => $this->price !== null ? (float) $this->price : null,
            'is_negotiable'    => (bool) $this->is_negotiable,
            'is_free'          => (bool) $this->is_free,
            'listing_type'     => $this->listing_type,
            'condition'        => $this->condition,
            'status'           => $this->status,
            'rejection_reason' => $this->when($this->isOwner($request), $this->rejection_reason),
            'primary_image'    => ImageStorage::url($primary),
            'category'         => $this->whenLoaded('category', fn () => [
                'id'   => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ]),
            'location' => [
                'division'     => $this->relationLoaded('division') ? $this->division?->name : null,
                'district'     => $this->relationLoaded('district') ? $this->district?->name : null,
                'area'         => $this->relationLoaded('area') ? $this->area?->name : null,
                'full_address' => $this->full_address,
            ],
            'seller' => $this->whenLoaded('user', fn () => [
                'id'          => $this->user->id,
                'name'        => $this->user->name,
                'is_verified' => (bool) $this->user->is_verified,
            ]),
            'is_featured' => (bool) $this->is_featured,
            'is_urgent'   => (bool) $this->is_urgent,
            'views'       => (int) $this->views,
            'created_at'  => $this->created_at?->toIso8601String(),
            'updated_at'  => $this->updated_at?->toIso8601String(),
        ];
    }

    private function isOwner(Request $request): bool
    {
        $userId = $request->user()?->id ?? auth('sanctum')->id();

        return $userId !== null && (int) $userId === (int) $this->user_id;
    }
}
