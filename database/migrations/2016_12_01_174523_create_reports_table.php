<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportsTable extends Migration
{
    /**
     * 举报信息
     */
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->increments('id');
            // 投诉者信息
            $table->integer('user_id');
            $table->string('user_name');
            $table->text('content');
            $table->text('pictures')->default('');

            // 'order': 订单, 'user': 用户, 'company': 企业, 'job': 岗位, 'expect_job': 公开简历
            $table->string('target_type', 12);
            $table->integer('target_id');

            // 处理状态 1：未处理，2：已处理，3：搁置
            $table->tinyInteger('status')->default(1);
            // 处理时间
            $table->dateTime('dealt_at')->nullable();
            // 处理者留言
            $table->text('message')->default('');

            $table->timestamps();

            $table->index(['target_type', 'target_id']);
            $table->index('user_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('reports');
    }
}
