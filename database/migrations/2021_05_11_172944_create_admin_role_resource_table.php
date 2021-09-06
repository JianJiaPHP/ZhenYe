<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminRoleResourceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_role_resource', function (Blueprint $table) {
            $table->bigInteger('role_id')->comment('角色ID');
            $table->bigInteger('resource_id')->comment('资源ID');
            $table->index(['resource_id','role_id']);
            $table->dateTime('created_at');
        });
        \DB::statement("ALTER TABLE `zy_admin_role_resource` comment '角色资源'");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_role_resource');
    }
}
