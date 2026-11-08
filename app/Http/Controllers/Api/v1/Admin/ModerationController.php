<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductDetailResource;
use App\Http\Resources\ProductResource;
use App\Models\AdminModerationLog;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModerationController extends Controller
{
    /**
     * List listings in moderation queue (pending, active, rejected).
     */
    public function index(Request $request): JsonResponse
    {
        $status = $request->get('status', 'pending');

        $listings = Product::where('status', $status)
            ->with(['category', 'user', 'primaryImage', 'images', 'division', 'district', 'area', 'moderationLogs.admin'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'status' => 2000,
            'result' => [
                'data'         => ProductResource::collection($listings)->response()->getData(true)['data'],
                'current_page' => $listings->currentPage(),
                'last_page'    => $listings->lastPage(),
                'per_page'     => $listings->perPage(),
                'total'        => $listings->total(),
            ],
            'data' => ProductResource::collection($listings),
        ]);
    }

    /**
     * Approve listing for publication.
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        $admin = $request->user();
        $product = Product::findOrFail($id);

        $product->update([
            'status' => 'active',
            'rejection_reason' => null,
        ]);

        AdminModerationLog::create([
            'product_id' => $product->id,
            'admin_id'   => $admin ? $admin->id : 1,
            'action'     => 'approved',
            'reason'     => 'Listing approved by admin moderation.',
        ]);

        return response()->json([
            'status'  => 2000,
            'message' => 'Listing approved and is now live on marketplace.',
            'result'  => new ProductDetailResource($product),
        ]);
    }

    /**
     * Reject listing with mandatory reason.
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        $request->validate(['reason' => 'required|string|max:500']);

        $admin = $request->user();
        $product = Product::findOrFail($id);

        $product->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason,
        ]);

        AdminModerationLog::create([
            'product_id' => $product->id,
            'admin_id'   => $admin ? $admin->id : 1,
            'action'     => 'rejected',
            'reason'     => $request->reason,
        ]);

        return response()->json([
            'status'  => 2000,
            'message' => 'Listing rejected successfully.',
            'result'  => new ProductDetailResource($product),
        ]);
    }
}
