<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /**
     * Get user's saved wishlist listings.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $productIds = Wishlist::where('user_id', $userId)->pluck('product_id');

        $products = Product::whereIn('id', $productIds)
            ->with(['category', 'user', 'primaryImage', 'images', 'division', 'district', 'area'])
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'data'   => ProductResource::collection($products),
        ]);
    }

    /**
     * Toggle wishlist item.
     */
    public function toggle(Request $request): JsonResponse
    {
        $request->validate(['product_id' => 'required|exists:products,id']);

        $userId = $request->user()->id;
        $productId = $request->product_id;

        $existing = Wishlist::where('user_id', $userId)->where('product_id', $productId)->first();

        if ($existing) {
            $existing->delete();
            return response()->json([
                'status' => true,
                'message' => 'Listing removed from wishlist.',
                'is_wishlisted' => false,
            ]);
        }

        Wishlist::create([
            'user_id' => $userId,
            'product_id' => $productId,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Listing saved to wishlist.',
            'is_wishlisted' => true,
        ]);
    }
}
