<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\ProductReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductReportController extends Controller
{
    /**
     * Report listing for abuse / violation.
     */
    public function store(Request $request, int $productId): JsonResponse
    {
        $request->validate([
            'reason'      => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
        ]);

        $report = ProductReport::updateOrCreate(
            [
                'product_id' => $productId,
                'user_id'    => $request->user()->id,
            ],
            [
                'reason'      => $request->reason,
                'description' => $request->description,
                'status'      => 'pending',
            ]
        );

        return response()->json([
            'status'  => true,
            'message' => 'Listing report submitted to admin for review.',
            'data'    => $report,
        ], 201);
    }
}
