<?php

use Illuminate\Database\Seeder;

class ConfigTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Config::create([
            'group'=>'admin',
            'key'=>'admin_name',
            'value'=>'宏烨商城',
            'desc'=>'后台名'
        ]);

        \App\Models\Config::create([
            'group'=>'admin',
            'key'=>'record',
            'value'=>'渝ICP备17013228号-1',
            'desc'=>'备案号'
        ]);
    }
}
