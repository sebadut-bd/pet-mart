<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $roles = Role::orderBy('id', 'asc')->paginate($request->get('per_page', 20));

        return response()->json([
            'status' => 2000,
            'result' => $roles,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'         => 'required|string|max:100|unique:roles,name',
            'display_name' => 'required|string|max:100',
        ]);

        $role = Role::create([
            'name'         => $request->name,
            'display_name' => $request->display_name,
            'status'       => true,
        ]);

        return response()->json([
            'status'  => 2000,
            'message' => 'Role created successfully.',
            'result'  => $role,
        ], 200);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $role = Role::findOrFail($id);

        $role->update([
            'display_name' => $request->display_name ?? $role->display_name,
            'status'       => $request->has('status') ? (bool)$request->status : $role->status,
        ]);

        return response()->json([
            'status'  => 2000,
            'message' => 'Role updated successfully.',
            'result'  => $role,
        ]);
    }

    public function status(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'integer']]);
        $role = Role::findOrFail($request->id);

        if ($role->id === 1) {
            return response()->json(['status' => 5000, 'message' => 'The Super Admin role cannot be switched off.'], 422);
        }

        $role->update(['status' => (int) $role->status === 1 ? 0 : 1]);

        return response()->json([
            'status'  => 2000,
            'message' => (int) $role->status === 1 ? 'Role activated.' : 'Role deactivated.',
            'result'  => $role,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $role = Role::findOrFail($id);

        if ($role->id === 1) {
            return response()->json(['status' => 5000, 'message' => 'The Super Admin role cannot be deleted.'], 422);
        }
        if (\App\Models\User::where('role_id', $role->id)->exists()) {
            return response()->json(['status' => 5000, 'message' => 'This role is still given to users. Change their role first.'], 422);
        }

        $role->delete();

        return response()->json([
            'status'  => 2000,
            'message' => 'Role deleted successfully.',
        ]);
    }
}
