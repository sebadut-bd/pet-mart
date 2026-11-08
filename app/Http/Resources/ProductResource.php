<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $primaryImg = $this->primaryImage?->image_path ?? $this->images->first()?->image_path;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'price' => $this->price !== null ? (float) $this->price : null,
            'is_negotiable' => (bool) $this->is_negotiable,
            'is_free' => (bool) $this->is_free,
            'listing_type' => $this->listing_type,
            'condition' => $this->condition,
            'status' => $this->status,
            'primary_image' => $primaryImg ? (str_starts_with($primaryImg, 'http') ? $primaryImg : asset('storage/' . $primaryImg)) : null,
            'category' => $this->whenLoaded('category', fn() => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ]),
            'location' => [
                'division' => $this->division?->name,
                'district' => $this->district?->name,
                'area' => $this->area?->name,
                'full_address' => $this->full_address,
            ],
            'seller' => $this->whenLoaded('user', fn() => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'phone' => $this->user->phone,
                'is_verified' => (bool) $this->user->is_verified,
            ]),
            'is_featured' => (bool) $this->is_featured,
            'is_urgent' => (bool) $this->is_urgent,
            'views' => $this->views,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
