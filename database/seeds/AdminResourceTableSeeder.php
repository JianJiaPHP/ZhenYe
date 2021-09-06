<?php


class AdminResourceTableSeeder extends \Illuminate\Database\Seeder
{

    public function run()
    {

        $now = \Carbon\Carbon::now()->toDateTimeString();

        \App\Models\AdminResource::query()->insert([
            [
                'name' => '资源列表',
                'url' => '/adminResource*',
                'http_method' => '*',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '菜单管理',
                'url' => '/adminMenu*',
                'http_method' => '*',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '角色管理',
                'url' => '/role*',
                'http_method' => '*',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '管理员管理',
                'url' => '/administrators*',
                'http_method' => '*',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '操作日志',
                'url' => '/operating_log*',
                'http_method' => '*',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '登录日志',
                'url' => '/login_log*',
                'http_method' => '*',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '系统配置',
                'url' => '/config*',
                'http_method' => '*',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

    }

}
