<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Services\ImageStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate(['position' => ['nullable', 'string', 'max:50']]);

        $banners = Banner::where('status', true)
            ->when($request->filled('position'), fn ($q) => $q->where('position', $request->position))
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $banners->map(fn (Banner $b) => [
                'id'       => $b->id,
                'title'    => $b->title,
                'image'    => ImageStorage::url($b->image),
                'link'     => $b->link,
                'position' => $b->position,
            ])->values(),
        ]);
    }
}
