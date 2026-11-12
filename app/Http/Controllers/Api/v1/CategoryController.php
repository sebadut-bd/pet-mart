<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::whereNull('parent_id')
            ->where('status', true)
            ->with(['children' => fn($q) => $q->where('status', true)->with('attributes'), 'attributes'])
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => CategoryResource::collection($categories),
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $category = Category::where('slug', $slug)
            ->where('status', true)
            ->with(['children', 'attributes', 'parent'])
            ->firstOrFail();

        return response()->json([
            'status' => true,
            'data'   => new CategoryResource($category),
        ]);
    }
}
