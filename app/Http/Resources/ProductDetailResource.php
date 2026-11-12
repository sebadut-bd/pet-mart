<?php

namespace App\Http\Resources;

use App\Services\ImageStorage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $viewerId = $request->user()?->id ?? auth('sanctum')->id();
        $isOwner = $viewerId !== null && (int) $viewerId === (int) $this->user_id;

        return [
            'id'               => $this->id,
            'title'            => $this->title,
            'slug'             => $this->slug,
            'description'      => $this->description,
            'price'            => $this->price !== null ? (float) $this->price : null,
            'is_negotiable'    => (bool) $this->is_negotiable,
            'is_free'          => (bool) $this->is_free,
            'listing_type'     => $this->listing_type,
            'condition'        => $this->condition,
            'status'           => $this->status,
            'rejection_reason' => $isOwner ? $this->rejection_reason : null,
            'is_owner'         => $isOwner,
            'views'            => (int) $this->views,
            'is_featured'      => (bool) $this->is_featured,
            'is_urgent'        => (bool) $this->is_urgent,
            'is_wishlisted'    => $viewerId ? $this->wishlists()->where('user_id', $viewerId)->exists() : false,
            'category'         => $this->whenLoaded('category', fn () => [
                'id'        => $this->category->id,
                'name'      => $this->category->name,
                'slug'      => $this->category->slug,
                'parent_id' => $this->category->parent_id,
            ]),
            'seller' => $this->whenLoaded('user', fn () => [
                'id'          => $this->user->id,
                'name'        => $this->user->name,
                'phone'       => $this->user->phone,
                'image'       => ImageStorage::url($this->user->image),
                'is_verified' => (bool) $this->user->is_verified,
                'created_at'  => $this->user->created_at?->toIso8601String(),
            ]),
            'location' => [
                'division_id'   => $this->division_id,
                'division_name' => $this->relationLoaded('division') ? $this->division?->name : null,
                'district_id'   => $this->district_id,
                'district_name' => $this->relationLoaded('district') ? $this->district?->name : null,
                'area_id'       => $this->area_id,
                'area_name'     => $this->relationLoaded('area') ? $this->area?->name : null,
                'full_address'  => $this->full_address,
            ],
            'images' => $this->whenLoaded('images', fn () => $this->images->map(fn ($img) => [
                'id'         => $img->id,
                'image_path' => ImageStorage::url($img->image_path),
                'path'       => $img->image_path,
                'is_primary' => (bool) $img->is_primary,
            ])->values()),
            'attributes' => $this->whenLoaded('attributes', fn () => $this->attributes->mapWithKeys(fn ($attr) => [
                $attr->attribute_key => $attr->attribute_value,
            ])),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
