<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContactMessageAdminController extends AdminController
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'status'  => ['nullable', 'in:new,read,resolved'],
            'keyword' => ['nullable', 'string', 'max:100'],
        ]);

        $page = ContactMessage::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('keyword'), function ($q) use ($request) {
                $term = $this->like($request->keyword);
                $q->where(fn ($w) => $w->where('name', 'like', $term)->orWhere('phone', 'like', $term)->orWhere('message', 'like', $term));
            })
            ->latest()
            ->paginate($this->perPage($request));

        $result = $this->paginated($page, fn (ContactMessage $m) => [
            'id'         => $m->id,
            'name'       => $m->name,
            'phone'      => $m->phone,
            'email'      => $m->email,
            'message'    => $m->message,
            'status'     => $m->status,
            'admin_note' => $m->admin_note,
            'user_id'    => $m->user_id,
            'created_at' => $m->created_at?->toIso8601String(),
        ]);
        $result['counts'] = ContactMessage::select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status');

        return $this->ok($result);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'status'     => ['required', 'in:new,read,resolved'],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $message = ContactMessage::findOrFail($id);
        $message->update($data);

        return $this->ok(['id' => $message->id, 'status' => $message->status], 'Message updated.');
    }

    public function destroy(int $id): JsonResponse
    {
        ContactMessage::findOrFail($id)->delete();

        return $this->ok(null, 'Message deleted.');
    }
}
