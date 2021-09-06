<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminRoleMenuTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_role_menu', function (Blueprint $table) {
            $table->bigInteger('role_id')->comment('角色ID');
            $table->bigInteger('menu_id')->comment('菜单ID');
            $table->index(['menu_id','role_id']);
            $table->dateTime('created_at');
        });
        \DB::statement("ALTER TABLE `zy_admin_role_menu` comment '角色菜单'");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_role_menu');
    }
}
