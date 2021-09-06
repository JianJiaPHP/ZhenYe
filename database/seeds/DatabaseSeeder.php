<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            AdministratorsTableSeeder::class,
            ConfigTableSeeder::class,
            PermissionTableSeeder::class,
            AdminResourceTableSeeder::class,
            AdminRoleTableSeeder::class,
            AdminRoleAdministratorTableSeeder::class,
        ]);
    }
}
