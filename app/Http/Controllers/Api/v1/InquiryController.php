<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InquiryController extends Controller
{
    /**
     * List all chat threads for authenticated buyer/seller.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $inquiries = Inquiry::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->with(['product.primaryImage', 'sender', 'receiver'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $inquiries->map(fn($inq) => [
                'id' => $inq->id,
                'product_id' => $inq->product_id,
                'product_title' => $inq->product?->title,
                'sender_name' => $inq->sender?->name,
                'receiver_name' => $inq->receiver?->name,
                'message' => $inq->message,
                'is_read' => (bool) $inq->is_read,
                'created_at' => $inq->created_at?->toIso8601String(),
            ]),
        ]);
    }

    /**
     * Send inquiry/message to listing seller.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'message'    => 'required|string|max:1000',
        ]);

        $product = Product::findOrFail($request->product_id);
        $sender = $request->user();

        if ($product->user_id === $sender->id) {
            return response()->json([
                'status'  => false,
                'message' => 'You cannot send an inquiry to your own listing.',
            ], 422);
        }

        $inquiry = Inquiry::create([
            'product_id'  => $product->id,
            'sender_id'   => $sender->id,
            'receiver_id' => $product->user_id,
            'message'     => $request->message,
            'parent_id'   => $request->parent_id ?? null,
            'is_read'     => false,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Inquiry sent to seller successfully.',
            'data'    => $inquiry->load(['sender', 'receiver', 'product']),
        ], 201);
    }
}
