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

            $table->integer('start_at')->unsigned();
            $table->integer('end_at')->unsigned()->nullable();

//            $table->smallInteger('year')->unsigned();
//            $table->tinyInteger('month')->unsigned();
//            $table->tinyInteger('dayS')->unsigned();
//            $table->tinyInteger('dayE')->nullable()->unsigned();
//            $table->tinyInteger('hourS')->nullable()->unsigned();
//            $table->tinyInteger('hourE')->nullable()->unsigned();
//            $table->tinyInteger('minuteS')->nullable()->unsigned();
//            $table->tinyInteger('minuteE')->nullable()->unsigned();
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
