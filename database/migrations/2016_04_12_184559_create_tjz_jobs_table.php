<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTjzJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tjz_jobs', function (Blueprint $table) {
            $table->increments('id');
            // 工作名称
            $table->string('name');
            // 支付方式 1：线下支付，2：在线支付
            $table->tinyInteger('pay_way')->default(1)->nullable();
            // 工资类型，1：面议, 2：固定数值
            $table->tinyInteger('salary_type')->default(1)->nullable();

            // 工资 放到 job_time里
//            $table->string('salary', 16);
            $table->string('description')->nullable();
            // 访问次数
            $table->integer('visited')->unsigned()->default(0);
            // 工作时间, 下个版本会改
//            $table->string('time')->default('非固定');
            // 如果是以商家为名发布则不为空
            $table->integer('company_id')->unsigned()->nullable();
            $table->string('company_name')->nullable();
            // 创建者
            $table->integer('creator_id')->unsigned();
            $table->string('creator_name');

            // 工资
            $table->string('salary')->nullable();

            // 岗位是否活跃，0表示不活跃（下架）,1表示活跃
            $table->tinyInteger('active')->default(1);
             //工作类型
            $table->string('job_type');

            // 所在城市
            $table->string('city');
            // 详细地址
            $table->string('address');
          
            //联系人的电话
            $table->string('contact');
            $table->string('position');

            // 为了能按照分数高低来排序所以加入字段
            $table->integer('number_evaluate')->default(0);
            $table->float('average_score')->default(0);

            // 岗位类型
            $table->string('type')->default('其他');

            $table->softDeletes();
            $table->timestamps();
            $table->index('name');
            $table->index('company_id');
            $table->index('company_name');
            $table->index('creator_id');
            $table->index('creator_name');
            $table->index('active');
            $table->index('job_type');
            $table->index('type');
            $table->index('city');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tjz_jobs');
    }
}
