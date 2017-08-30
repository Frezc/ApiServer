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
            //期待工作的类型
            $table->string('type');
            //期待工作工资
            $table->string('salary');
            $table->string('salary_type');
            //期待工作的城市
            $table->string('city');
            // 期望工作地点
            $table->string('expect_location')->nullable();
           //期待工作的时间
            $table->string('time');

            $table->softDeletes();
            $table->timestamps();
            $table->index('user_id');
            $table->index('expect_location');
            $table->index('city');
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
