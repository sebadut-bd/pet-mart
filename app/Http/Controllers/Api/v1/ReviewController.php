<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Submit rating & review for seller after completed transaction.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating'     => 'required|integer|min:1|max:5',
            'comment'    => 'nullable|string|max:500',
        ]);

        $reviewer = $request->user();
        $product = Product::findOrFail($request->product_id);

        if ($product->user_id === $reviewer->id) {
            return response()->json([
                'status'  => false,
                'message' => 'You cannot review yourself.',
            ], 422);
        }

        $review = Review::updateOrCreate(
            [
                'product_id'  => $product->id,
                'reviewer_id' => $reviewer->id,
            ],
            [
                'reviewee_id' => $product->user_id,
                'rating'      => $request->rating,
                'comment'     => $request->comment,
                'status'      => 'approved',
            ]
        );

        return response()->json([
            'status'  => true,
            'message' => 'Review submitted successfully.',
            'data'    => $review,
        ], 201);
    }
}
