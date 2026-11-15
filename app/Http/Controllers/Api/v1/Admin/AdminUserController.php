<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Models\Role;
use App\Models\User;
use App\Services\ImageStorage;
use App\Support\PhoneNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminUserController extends AdminController
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'keyword' => ['nullable', 'string', 'max:100'],
            'status'  => ['nullable', 'in:0,1'],
            'role_id' => ['nullable', 'integer'],
        ]);

        $page = $this->admins()
            ->when($request->filled('keyword'), function ($q) use ($request) {
                $term = $this->like($request->keyword);
                $q->where(fn ($w) => $w->where('name', 'like', $term)->orWhere('email', 'like', $term)->orWhere('phone', 'like', $term));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', (bool) $request->status))
            ->when($request->filled('role_id'), fn ($q) => $q->where('role_id', $request->role_id))
            ->with('role:id,display_name')
            ->orderBy('name')
            ->paginate($this->perPage($request, 20));

        $me = $request->user()->id;
        $result = $this->paginated($page, fn (User $u) => $this->present($u, $me));
        $result['roles'] = Role::where('status', true)->orderBy('id')->get(['id', 'display_name']);
        $result['counts'] = [
            'total'    => $this->admins()->count(),
            'active'   => $this->admins()->where('status', true)->count(),
            'inactive' => $this->admins()->where('status', false)->count(),
        ];

        return $this->ok($result);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'phone'    => $data['phone'],
            'role_id'  => $data['role_id'],
            'image'    => $data['image'] ?? null,
            'password' => Hash::make($data['password']),
            'status'   => $data['status'],
            'type'     => 1,
        ]);

        return $this->ok(['id' => $user->id], 'User created. They can now sign in to the dashboard.', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $this->admins()->findOrFail($id);
        $data = $this->validated($request, $user);

        if (! $data['status'] && ($error = $this->cannotDisable($request, $user))) {
            return $this->error($error);
        }

        DB::transaction(function () use ($user, $data) {
            $fields = collect($data)->only(['name', 'email', 'phone', 'role_id', 'status', 'image'])->all();
            if (! empty($data['password'])) {
                $fields['password'] = Hash::make($data['password']);
            }
            $user->update($fields);
            if (! $user->status || ! empty($data['password'])) {
                $user->tokens()->delete();
            }
        });

        return $this->ok(['id' => $user->id], 'User updated.');
    }

    public function toggleStatus(Request $request, int $id): JsonResponse
    {
        $user = $this->admins()->findOrFail($id);

        if ($user->status && ($error = $this->cannotDisable($request, $user))) {
            return $this->error($error);
        }

        DB::transaction(function () use ($user) {
            $user->update(['status' => ! $user->status]);
            if (! $user->status) {
                $user->tokens()->delete();
            }
        });

        return $this->ok($this->present($user->load('role'), $request->user()->id), $user->status ? 'User activated.' : 'User deactivated.');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $this->admins()->findOrFail($id);

        if ($error = $this->cannotDisable($request, $user)) {
            return $this->error(str_replace('deactivate', 'delete', $error));
        }

        DB::transaction(function () use ($user) {
            $user->tokens()->delete();
            $user->delete();
        });

        return $this->ok(null, 'User deleted.');
    }

    private function cannotDisable(Request $request, User $user): ?string
    {
        if ($user->id === $request->user()->id) {
            return 'You cannot deactivate your own account.';
        }
        if ($user->status && $this->admins()->where('status', true)->count() <= 1) {
            return 'You cannot deactivate the last active admin.';
        }

        return null;
    }

    private function validated(Request $request, ?User $user = null): array
    {
        if ($request->filled('phone')) {
            $request->merge(['phone' => PhoneNumber::normalize($request->phone)]);
        }

        $data = $request->validate([
            'name'     => ['required', 'string', 'min:2', 'max:100'],
            'email'    => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user?->id)],
            'phone'    => ['required', 'regex:' . PhoneNumber::PATTERN, Rule::unique('users', 'phone')->ignore($user?->id)],
            'role_id'  => ['required', 'integer', Rule::exists('roles', 'id')],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'max:100', 'confirmed'],
            'status'   => ['required', 'boolean'],
            'image'    => ['nullable', 'string', 'max:255', 'regex:#^avatars/[\w/.-]+$#'],
        ], [
            'image.regex'  => 'Upload the photo again.',
            'phone.regex'  => 'Enter a valid Bangladesh mobile number, e.g. 01712345678.',
            'phone.unique' => 'Another account already uses this mobile number.',
            'email.unique' => 'Another account already uses this email.',
        ]);

        if (! empty($data['image']) && (str_contains($data['image'], '..') || ! Storage::disk('public')->exists($data['image']))) {
            abort(response()->json(['message' => 'The photo was not found. Upload it again.', 'errors' => ['image' => ['The photo was not found. Upload it again.']]], 422));
        }

        return $data;
    }

    private function admins()
    {
        return User::where('type', 1);
    }

    private function present(User $u, int $me): array
    {
        return [
            'id'         => $u->id,
            'name'       => $u->name,
            'email'      => $u->email,
            'phone'      => $u->phone,
            'image'      => $u->image,
            'image_url'  => ImageStorage::url($u->image),
            'role_id'    => $u->role_id,
            'role'       => $u->role?->display_name,
            'status'     => (bool) $u->status,
            'is_me'      => $u->id === $me,
            'created_at' => $u->created_at?->toIso8601String(),
        ];
    }
}
