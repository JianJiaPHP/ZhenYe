<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminResourceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_resource', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255)->nullable()->comment('资源名称');
            $table->string('url', 100)->nullable()->comment('资源路径');
            $table->string('http_method')->nullable()->comment('请求方式(*号全部)');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });
        \DB::statement("ALTER TABLE `zy_admin_resource` comment '资源'");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_resource');
    }
}
