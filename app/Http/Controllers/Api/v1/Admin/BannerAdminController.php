<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Models\Banner;
use App\Services\ImageStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BannerAdminController extends AdminController
{
    public function index(Request $request): JsonResponse
    {
        $page = Banner::orderBy('sort_order')->latest('id')->paginate($this->perPage($request));

        return $this->ok($this->paginated($page, fn (Banner $b) => [
            'id'         => $b->id,
            'title'      => $b->title,
            'image'      => $b->image,
            'image_url'  => ImageStorage::url($b->image),
            'link'       => $b->link,
            'position'   => $b->position,
            'sort_order' => (int) $b->sort_order,
            'status'     => (bool) $b->status,
            'starts_at'  => $b->starts_at?->toDateString(),
            'ends_at'    => $b->ends_at?->toDateString(),
            'is_running' => $b->status
                && (! $b->starts_at || $b->starts_at->lte(now()))
                && (! $b->ends_at || $b->ends_at->endOfDay()->gte(now())),
        ]));
    }

    public function store(Request $request): JsonResponse
    {
        $banner = Banner::create($this->validated($request));

        return $this->ok(['id' => $banner->id], 'Banner created.', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $banner = Banner::findOrFail($id);
        $banner->update($this->validated($request));

        return $this->ok(['id' => $banner->id], 'Banner updated.');
    }

    public function destroy(int $id): JsonResponse
    {
        Banner::findOrFail($id)->delete();

        return $this->ok(null, 'Banner deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title'      => ['required', 'string', 'max:255'],
            'image'      => ['required', 'string', 'max:500', 'regex:#^(banners/[\w/.-]+|https://\S+)$#'],
            'link'       => ['nullable', 'string', 'max:255', 'regex:#^(/[^/\s]\S*|/|https://\S+)$#'],
            'position'   => ['nullable', 'in:home_top,home_middle,browse_top'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'status'     => ['nullable', 'boolean'],
            'starts_at'  => ['nullable', 'date'],
            'ends_at'    => ['nullable', 'date', 'after_or_equal:starts_at'],
        ], [
            'image.regex' => 'Upload an image or use an https:// link.',
            'link.regex'  => 'Use a site path such as /browse?category_slug=dogs, or an https:// link.',
        ]);

        $data['position'] = $data['position'] ?? 'home_top';
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['status'] = $data['status'] ?? true;

        return $data;
    }
}
