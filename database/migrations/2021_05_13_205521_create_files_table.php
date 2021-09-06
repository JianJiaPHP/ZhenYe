<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('file_size')->default(0)->comment('文件大小(字节数)');
            $table->string('original_name')->nullable()->comment('原始文件名');
            $table->string('path', 300)->nullable()->comment('oss路径');
            $table->string('object', 255)->nullable()->comment('oss的object');
            $table->bigInteger('uploader_id')->default(0)->comment('上传者ID');
            $table->smallInteger('uploader_type')->default(0)->comment('上传者类型(0=用户 1=管理员)');
            $table->dateTime('upload_at')->comment('上传时间');
        });
        \DB::statement("ALTER TABLE `zy_files` comment '文件'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('files');
    }
}
