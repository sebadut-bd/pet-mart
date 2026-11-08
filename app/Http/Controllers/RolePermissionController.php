<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Permission;
use App\Models\RoleModules;
use App\Models\RolePermission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RolePermissionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $roleId = $request->get('role_id');

        $allModules = Module::where('parent_id', 0)
            ->with(['permissions', 'submenus.permissions', 'submenus.submenus.permissions'])
            ->orderBy('id', 'asc')
            ->paginate(50);

        $assignedModules = [];
        $assignedPermissions = [];

        if ($roleId) {
            $assignedModules = RoleModules::where('role_id', $roleId)->pluck('module_id')->toArray();
            $assignedPermissions = RolePermission::where('role_id', $roleId)->pluck('permission_id')->toArray();
        }

        return response()->json([
            'status' => 2000,
            'result' => [
                'all_modules' => $allModules,
                'modules'     => $assignedModules,
                'permissions' => $assignedPermissions,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'role_id' => 'required|integer|exists:roles,id',
            'type'    => 'required|string|in:insert,remove',
        ]);

        $roleId = $request->role_id;
        $type = $request->type;
        $moduleId = $request->module_id;
        $permissionIds = $request->permissions ?? [];

        if ($moduleId) {
            if ($type === 'insert') {
                RoleModules::firstOrCreate([
                    'role_id'   => $roleId,
                    'module_id' => $moduleId,
                ]);
            } else {
                RoleModules::where('role_id', $roleId)->where('module_id', $moduleId)->delete();
            }
        }

        foreach ($permissionIds as $pId) {
            if ($type === 'insert') {
                RolePermission::firstOrCreate([
                    'role_id'       => $roleId,
                    'permission_id' => $pId,
                ]);
            } else {
                RolePermission::where('role_id', $roleId)->where('permission_id', $pId)->delete();
            }
        }

        return response()->json([
            'status'  => 2000,
            'message' => 'Role permissions updated successfully.',
        ]);
    }
}
