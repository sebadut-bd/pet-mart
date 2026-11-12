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
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $products = Product::query()
            ->join('wishlists', 'wishlists.product_id', '=', 'products.id')
            ->where('wishlists.user_id', $userId)
            ->whereIn('products.status', ['active', 'sold'])
            ->orderByDesc('wishlists.created_at')
            ->select('products.*')
            ->with(['category', 'user', 'primaryImage', 'division', 'district', 'area'])
            ->get();

        return response()->json([
            'status' => true,
            'data'   => ProductResource::collection($products),
        ]);
    }

    public function toggle(Request $request): JsonResponse
    {
        $request->validate(['product_id' => ['required', 'integer']]);
        $userId = $request->user()->id;

        $existing = Wishlist::where('user_id', $userId)->where('product_id', $request->product_id)->first();
        if ($existing) {
            $existing->delete();

            return response()->json(['status' => true, 'message' => 'Removed from saved listings.', 'is_wishlisted' => false]);
        }

        if (! Product::whereKey($request->product_id)->whereIn('status', ['active', 'sold'])->exists()) {
            return response()->json(['status' => false, 'message' => 'This listing is no longer available.'], 422);
        }

        Wishlist::firstOrCreate(['user_id' => $userId, 'product_id' => $request->product_id]);

        return response()->json(['status' => true, 'message' => 'Saved to your list.', 'is_wishlisted' => true]);
    }
}
