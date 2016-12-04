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
            $table->string('job_name');
            $table->integer('job_time_id')->unsigned()->nullable();
            // 用户申请所提供的数据
            $table->integer('expect_job_id')->unsigned();

            // 应聘者id
            $table->integer('applicant_id')->unsigned();
            $table->string('applicant_name');
            // 招聘者类型 0：个人，1：商家
            $table->tinyInteger('recruiter_type')->default(0);
            $table->integer('recruiter_id')->unsigned();
            $table->string('recruiter_name');

            // 订单状态 0：创建，1：确认、未开始、进行中，2：已完成，3：已关闭
            $table->tinyInteger('status')->default(0);

            // 用来区分订单是由谁关闭的 1： 应聘者 2： 招聘者 3：管理员
            $table->tinyInteger('close_type')->nullable();

            // 用户和招聘者的确认情况
            $table->tinyInteger('applicant_check')->default(0);
            $table->tinyInteger('recruiter_check')->default(0);

            // 是否已支付
            $table->tinyInteger('has_paid')->default(0);

            // 评价情况 0：未评价 1：已评价，前面的数字代表应聘者，后面的数字代表商家
            $table->string('evaluate_status', 4)->default('00');

            $table->timestamps();

            $table->index('applicant_id');
            $table->index(['recruiter_type', 'recruiter_id']);
            $table->index('status');
            $table->index('applicant_check');
            $table->index('recruiter_check');
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
