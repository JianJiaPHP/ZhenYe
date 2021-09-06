<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminMenuTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_menu', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('parent_id')->comment('父级ID');
            $table->string('path', 255)->nullable()->comment('路径');
            $table->string('icon', 100)->nullable()->comment('图标');
            $table->string('name', 100)->nullable()->comment('菜单名称');
            $table->integer('sort')->default(0)->nullable()->comment('排序');
            $table->tinyInteger('is_hidden')->default(0)->nullable()->comment('是否隐藏 0=否 1=是');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });
        \DB::statement("ALTER TABLE `zy_admin_menu` comment '后台菜单'");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_menu');
    }
}
