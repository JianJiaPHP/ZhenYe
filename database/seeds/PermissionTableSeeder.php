<?php

use App\Models\AdminPermission;

class PermissionTableSeeder extends \Illuminate\Database\Seeder
{

    public function run()
    {
        $now = \Carbon\Carbon::now()->toDateTimeString();

        // 权限父级
        $permission = \App\Models\AdminMenu::query()->create([
            'parent_id' => 0,
            'path' => '/permission',
            'icon' => 'nested',
            'name' => '权限管理',
            'sort' => 0,
            'is_hidden' => 0,
        ]);

        \App\Models\AdminMenu::query()->insert([
            [
                'parent_id' => $permission->id,
                'path' => '/permission/resource',
                'icon' => '',
                'name' => '资源管理',
                'sort' => 2,
                'is_hidden' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'parent_id' => $permission->id,
                'path' => '/permission/menu',
                'icon' => '',
                'name' => '菜单管理',
                'sort' => 3,
                'is_hidden' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ], [
                'parent_id' => $permission->id,
                'path' => '/permission/role',
                'icon' => '',
                'name' => '角色管理',
                'sort' => 4,
                'is_hidden' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ], [
                'parent_id' => $permission->id,
                'path' => '/permission/administrators',
                'icon' => '',
                'name' => '管理员管理',
                'sort' => 5,
                'is_hidden' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);


        // 系统管理父级
        $system = \App\Models\AdminMenu::query()->create([
            'parent_id' => 0,
            'path' => '/system',
            'icon' => 'system',
            'name' => '系统管理',
            'sort' => 0,
            'is_hidden' => 0,
        ]);
        \App\Models\AdminMenu::query()->insert([
            [
                'parent_id' => $system->id,
                'path' => '/system/config',
                'icon' => '',
                'name' => '系统配置',
                'sort' => 1,
                'is_hidden' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'parent_id' => $system->id,
                'path' => '/system/loginLog',
                'icon' => '',
                'name' => '登录日志',
                'sort' => 2,
                'is_hidden' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'parent_id' => $system->id,
                'path' => '/system/operatingLog',
                'icon' => '',
                'name' => '操作日志',
                'sort' => 3,
                'is_hidden' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);


    }

}
