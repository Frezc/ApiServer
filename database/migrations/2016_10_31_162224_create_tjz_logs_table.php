<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTjzLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tjz_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ip');
            $table->integer('user_id')->unsigned();
            $table->string('method');
            $table->string('url');
            $table->text('params');
            $table->timestamps();

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tjz_logs');
    }
}
