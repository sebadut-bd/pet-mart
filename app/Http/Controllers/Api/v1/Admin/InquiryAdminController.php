<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Models\Inquiry;
use App\Models\InquiryAdminLog;
use App\Models\Permission;
use App\Models\Product;
use App\Models\RolePermission;
use App\Models\User;
use App\Services\ImageStorage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Admin view of buyer–seller inquiries, drilled down as
 *   seller → listings with inquiries → one conversation per buyer → messages.
 *
 * A conversation is (product_id, buyer_id), where the buyer is whichever party is not
 * the listing owner. Admins can read, edit (marked as "edited by PetMart" for both users),
 * soft delete and restore. They cannot post messages. Every action is written to
 * inquiry_admin_logs, including opening a conversation.
 *
 * `scope=deleted` switches the lists to soft-deleted messages so they can be restored.
 */
class InquiryAdminController extends AdminController
{
    private const BUYER_SQL = 'CASE WHEN inquiries.sender_id = products.user_id THEN inquiries.receiver_id ELSE inquiries.sender_id END';
    private const UNREAD_SQL = 'SUM(CASE WHEN inquiries.is_read = 0 THEN 1 ELSE 0 END)';
    private const THREAD_LIMIT = 500;

    public function sellers(Request $request): JsonResponse
    {
        $this->authorizeAction($request, 'view');
        $request->validate([
            'keyword' => ['nullable', 'string', 'max:100'],
            'scope'   => ['nullable', 'in:active,deleted'],
        ]);

        $stats = $this->messages($request)
            ->selectRaw('products.user_id AS seller_id')
            ->selectRaw('COUNT(DISTINCT inquiries.product_id) AS listings')
            ->selectRaw('COUNT(DISTINCT inquiries.product_id, ' . self::BUYER_SQL . ') AS threads')
            ->selectRaw('COUNT(*) AS messages')
            ->selectRaw(self::UNREAD_SQL . ' AS unread')
            ->selectRaw('MAX(inquiries.created_at) AS last_at')
            ->groupBy('products.user_id');

        $page = User::query()
            ->joinSub($stats->toBase(), 's', 's.seller_id', '=', 'users.id')
            ->select('users.id', 'users.name', 'users.phone', 'users.image', 's.listings', 's.threads', 's.messages', 's.unread', 's.last_at')
            ->when($request->filled('keyword'), function ($q) use ($request) {
                $term = $this->like($request->keyword);
                $q->where(fn ($w) => $w->where('users.name', 'like', $term)
                    ->orWhere('users.phone', 'like', $term)
                    ->orWhereExists(fn ($e) => $e->selectRaw('1')->from('products')
                        ->whereColumn('products.user_id', 'users.id')
                        ->where('products.title', 'like', $term)));
            })
            ->orderByDesc('s.last_at')
            ->paginate($this->perPage($request, 20));

        return $this->ok($this->paginated($page, fn (User $u) => [
            'id'       => $u->id,
            'name'     => $u->name,
            'phone'    => $u->phone,
            'avatar'   => ImageStorage::url($u->image),
            'listings' => (int) $u->listings,
            'threads'  => (int) $u->threads,
            'messages' => (int) $u->messages,
            'unread'   => (int) $u->unread,
            'last_at'  => $this->iso($u->last_at),
        ]));
    }

    public function listings(Request $request, int $sellerId): JsonResponse
    {
        $this->authorizeAction($request, 'view');
        $request->validate(['scope' => ['nullable', 'in:active,deleted']]);

        $seller = User::select('id', 'name', 'phone', 'image')->findOrFail($sellerId);

        $page = $this->messages($request)
            ->where('products.user_id', $seller->id)
            ->selectRaw('inquiries.product_id')
            ->selectRaw('COUNT(DISTINCT ' . self::BUYER_SQL . ') AS threads')
            ->selectRaw('COUNT(*) AS messages')
            ->selectRaw(self::UNREAD_SQL . ' AS unread')
            ->selectRaw('MAX(inquiries.created_at) AS last_at')
            ->groupBy('inquiries.product_id')
            ->orderByDesc('last_at')
            ->toBase()
            ->paginate($this->perPage($request, 20));

        $products = Product::withTrashed()->with('primaryImage')
            ->whereIn('id', collect($page->items())->pluck('product_id'))
            ->get()->keyBy('id');

        $result = $this->paginated($page, function ($row) use ($products) {
            $p = $products[$row->product_id] ?? null;

            return [
                'id'       => (int) $row->product_id,
                'title'    => $p?->title ?? 'Listing #' . $row->product_id,
                'slug'     => $p && ! $p->trashed() ? $p->slug : null,
                'status'   => ! $p || $p->trashed() ? 'deleted' : $p->status,
                'price'    => $p?->price,
                'is_free'  => (bool) $p?->is_free,
                'image'    => ImageStorage::url($p?->primaryImage?->image_path),
                'threads'  => (int) $row->threads,
                'messages' => (int) $row->messages,
                'unread'   => (int) $row->unread,
                'last_at'  => $this->iso($row->last_at),
            ];
        });
        $result['seller'] = ['id' => $seller->id, 'name' => $seller->name, 'phone' => $seller->phone, 'avatar' => ImageStorage::url($seller->image)];

        return $this->ok($result);
    }

    public function threads(Request $request, int $productId): JsonResponse
    {
        $this->authorizeAction($request, 'view');
        $request->validate(['scope' => ['nullable', 'in:active,deleted']]);

        Product::withTrashed()->select('id')->findOrFail($productId);

        $rows = $this->messages($request)
            ->where('inquiries.product_id', $productId)
            ->selectRaw(self::BUYER_SQL . ' AS buyer_id')
            ->selectRaw('COUNT(*) AS messages')
            ->selectRaw(self::UNREAD_SQL . ' AS unread')
            ->selectRaw('MAX(inquiries.created_at) AS last_at')
            ->selectRaw('MAX(inquiries.id) AS last_id')
            ->groupByRaw(self::BUYER_SQL)
            ->orderByDesc('last_at')
            ->limit(200)
            ->toBase()
            ->get();

        $buyers = User::select('id', 'name', 'phone', 'image')->whereIn('id', $rows->pluck('buyer_id'))->get()->keyBy('id');
        $last = Inquiry::withTrashed()->whereIn('id', $rows->pluck('last_id'))->get(['id', 'sender_id', 'message'])->keyBy('id');

        return $this->ok($rows->map(function ($row) use ($buyers, $last) {
            $buyer = $buyers[$row->buyer_id] ?? null;
            $msg = $last[$row->last_id] ?? null;

            return [
                'buyer_id'     => (int) $row->buyer_id,
                'buyer_name'   => $buyer?->name ?? 'Deleted member',
                'buyer_phone'  => $buyer?->phone,
                'buyer_avatar' => ImageStorage::url($buyer?->image),
                'messages'     => (int) $row->messages,
                'unread'       => (int) $row->unread,
                'last_at'      => $this->iso($row->last_at),
                'last_message' => $msg ? mb_strimwidth($msg->message, 0, 90, '…') : null,
                'last_from'    => $msg && (int) $msg->sender_id === (int) $row->buyer_id ? 'buyer' : 'seller',
            ];
        })->values());
    }

    public function show(Request $request, int $productId, int $buyerId): JsonResponse
    {
        $this->authorizeAction($request, 'view');

        $product = Product::withTrashed()->with(['primaryImage', 'user:id,name,phone,image'])->findOrFail($productId);
        $messages = $this->conversation($product, $buyerId)->withTrashed()
            ->orderByDesc('id')->limit(self::THREAD_LIMIT)->get()->reverse()->values();
        abort_if($messages->isEmpty(), 404, 'Conversation not found.');

        $buyer = User::select('id', 'name', 'phone', 'image')->find($buyerId);
        $this->log($request, 'viewed', ['product_id' => $product->id, 'buyer_id' => $buyerId, 'affected' => 0]);

        $activity = InquiryAdminLog::with('admin:id,name')
            ->where('product_id', $product->id)->where('buyer_id', $buyerId)
            ->where('action', '!=', 'viewed')
            ->latest('id')->limit(10)->get()
            ->map(fn (InquiryAdminLog $l) => [
                'action'     => $l->action,
                'admin'      => $l->admin?->name ?? 'Removed admin',
                'affected'   => $l->affected,
                'inquiry_id' => $l->inquiry_id,
                'created_at' => $l->created_at?->toIso8601String(),
            ]);

        return $this->ok([
            'product' => [
                'id'      => $product->id,
                'title'   => $product->title,
                'slug'    => $product->trashed() ? null : $product->slug,
                'status'  => $product->trashed() ? 'deleted' : $product->status,
                'price'   => $product->price,
                'is_free' => (bool) $product->is_free,
                'image'   => ImageStorage::url($product->primaryImage?->image_path),
            ],
            'seller'    => $this->person($product->user),
            'buyer'     => $this->person($buyer, $buyerId),
            'truncated' => $messages->count() >= self::THREAD_LIMIT,
            'messages'  => $messages->map(fn (Inquiry $m) => $this->presentMessage($m, (int) $product->user_id)),
            'activity'  => $activity,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $this->authorizeAction($request, 'update');
        $data = $request->validate(['message' => ['required', 'string', 'min:1', 'max:1000']]);

        $inquiry = Inquiry::findOrFail($id);
        $text = trim($data['message']);
        if ($text === $inquiry->message) {
            return $this->error('The message has not changed.');
        }

        DB::transaction(function () use ($request, $inquiry, $text) {
            $this->log($request, 'edited', $this->target($inquiry) + ['old_message' => $inquiry->message]);
            $inquiry->update(['message' => $text, 'admin_edited_at' => now()]);
        });

        return $this->ok($this->presentMessage($inquiry->fresh(), $this->sellerOf($inquiry)), 'Message updated. Both users will see it was edited by PetMart.');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->authorizeAction($request, 'delete');

        $inquiry = Inquiry::findOrFail($id);
        DB::transaction(function () use ($request, $inquiry) {
            $this->log($request, 'deleted', $this->target($inquiry) + ['old_message' => $inquiry->message]);
            $inquiry->delete();
        });

        return $this->ok(['id' => $inquiry->id], 'Message deleted. You can restore it from this conversation.');
    }

    public function restore(Request $request, int $id): JsonResponse
    {
        $this->authorizeAction($request, 'delete');

        $inquiry = Inquiry::onlyTrashed()->findOrFail($id);
        DB::transaction(function () use ($request, $inquiry) {
            $inquiry->restore();
            $this->log($request, 'restored', $this->target($inquiry));
        });

        return $this->ok(['id' => $inquiry->id], 'Message restored.');
    }

    public function destroyThread(Request $request, int $productId, int $buyerId): JsonResponse
    {
        $this->authorizeAction($request, 'delete');
        $product = Product::withTrashed()->select('id', 'user_id')->findOrFail($productId);

        $count = DB::transaction(function () use ($request, $product, $buyerId) {
            $ids = $this->conversation($product, $buyerId)->pluck('id');
            abort_if($ids->isEmpty(), 404, 'Conversation not found.');
            Inquiry::whereIn('id', $ids)->delete();
            $this->log($request, 'thread_deleted', ['product_id' => $product->id, 'buyer_id' => $buyerId, 'affected' => $ids->count()]);

            return $ids->count();
        });

        return $this->ok(['deleted' => $count], "Conversation deleted ({$count} messages). Find it under the Deleted tab to restore it.");
    }

    public function restoreThread(Request $request, int $productId, int $buyerId): JsonResponse
    {
        $this->authorizeAction($request, 'delete');
        $product = Product::withTrashed()->select('id', 'user_id')->findOrFail($productId);

        $count = DB::transaction(function () use ($request, $product, $buyerId) {
            $ids = $this->conversation($product, $buyerId)->onlyTrashed()->pluck('id');
            abort_if($ids->isEmpty(), 404, 'Nothing to restore.');
            Inquiry::onlyTrashed()->whereIn('id', $ids)->restore();
            $this->log($request, 'thread_restored', ['product_id' => $product->id, 'buyer_id' => $buyerId, 'affected' => $ids->count()]);

            return $ids->count();
        });

        return $this->ok(['restored' => $count], "Conversation restored ({$count} messages).");
    }

    /* ------------------------------------------------------------------ */

    /** Inquiries joined to their listing (soft-deleted listings included). */
    private function messages(Request $request): Builder
    {
        $query = Inquiry::query()->join('products', 'products.id', '=', 'inquiries.product_id');

        return $request->input('scope') === 'deleted' ? $query->onlyTrashed() : $query;
    }

    private function conversation(Product $product, int $buyerId): Builder
    {
        $sellerId = (int) $product->user_id;

        return Inquiry::query()
            ->where('product_id', $product->id)
            ->where(fn ($q) => $q
                ->where(fn ($a) => $a->where('sender_id', $buyerId)->where('receiver_id', $sellerId))
                ->orWhere(fn ($b) => $b->where('sender_id', $sellerId)->where('receiver_id', $buyerId)));
    }

    private function sellerOf(Inquiry $inquiry): int
    {
        return (int) Product::withTrashed()->whereKey($inquiry->product_id)->value('user_id');
    }

    private function target(Inquiry $inquiry): array
    {
        $sellerId = $this->sellerOf($inquiry);

        return [
            'product_id' => $inquiry->product_id,
            'buyer_id'   => (int) $inquiry->sender_id === $sellerId ? $inquiry->receiver_id : $inquiry->sender_id,
            'inquiry_id' => $inquiry->id,
        ];
    }

    private function presentMessage(Inquiry $m, int $sellerId): array
    {
        return [
            'id'              => $m->id,
            'from'            => (int) $m->sender_id === $sellerId ? 'seller' : 'buyer',
            'message'         => $m->message,
            'is_read'         => (bool) $m->is_read,
            'created_at'      => $m->created_at?->toIso8601String(),
            'admin_edited_at' => $this->iso($m->admin_edited_at),
            'deleted_at'      => $m->deleted_at?->toIso8601String(),
        ];
    }

    private function person(?User $user, ?int $fallbackId = null): array
    {
        return [
            'id'     => $user?->id ?? $fallbackId,
            'name'   => $user?->name ?? 'Deleted member',
            'phone'  => $user?->phone,
            'avatar' => ImageStorage::url($user?->image),
        ];
    }

    private function log(Request $request, string $action, array $attributes): void
    {
        InquiryAdminLog::create($attributes + ['admin_id' => $request->user()->id, 'action' => $action]);
    }

    /**
     * Server-side permission check. The admin middleware only checks type=1, and the
     * role permissions otherwise only hide menu items. Role 1 (Super Admin) is allowed.
     */
    private function authorizeAction(Request $request, string $action): void
    {
        $user = $request->user();
        if ((int) $user->role_id === 1) {
            return;
        }

        $allowed = $user->role_id && RolePermission::where('role_id', $user->role_id)
            ->whereIn('permission_id', Permission::where('name', 'inquiry_' . $action)->select('id'))
            ->exists();

        abort_unless($allowed, 403, 'You do not have permission to do this.');
    }

    private function iso(mixed $value): ?string
    {
        return $value ? \Illuminate\Support\Carbon::parse($value)->toIso8601String() : null;
    }
}
