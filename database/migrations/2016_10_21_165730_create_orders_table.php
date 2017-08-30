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
            //工作id
            $table->integer('job_id')->unsigned();
            //工作名字
            $table->string('job_name');
            //工作工资
            $table->string('salary');
            //工作时间id
            $table->string('salary_type');
            $table->integer('job_time_id')->unsigned()->nullable();
            // 支付方式 1：线下支付，2：在线支付
            $table->tinyInteger('pay_way')->default(1)->nullable();
            // 应聘者id
            $table->integer('applicant_id')->unsigned();
            //应聘者名字
            $table->string('applicant_name');
            // 招聘者类型 0：个人，1：商家
            $table->tinyInteger('recruiter_type')->default(0);
            //发布者的id
            $table->integer('recruiter_id')->unsigned();
            //发布者的的名字
            $table->string('recruiter_name');
            // 订单状态 0：创建，1：成功，2：已完成，3：已关闭  4.失败
            $table->tinyInteger('status')->default(0);
            // 用来区分订单是由谁关闭的 1： 应聘者 2： 招聘者 3：管理员 4：系统
            $table->tinyInteger('close_type')->nullable();
            // 关闭理由
            $table->text('close_reason')->nullable();
            // 用户和招聘者的确认情况
            $table->tinyInteger('applicant_check')->default(0);
            $table->tinyInteger('recruiter_check')->default(0);
            // 是否已支付
            $table->tinyInteger('has_paid')->default(0);
            $table->timestamps();
            $table->index('applicant_id');
            $table->index(['recruiter_type', 'recruiter_id']);
            $table->index('status');
            $table->index('applicant_check');
            $table->index('recruiter_check');
            $table->index('has_paid');
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
