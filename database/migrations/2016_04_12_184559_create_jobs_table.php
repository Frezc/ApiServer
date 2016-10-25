<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->increments('id');
            // 工作名称
            $table->string('name');
            // 支付方式 0：线下支付，1：在线支付
            $table->tinyInteger('pay_way')->default(0);
            // 工资类型，0：面议, 1：固定数值
            $table->tinyInteger('salary_type')->default(0);
            // 工资
            $table->string('salary', 16);
            $table->string('description')->nullable();
            // 访问次数
            $table->integer('visited')->unsigned()->default(0);
            // 工作时间, 下个版本会改
//            $table->string('time')->default('非固定');
            // 如果是以商家为名发布则不为空
            $table->integer('company_id')->unsigned()->nullable();
            $table->string('company_name')->nullable();
            // 创建者的id
            $table->integer('creator_id')->unsigned();
            // 岗位是否活跃，0表示不活跃,1表示活跃
            $table->tinyInteger('active')->default(1);
            $table->timestamps();

            $table->index(['name', 'active']);
            $table->index(['company_id', 'active']);
            $table->index(['company_name', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('jobs');
    }
}
