<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobApplyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_apply', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('job_id')->unsigned();
            $table->integer('resume_id')->unsigned();
            $table->string('description')->nullable();
            // 0：申请中，1：申请成功，2：申请失败
            $table->tinyInteger('status')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'status']);
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
        Schema::drop('job_apply');
    }
}
