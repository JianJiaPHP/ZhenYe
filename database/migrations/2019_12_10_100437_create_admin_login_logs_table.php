<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminLoginLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_login_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->index('id');
            $table->integer('uid')->comment('管理员id');
            $table->string('ip',50)->comment('ip地址');
            $table->string('country',100)->nullable()->comment('国家');
            $table->string('city',100)->nullable()->comment('城市');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });
        DB::statement("ALTER TABLE `zy_admin_login_logs` comment '管理登陆日志'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_login_logs');
    }
}
