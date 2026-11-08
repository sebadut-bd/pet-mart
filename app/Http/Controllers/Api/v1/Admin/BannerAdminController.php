<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BannerAdminController extends Controller
{
    public function index(): JsonResponse
    {
        $banners = Banner::orderBy('sort_order', 'asc')->paginate(15);
        return response()->json([
            'status' => 2000,
            'result' => [
                'data'         => $banners->items(),
                'current_page' => $banners->currentPage(),
                'last_page'    => $banners->lastPage(),
                'per_page'     => $banners->perPage(),
                'total'        => $banners->total(),
            ],
            'data'   => $banners,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title'      => 'required|string|max:255',
            'image_path' => 'required|string',
            'link_url'   => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $banner = Banner::create([
            'title'      => $request->title,
            'image_path' => $request->image_path,
            'link_url'   => $request->link_url,
            'sort_order' => $request->sort_order ?? 0,
            'status'     => $request->status ?? true,
        ]);

        return response()->json([
            'status'  => 2000,
            'message' => 'Banner created successfully.',
            'result'  => $banner,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $banner = Banner::findOrFail($id);
        $banner->update($request->only(['title', 'image_path', 'link_url', 'sort_order', 'status']));

        return response()->json([
            'status'  => 2000,
            'message' => 'Banner updated successfully.',
            'result'  => $banner,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $banner = Banner::findOrFail($id);
        $banner->delete();

        return response()->json([
            'status'  => 2000,
            'message' => 'Banner deleted successfully.',
        ]);
    }
}
