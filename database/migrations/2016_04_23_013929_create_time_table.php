<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTimeTable extends Migration
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
            $table->smallInteger('year')->unsigned();
            $table->tinyInteger('month')->unsigned();
            $table->tinyInteger('dayS')->unsigned();
            $table->tinyInteger('dayE')->nullable()->unsigned();
            $table->tinyInteger('hourS')->unsigned();
            $table->tinyInteger('hourE')->nullable()->unsigned();
            $table->timestamps();

            $table->index(['job_id', 'year', 'month']);
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
