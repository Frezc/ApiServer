<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobTimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_times', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('job_id')->unsigned();
            // 人数限制（null为无限制）
            $table->mediumInteger('number')->unsigned()->nullable();
            // 已经申请成功的人数
            $table->mediumInteger('number_applied')->unsigned()->default(0);
            // 工资类型，1：面议, 2：固定数值
            $table->tinyInteger('salary_type')->default(1);
            // 工资
            $table->integer('salary')->unsigned()->default(0);
            // 申请截止时间（默认为开始时间）
            $table->dateTime('apply_end_at')->nullable();
            // 开始时间
            $table->dateTime('start_at')->nullable();
            // 结束时间

            $table->dateTime('end_at')->nullable();
        
//            $table->smallInteger('year')->unsigned();
//            $table->tinyInteger('month')->unsigned();
//            $table->tinyInteger('dayS')->unsigned();
//            $table->tinyInteger('dayE')->nullable()->unsigned();
//            $table->tinyInteger('hourS')->nullable()->unsigned();
//            $table->tinyInteger('hourE')->nullable()->unsigned();
//            $table->tinyInteger('minuteS')->nullable()->unsigned();
//            $table->tinyInteger('minuteE')->nullable()->unsigned();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['job_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('job_times');
    }
}
