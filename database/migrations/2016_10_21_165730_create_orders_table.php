<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('job_id')->unsigned();
            $table->integer('job_time_id')->unsigned();
            // 用户申请所提供的数据
            $table->integer('expect_job_id')->unsigned();

            // 应聘者id
            $table->integer('applicant_id')->unsigned();
            // 招聘者类型 0：个人，1：商家
            $table->tinyInteger('recruiter_type')->default(0);
            $table->integer('recruiter_id')->unsigned();

            // 订单状态 0：创建，1：确认、未开始、进行中、已结束，2：已完成，3：已取消
            $table->tinyInteger('status')->default(0);

            // 用户和招聘者的确认情况
            $table->tinyInteger('applicant_check')->default(0);
            $table->tinyInteger('recruiter_check')->default(0);

            // 是否已支付
            $table->tinyInteger('has_paid')->default(0);

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
        Schema::drop('orders');
    }
}
