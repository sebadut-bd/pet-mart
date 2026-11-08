<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\CategoryAttribute;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryAdminController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $categories = Category::with(['children', 'attributes', 'parent'])->orderBy('sort_order')->get();
        $formatted = CategoryResource::collection($categories);

        return response()->json([
            'status' => 2000,
            'result' => [
                'data' => $formatted,
            ],
            'data' => $formatted,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'parent_id' => 'nullable|exists:categories,id',
            'icon' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $category = Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'parent_id' => $request->parent_id,
            'description' => $request->description,
            'icon' => $request->icon,
            'sort_order' => $request->sort_order ?? 0,
            'status' => true,
        ]);

        return response()->json([
            'status' => 2000,
            'message' => 'Category created successfully.',
            'result' => new CategoryResource($category),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $category = Category::findOrFail($id);

        $category->update([
            'name' => $request->name ?? $category->name,
            'parent_id' => $request->parent_id,
            'description' => $request->description,
            'icon' => $request->icon,
            'sort_order' => $request->sort_order ?? $category->sort_order,
            'status' => $request->has('status') ? (bool)$request->status : $category->status,
        ]);

        return response()->json([
            'status' => 2000,
            'message' => 'Category updated successfully.',
            'result' => new CategoryResource($category),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json([
            'status'  => 2000,
            'message' => 'Category deleted successfully.',
        ]);
    }

    public function storeAttribute(Request $request, int $categoryId): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'attribute_key' => 'required|string|max:80',
            'type' => 'required|string|in:text,number,select,boolean',
            'options' => 'nullable|array',
            'is_required' => 'nullable|boolean',
        ]);

        $attr = CategoryAttribute::create([
            'category_id' => $categoryId,
            'name' => $request->name,
            'attribute_key' => Str::slug($request->attribute_key, '_'),
            'type' => $request->type,
            'options' => $request->options,
            'is_required' => $request->boolean('is_required', false),
            'is_filterable' => true,
        ]);

        return response()->json([
            'status' => 2000,
            'message' => 'Category attribute added.',
            'result' => $attr,
        ], 201);
    }
}
