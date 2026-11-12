<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductReportController extends Controller
{
    public function store(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'reason'      => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $product = Product::whereIn('status', ['active', 'sold'])->find($id);
        if (! $product) {
            return response()->json(['status' => false, 'message' => 'This listing is no longer available.'], 404);
        }
        if ($product->user_id === $request->user()->id) {
            return response()->json(['status' => false, 'message' => 'You cannot report your own listing.'], 422);
        }

        ProductReport::updateOrCreate(
            ['product_id' => $product->id, 'user_id' => $request->user()->id],
            ['reason' => $data['reason'], 'description' => $data['description'] ?? null, 'status' => 'pending']
        );

        return response()->json([
            'status'  => true,
            'message' => 'Thanks. Our moderators will review this listing.',
        ], 201);
    }
}
