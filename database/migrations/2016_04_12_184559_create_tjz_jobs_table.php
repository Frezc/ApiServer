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
            // 直接存取支付方式便
            $table->string('pay_way')->default(1)->nullable();
           //直接存支付方式
            $table->string('salary_type')->default(1)->nullable();
            //   工资
            $table->string('salary')->nullable();
            //工作描述
            $table->string('description')->nullable();
            // 访问次数
            $table->integer('visited')->unsigned()->default(0);
            // 工作公司id
            $table->integer('company_id')->unsigned()->nullable();
            //工作公司名字
            $table->string('company_name')->nullable();
            // 创建者id
            $table->integer('creator_id')->unsigned();
            //创建者名字
            $table->string('creator_name');

            // 岗位是否活跃，0表示不活跃（下架）,1表示活跃
            $table->tinyInteger('active')->default(1);
            // 所在城市
            $table->string('city');
            // 详细地址
            $table->string('address')->nullable();
            //联系人的电话
            $table->string('contact');
            // 联系人的名字
            $table->string('contact_person', 16);

            $table->string('type')->default('其他');
            //岗位申请人数 默认是0
            $table->integer('apply_number')->defualt(0);
            //需求人数
            $table->integer('required_number')->defualt(0);

            $table->softDeletes();
            $table->timestamps();
            $table->index('name');
            $table->index('company_id');
            $table->index('company_name');
            $table->index('creator_id');
            $table->index('creator_name');
            $table->index('active');
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
