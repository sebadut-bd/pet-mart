<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Models\Product;
use App\Services\ImageStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InquiryController extends Controller
{
    private const MAX_MESSAGES = 500;

    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $inquiries = Inquiry::where(fn ($q) => $q->where('sender_id', $userId)->orWhere('receiver_id', $userId))
            ->with(['product' => fn ($q) => $q->withTrashed()->with('primaryImage'), 'sender:id,name', 'receiver:id,name'])
            ->latest()
            ->limit(self::MAX_MESSAGES)
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $inquiries->map(fn (Inquiry $inq) => $this->present($inq, $userId))->values(),
            'meta'   => [
                'unread' => Inquiry::where('receiver_id', $userId)->where('is_read', false)->count(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'message'    => ['required', 'string', 'min:2', 'max:1000'],
        ]);

        $product = Product::active()->find($data['product_id']);
        if (! $product) {
            return response()->json(['status' => false, 'message' => 'This listing is no longer available.'], 422);
        }

        $sender = $request->user();
        if ($product->user_id === $sender->id) {
            return response()->json(['status' => false, 'message' => 'You cannot send an inquiry to your own listing.'], 422);
        }

        $inquiry = Inquiry::create([
            'product_id'  => $product->id,
            'sender_id'   => $sender->id,
            'receiver_id' => $product->user_id,
            'message'     => trim($data['message']),
            'parent_id'   => null,
            'is_read'     => false,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Message sent to the seller.',
            'data'    => $this->present($inquiry->load(['product.primaryImage', 'sender:id,name', 'receiver:id,name']), $sender->id),
        ], 201);
    }

    public function reply(Request $request, int $id): JsonResponse
    {
        $data = $request->validate(['message' => ['required', 'string', 'min:1', 'max:1000']]);
        $userId = $request->user()->id;

        $original = Inquiry::where('id', $id)
            ->where(fn ($q) => $q->where('sender_id', $userId)->orWhere('receiver_id', $userId))
            ->firstOrFail();

        if (! Product::whereKey($original->product_id)->exists()) {
            return response()->json(['status' => false, 'message' => 'This listing has been removed.'], 422);
        }

        $otherId = $original->sender_id === $userId ? $original->receiver_id : $original->sender_id;

        $reply = Inquiry::create([
            'product_id'  => $original->product_id,
            'sender_id'   => $userId,
            'receiver_id' => $otherId,
            'message'     => trim($data['message']),
            'parent_id'   => $original->parent_id ?? $original->id,
            'is_read'     => false,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Reply sent.',
            'data'    => $this->present($reply->load(['product.primaryImage', 'sender:id,name', 'receiver:id,name']), $userId),
        ], 201);
    }

    public function markRead(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;

        $inquiry = Inquiry::where('id', $id)
            ->where(fn ($q) => $q->where('sender_id', $userId)->orWhere('receiver_id', $userId))
            ->firstOrFail();

        $otherId = $inquiry->sender_id === $userId ? $inquiry->receiver_id : $inquiry->sender_id;

        $updated = Inquiry::where('product_id', $inquiry->product_id)
            ->where('receiver_id', $userId)
            ->where('sender_id', $otherId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['status' => true, 'message' => 'Marked as read.', 'data' => ['updated' => $updated]]);
    }

    private function present(Inquiry $inq, int $userId): array
    {
        $mine = $inq->sender_id === $userId;
        $other = $mine ? $inq->receiver : $inq->sender;

        return [
            'id'                => $inq->id,
            'parent_id'         => $inq->parent_id,
            'product_id'        => $inq->product_id,
            'product_title'     => $inq->product?->title,
            'product_slug'      => $inq->product?->trashed() ? null : $inq->product?->slug,
            'product_image'     => ImageStorage::url($inq->product?->primaryImage?->image_path),
            'sender_id'         => $inq->sender_id,
            'sender_name'       => $inq->sender?->name,
            'receiver_id'       => $inq->receiver_id,
            'receiver_name'     => $inq->receiver?->name,
            'other_user_id'     => $other?->id,
            'other_user_name'   => $other?->name,
            'conversation_key'  => $inq->product_id . ':' . implode('-', [min($inq->sender_id, $inq->receiver_id), max($inq->sender_id, $inq->receiver_id)]),
            'is_mine'           => $mine,
            'message'           => $inq->message,
            'is_read'           => (bool) $inq->is_read,
            'edited_by_admin'   => $inq->admin_edited_at !== null,
            'created_at'        => $inq->created_at?->toIso8601String(),
        ];
    }
}
