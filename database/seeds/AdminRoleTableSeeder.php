<?php

use Illuminate\Database\Seeder;

class AdminRoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\AdminRole::query()->create([
            'name'=>'超管',
            'desc'=>'所有权限',
        ]);
    }
}
