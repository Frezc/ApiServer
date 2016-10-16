<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUploadfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uploadfiles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('path');
            $table->integer('uploader_id')->unsigned();
            $table->tinyInteger('used')->default(0);  // 是否被使用，如果没有被使用，一天后就会被清理
            $table->tinyInteger('exist')->default(1);  // 文件是否还存在，被删除后置0
            $table->timestamps();

            $table->index(['path']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('uploadfiles');
    }
}
