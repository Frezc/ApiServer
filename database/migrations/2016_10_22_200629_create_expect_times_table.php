<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExpectTimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expect_times', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('expect_job_id')->unsigned();

            $table->smallInteger('year')->unsigned();
            $table->tinyInteger('month')->unsigned();
            $table->tinyInteger('dayS')->unsigned();
            $table->tinyInteger('dayE')->nullable()->unsigned();
//            $table->tinyInteger('hourS')->nullable()->unsigned();
//            $table->tinyInteger('hourE')->nullable()->unsigned();
//            $table->tinyInteger('minuteS')->nullable()->unsigned();
//            $table->tinyInteger('minuteE')->nullable()->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('expect_times');
    }
}
