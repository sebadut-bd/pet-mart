<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Permission;
use App\Models\RoleModules;
use App\Models\RolePermission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $modules = Module::where('parent_id', 0)
            ->with(['permissions', 'submenus.permissions', 'submenus.submenus.permissions'])
            ->orderBy('id', 'asc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'status' => 2000,
            'result' => $modules,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'         => 'required|string|max:100|unique:modules,name',
            'display_name' => 'required|string|max:100',
            'link'         => 'required|string',
            'parent_id'    => 'nullable',
        ]);

        $module = Module::create([
            'name'         => $request->name,
            'display_name' => $request->display_name,
            'link'         => $request->link,
            'parent_id'    => $request->parent_id ?: 0,
            'status'       => true,
        ]);

        RoleModules::firstOrCreate([
            'role_id'   => 1,
            'module_id' => $module->id,
        ]);

        $defaultPermissions = ['view', 'add', 'update', 'delete'];
        foreach ($defaultPermissions as $perm) {
            $p = Permission::create([
                'module_id'    => $module->id,
                'name'         => $module->name . '_' . $perm,
                'display_name' => $module->display_name . ' ' . ucfirst($perm),
            ]);
            RolePermission::firstOrCreate([
                'role_id'       => 1,
                'permission_id' => $p->id,
            ]);
        }

        return response()->json([
            'status'  => 2000,
            'message' => 'Module created successfully.',
            'result'  => $module,
        ], 200);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $module = Module::findOrFail($id);

        $module->update([
            'display_name' => $request->display_name ?? $module->display_name,
            'link'         => $request->link ?? $module->link,
            'parent_id'    => $request->has('parent_id') ? ($request->parent_id ?: 0) : $module->parent_id,
            'status'       => $request->has('status') ? (bool)$request->status : $module->status,
        ]);

        return response()->json([
            'status'  => 2000,
            'message' => 'Module updated successfully.',
            'result'  => $module,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $module = Module::findOrFail($id);
        Permission::where('module_id', $id)->delete();
        RoleModules::where('module_id', $id)->delete();
        $module->delete();

        return response()->json([
            'status'  => 2000,
            'message' => 'Module deleted successfully.',
        ]);
    }
}
