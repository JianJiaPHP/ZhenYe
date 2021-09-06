<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminRoleAdministratorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_role_administrator', function (Blueprint $table) {
            $table->bigInteger('administrator_id')->comment('管理员ID');
            $table->bigInteger('role_id')->comment('角色ID');
            $table->index(['administrator_id','role_id']);
            $table->dateTime('created_at');
        });
        \DB::statement("ALTER TABLE `zy_admin_role_administrator` comment '管理员角色'");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_role_administrator');
    }
}
