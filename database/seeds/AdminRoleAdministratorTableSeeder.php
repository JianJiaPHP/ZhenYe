<?php

use Illuminate\Database\Seeder;

class AdminRoleAdministratorTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\AdminRoleAdministrator::query()->create([
            'administrator_id'=>'1',
            'role_id'=>'1',
            'created_at'=>\Carbon\Carbon::now()->toDateTimeString()
        ]);
    }
}
