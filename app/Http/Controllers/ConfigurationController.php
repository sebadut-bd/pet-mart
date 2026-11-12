<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Permission;
use App\Models\RoleModules;
use App\Models\RolePermission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConfigurationController extends Controller
{
    public function getConfigurations(Request $request): JsonResponse
    {
        $user = $request->user();
        $roleId = (int) ($user->role_id ?? 1);

        $allowedModuleIds = RoleModules::where('role_id', $roleId)->pluck('module_id')->toArray();
        $allowedPermissionIds = RolePermission::where('role_id', $roleId)->pluck('permission_id')->toArray();

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
                'user'        => [
                    'id'     => $user->id,
                    'name'   => $user->name,
                    'email'  => $user->email,
                    'phone'  => $user->phone,
                    'avatar' => \App\Services\ImageStorage::url($user->image),
                ],
                'config'      => [
                    'name'         => 'PetMart',
                    'frontend_url' => config('app.frontend_url'),
                ],
                'badges'      => [
                    'moderation'      => \App\Models\Product::where('status', 'pending')->count(),
                    'report'          => \App\Models\ProductReport::where('status', 'pending')->count(),
                    'contact_message' => \App\Models\ContactMessage::where('status', 'new')->count(),
                ],
            ]
        ]);
    }
}
