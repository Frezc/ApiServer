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
            // 申请截止时间（默认为开始时间）
            $table->string('apply_end_at')->nullable();
            // 开始时间
            $table->string('start_at')->nullable();
            // 结束时间
            $table->string('end_at')->nullable();

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
