<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use App\Models\Module;
use App\Models\Permission;
use App\Models\Role;
use App\Models\RoleModules;
use App\Models\RolePermission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConfigurationController extends Controller
{
    public function getConfigurations(Request $request): JsonResponse
    {
        $user = $request->user();
        $roleId = $user ? ($user->role_id ?? 1) : 1; // Default to Super Admin (role 1)

        // Get allowed module IDs for this role
        $allowedModuleIds = RoleModules::where('role_id', $roleId)->pluck('module_id')->toArray();
        $allowedPermissionIds = RolePermission::where('role_id', $roleId)->pluck('permission_id')->toArray();

        // Fetch parent modules with submenus
        $modules = Module::where('parent_id', 0)
            ->where('status', true)
            ->with([
                'permissions',
                'submenus' => function ($q) {
                    $q->where('status', true)->with(['permissions', 'submenus' => function ($q2) {
                        $q2->where('status', true)->with('permissions');
                    }]);
                }
            ])
            ->orderBy('id', 'asc')
            ->get();

        // Filter modules according to role permissions if not super admin
        if ($roleId != 1) {
            $modules = $modules->filter(function ($mod) use ($allowedModuleIds) {
                return in_array($mod->id, $allowedModuleIds);
            })->values();
        }

        $userPermissions = Permission::whereIn('id', $allowedPermissionIds)->pluck('name')->toArray();

        return response()->json([
            'status' => 2000,
            'result' => [
                'menus'       => $modules,
                'permissions' => $userPermissions,
                'user'        => $user ? [
                    'id'       => $user->id,
                    'name'     => $user->name,
                    'username' => $user->name,
                    'email'    => $user->email,
                    'avatar'   => $user->avatar ?? null,
                ] : [
                    'id'       => 1,
                    'name'     => 'Super Admin',
                    'username' => 'Super Admin',
                    'email'    => 'admin@petmart.com',
                    'avatar'   => null,
                ],
                'config'      => [
                    'name' => 'Pet-Mart Marketplace',
                ]
            ]
        ]);
    }

    public function index(): JsonResponse
    {
        $configurations = Configuration::all();
        return response()->json([
            'status' => 2000,
            'result' => $configurations,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'key'          => 'required|string|max:50|unique:configurations,key',
            'type'         => 'required|string|max:50',
            'display_name' => 'required|string|max:100',
            'value'        => 'required',
        ]);

        $config = Configuration::create($request->only('key', 'type', 'display_name', 'value', 'is_visible'));
        return response()->json([
            'status'  => 2000,
            'message' => 'Created successfully.',
            'result'  => $config,
        ], 201);
    }

    public function show(Configuration $configuration): JsonResponse
    {
        return response()->json([
            'status' => 2000,
            'result' => $configuration,
        ]);
    }

    public function update(Request $request, Configuration $configuration): JsonResponse
    {
        $request->validate([
            'value' => 'required',
        ]);

        $configuration->update($request->only('value', 'is_visible'));
        return response()->json([
            'status'  => 2000,
            'message' => 'Updated successfully.',
            'result'  => $configuration,
        ]);
    }

    public function destroy(Configuration $configuration): JsonResponse
    {
        $configuration->delete();
        return response()->json([
            'status'  => 2000,
            'message' => 'Deleted successfully.',
        ]);
    }
}
