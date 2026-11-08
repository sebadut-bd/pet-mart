<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\RoleModules;
use App\Models\RolePermission;
use Illuminate\Database\Seeder;
use App\Models\Module;

class ModuleSeeder extends Seeder
{
    public function run()
    {
        Module::truncate();
        Permission::truncate();
        RolePermission::truncate();
        RoleModules::truncate();

        $modules = [
            [
                'display_name' => 'Dashboard',
                'name' => 'dashboard',
                'link' => '/admin/dashboard',
                'permissions' => ['view', 'user_view', 'invoice_view'],
                'icon' => 'fa-tachometer-alt'
            ],
            [
                'display_name' => 'Marketplace',
                'name' => 'marketplace',
                'link' => '#',
                'permissions' => ['show'],
                'icon' => 'fa-store',
                'submenus' => [
                    [
                        'display_name' => 'Product Approval Queue',
                        'name' => 'moderation',
                        'link' => '/admin/marketplace/moderation',
                        'permissions' => ['view', 'add', 'update', 'delete', 'approve', 'reject'],
                    ],
                    [
                        'display_name' => 'Categories & Specs',
                        'name' => 'category',
                        'link' => '/admin/marketplace/categories',
                        'permissions' => ['add', 'update', 'delete', 'view'],
                    ],
                    [
                        'display_name' => 'Users & Sellers',
                        'name' => 'user',
                        'link' => '/admin/marketplace/users',
                        'permissions' => ['add', 'update', 'delete', 'view', 'verify'],
                    ],
                    [
                        'display_name' => 'Homepage Banners',
                        'name' => 'banner',
                        'link' => '/admin/marketplace/banners',
                        'permissions' => ['add', 'update', 'delete', 'view'],
                    ],
                    [
                        'display_name' => 'Abuse Reports Queue',
                        'name' => 'report',
                        'link' => '/admin/marketplace/reports',
                        'permissions' => ['view', 'resolve', 'dismiss'],
                    ],
                ]
            ],
            [
                'display_name' => 'RBAC & Accesses',
                'name' => 'accesses',
                'link' => '#',
                'permissions' => ['show'],
                'icon' => 'fa-shield-alt',
                'submenus' => [
                    [
                        'display_name' => 'Modules',
                        'name' => 'module',
                        'link' => '/admin/module',
                        'permissions' => ['add', 'update', 'delete', 'view'],
                    ],
                    [
                        'display_name' => 'Roles',
                        'name' => 'role',
                        'link' => '/admin/role',
                        'permissions' => ['add', 'update', 'delete', 'view'],
                    ],
                    [
                        'display_name' => 'Role Permissions',
                        'name' => 'role_permission',
                        'link' => '/admin/role_permissions',
                        'permissions' => ['add', 'delete'],
                    ],
                ]
            ],
            [
                'display_name' => 'Configurations',
                'name' => 'setting',
                'link' => '#',
                'permissions' => ['show'],
                'icon' => 'fa-cogs',
                'submenus' => [
                    [
                        'display_name' => 'General Settings',
                        'name' => 'general_setting',
                        'link' => '/admin/general_setting',
                        'permissions' => ['add', 'update', 'delete', 'view'],
                    ],
                    [
                        'display_name' => 'Settings',
                        'name' => 'configuration',
                        'link' => '/admin/configurations',
                        'permissions' => ['add', 'update', 'delete', 'view'],
                    ]
                ]
            ],
        ];

        foreach ($modules as $menu) {
            $permissions = isset($menu['permissions']) ? $menu['permissions'] : [];
            $submenus = isset($menu['submenus']) ? $menu['submenus'] : [];
            unset($menu['permissions']);
            unset($menu['submenus']);

            $parent = $this->insertModule($menu);
            $this->insertRoleModule($parent->id, 1);

            foreach ($permissions as $permission) {
                $new_permission = $this->insertPermission($parent->name, $permission, $parent->display_name, $parent->id);
                $this->insertRolePermission($new_permission->id, 1);
            }

            foreach ($submenus as $submenu) {
                $permissions = isset($submenu['permissions']) ? $submenu['permissions'] : [];
                $subSubMenus = isset($submenu['submenus']) ? $submenu['submenus'] : [];
                unset($submenu['permissions']);
                unset($submenu['submenus']);

                $subParent = $this->insertModule($submenu, $parent->id);
                $this->insertRoleModule($subParent->id, 1);

                foreach ($permissions as $permission) {
                    $new_permission = $this->insertPermission($subParent->name, $permission, $subParent->display_name, $subParent->id);
                    $this->insertRolePermission($new_permission->id, 1);
                }

                foreach ($subSubMenus as $subSubMenu) {
                    $permissions = isset($subSubMenu['permissions']) ? $subSubMenu['permissions'] : [];
                    unset($subSubMenu['permissions']);
                    unset($subSubMenu['submenus']);

                    $module = $this->insertModule($subSubMenu, $subParent->id);
                    $this->insertRoleModule($module->id, 1);

                    foreach ($permissions as $permission) {
                        $new_permission = $this->insertPermission($module->name, $permission, $module->display_name, $module->id);
                        $this->insertRolePermission($new_permission->id, 1);
                    }
                }
            }
        }
    }

    public function insertModule($data, $parent_id = 0)
    {
        $module = new Module();
        $module->fill($data);
        $module->parent_id = $parent_id;
        $module->save();

        return $module;
    }

    public function insertRoleModule($module_id, $role_id = 1)
    {
        $role_module = new RoleModules();
        $role_module->role_id = $role_id;
        $role_module->module_id = $module_id;
        $role_module->save();

        return $role_module;
    }

    public function insertPermission($name, $permission, $display_name, $module_id)
    {
        $new_name = $name . '_' . $permission;
        $new_permission = new Permission();
        $new_permission->module_id = $module_id;
        $new_permission->name = $new_name;
        $new_permission->display_name = $display_name . ' ' . str_replace('_', ' ', $permission);
        $new_permission->save();

        return $new_permission;
    }

    public function insertRolePermission($permission_id, $role_id = 1)
    {
        $role_module = new RolePermission();
        $role_module->role_id = $role_id;
        $role_module->permission_id = $permission_id;
        $role_module->save();

        return $role_module;
    }
}
