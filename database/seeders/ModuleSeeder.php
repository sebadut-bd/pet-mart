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
                'name'         => 'dashboard',
                'link'         => '/admin/dashboard',
                'icon'         => 'fa-gauge-high',
                'permissions'  => ['view'],
            ],
            [
                'display_name' => 'Marketplace',
                'name'         => 'marketplace',
                'link'         => '#',
                'icon'         => 'fa-store',
                'permissions'  => ['show'],
                'submenus'     => [
                    ['display_name' => 'Listing moderation', 'name' => 'moderation', 'link' => '/admin/marketplace/moderation', 'icon' => 'fa-clipboard-check', 'permissions' => ['view', 'approve', 'reject', 'feature']],
                    ['display_name' => 'Reports', 'name' => 'report', 'link' => '/admin/marketplace/reports', 'icon' => 'fa-flag', 'permissions' => ['view', 'resolve', 'dismiss']],
                    ['display_name' => 'Contact messages', 'name' => 'contact_message', 'link' => '/admin/marketplace/messages', 'icon' => 'fa-envelope-open-text', 'permissions' => ['view', 'update', 'delete']],
                    ['display_name' => 'Members', 'name' => 'user', 'link' => '/admin/marketplace/members', 'icon' => 'fa-users', 'permissions' => ['view', 'verify', 'update', 'delete']],
                    ['display_name' => 'Categories', 'name' => 'category', 'link' => '/admin/marketplace/categories', 'icon' => 'fa-layer-group', 'permissions' => ['view', 'add', 'update', 'delete']],
                    ['display_name' => 'Banners', 'name' => 'banner', 'link' => '/admin/marketplace/banners', 'icon' => 'fa-image', 'permissions' => ['view', 'add', 'update', 'delete']],
                ],
            ],
            [
                'display_name' => 'Access control',
                'name'         => 'accesses',
                'link'         => '#',
                'icon'         => 'fa-shield-halved',
                'permissions'  => ['show'],
                'submenus'     => [
                    ['display_name' => 'Users', 'name' => 'admin_user', 'link' => '/admin/users', 'icon' => 'fa-user-gear', 'permissions' => ['view', 'add', 'update', 'delete']],
                    ['display_name' => 'Roles', 'name' => 'role', 'link' => '/admin/role', 'icon' => 'fa-user-shield', 'permissions' => ['view', 'add', 'update', 'delete']],
                    ['display_name' => 'Role permissions', 'name' => 'role_permission', 'link' => '/admin/role_permissions', 'icon' => 'fa-key', 'permissions' => ['add', 'delete']],
                    ['display_name' => 'Modules', 'name' => 'module', 'link' => '/admin/module', 'icon' => 'fa-cubes', 'permissions' => ['view', 'add', 'update', 'delete']],
                ],
            ],
            [
                'display_name' => 'Settings',
                'name'         => 'setting',
                'link'         => '#',
                'icon'         => 'fa-gear',
                'permissions'  => ['show'],
                'submenus'     => [
                    ['display_name' => 'General settings', 'name' => 'general_setting', 'link' => '/admin/general_setting', 'icon' => 'fa-sliders', 'permissions' => ['view', 'update']],
                ],
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
