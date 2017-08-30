<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResumesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resumes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            // 简历标题
            $table->string('title');

            $table->string('name', 16);
            // 照片的url
            $table->string('photo')->nullable();
            //学校
            $table->string('school')->nullable();
            //专业
            $table->string('major')->default('计算机科学与技术');
            //生日
            $table->date('birthday')->nullable();
            //联系方式
            $table->string('contact')->nullable();
            //作为个人标签
            $table->string('flag')->nullable();
            // 性别 0 为男 1为女
            $table->tinyInteger('sex')->default(1);
            // 期望工作城市
            $table->string('city');
             // 体重
            $table->string('weight');
            // 升高
            $table->string('height')->default('160');
            // 期望工作地点
            $table->string('expect_location')->nullable();
            // 自我介绍
            $table->string('introduction')->default('我要找工作');
            $table->timestamps();

            $table->index(['user_id', 'title']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('resumes');
    }
}
