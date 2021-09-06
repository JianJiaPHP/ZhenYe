<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOperatingLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_operating_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->index('id');
            $table->bigInteger('uid')->comment('操作人员id');
            $table->string('router',100)->nullable()->comment('操作路径');
            $table->string('method',20)->nullable()->comment('操作方式');
            $table->longText('content')->nullable()->comment('操作内容');
            $table->string('desc',20)->nullable()->comment('操作简单描述');
            $table->string('ip',50)->nullable()->comment('操作ip');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');

        });
        \DB::statement("ALTER TABLE `zy_admin_operating_logs` comment '管理员操作日志'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('operating_logs');
    }
}
