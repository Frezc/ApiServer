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
            // 工资
            $table->string('salary', 16);
            $table->string('description')->nullable();
            // 人数限制（null为无限制）
            $table->mediumInteger('number')->unsigned()->nullable();
            // 已经申请成功的人数
            $table->mediumInteger('number_applied')->unsigned()->default(0);
            // 访问次数
            $table->integer('visited')->unsigned()->default(0);
            // 工作时间, 下个版本会改
            $table->string('time')->default('非固定');
            $table->integer('company_id')->unsigned();
            $table->string('company_name');
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
