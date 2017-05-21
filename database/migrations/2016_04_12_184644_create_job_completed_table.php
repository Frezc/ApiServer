<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobCompletedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_completed', function (Blueprint $table) {
            $table->increments('id');
            //申请者id
            $table->integer('user_id')->unsigned();
            //工作id
            $table->integer('job_id')->unsigned();
            //简历id
            $table->integer('resume_id')->unsigned();
            //对该工作的评价
            $table->string('description')->nullable();
            $table->timestamps();
            $table->index('user_id');
            $table->index('job_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('job_completed');
    }
}
