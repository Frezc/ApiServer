<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExpectJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expect_jobs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('user_name');
            // 简历标题
            $table->string('title');
            $table->string('name', 16);
            // 照片的url
            $table->string('photo')->nullable();
            $table->string('school')->nullable();
            $table->date('birthday')->nullable();
            $table->string('contact')->nullable();
            // 性别 0 为男 1为女
            $table->tinyInteger('sex')->default(0);
            // 期望工作地点
            $table->string('expect_location')->nullable();
            // 自我介绍
            $table->string('introduction')->nullable();

            // 是否公开
            $table->tinyInteger('is_public')->default(0);

            $table->softDeletes();
            $table->timestamps();

            $table->index('user_id');
            $table->index('is_public');
            $table->index('introduction');
            $table->index('expect_location');
            $table->index('school');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('expect_jobs');
    }
}
