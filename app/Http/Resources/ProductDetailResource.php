<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $userId = auth('sanctum')->id();
        $isWishlisted = false;

        if ($userId) {
            $isWishlisted = $this->wishlists()->where('user_id', $userId)->exists();
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price !== null ? (float) $this->price : null,
            'is_negotiable' => (bool) $this->is_negotiable,
            'is_free' => (bool) $this->is_free,
            'listing_type' => $this->listing_type,
            'condition' => $this->condition,
            'status' => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'views' => $this->views,
            'is_featured' => (bool) $this->is_featured,
            'is_urgent' => (bool) $this->is_urgent,
            'is_wishlisted' => $isWishlisted,
            'category' => $this->whenLoaded('category', fn() => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ]),
            'sub_category' => $this->whenLoaded('subCategory', fn() => [
                'id' => $this->subCategory?->id,
                'name' => $this->subCategory?->name,
            ]),
            'seller' => $this->whenLoaded('user', fn() => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'phone' => $this->user->phone,
                'email' => $this->user->email,
                'bio' => $this->user->bio,
                'image' => $this->user->image ? (str_starts_with($this->user->image, 'http') ? $this->user->image : asset('storage/' . $this->user->image)) : null,
                'is_verified' => (bool) $this->user->is_verified,
                'created_at' => $this->user->created_at?->toIso8601String(),
            ]),
            'location' => [
                'division_id' => $this->division_id,
                'division_name' => $this->division?->name,
                'district_id' => $this->district_id,
                'district_name' => $this->district?->name,
                'area_id' => $this->area_id,
                'area_name' => $this->area?->name,
                'full_address' => $this->full_address,
            ],
            'images' => $this->images->map(fn($img) => [
                'id' => $img->id,
                'image_path' => str_starts_with($img->image_path, 'http') ? $img->image_path : asset('storage/' . $img->image_path),
                'is_primary' => (bool) $img->is_primary,
            ]),
            'attributes' => $this->attributes->mapWithKeys(fn($attr) => [
                $attr->attribute_key => $attr->attribute_value,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
