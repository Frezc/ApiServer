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
            $table->string('salary_time');
            $table->string('creator_name');
            // 岗位是否活跃，0表示不活跃,1表示活跃
            $table->tinyInteger('active')->default(1);
                //statu 0 表示已经通过申请切没有该工作了，1表示该工作可以申请，2表示该工作有人申请
            $table->tinyInteger('statu')->default(1) ;
             //工作类型
            $table->string('job_type');
          
            //联系人的电话
            $table->string('contact_number');
            $table->string('position');
                
            $table->timestamps();
            $table->index(['name', 'active']);
            $table->index(['company_id', 'active']);
            $table->index(['company_name', 'active']);
            $table->index('creator_name');
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
