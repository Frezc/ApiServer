<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobEvaluateTable extends Migration
{
    /**
     * Run the migrations.
     * 应聘者对岗位的评价
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_evaluate', function (Blueprint $table) {
            $table->increments('id');
            // 评价者
            $table->integer('user_id')->unsigned();

            $table->string('user_name');
            // 对应订单的id
            $table->integer('order_id')->unsigned();
            // 对应job的id
            $table->integer('job_id')->unsigned();
            // 1~5的整数
            $table->tinyInteger('score');
            // 评论
            $table->text('comment')->nullable();
            // 附图
            $table->text('pictures')->default('');
            $table->timestamps();

            $table->index('user_id');
            $table->index('order_id');
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
        Schema::drop('job_evaluate');
    }
}
