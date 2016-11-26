<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeedbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->increments('id');
            // 反馈者
            $table->integer('user_id')->unsigned();
            $table->string('user_name');

            // 反馈内容
            $table->text('content');
            // 反馈附带的图片
            $table->string('p1')->nullable();
            $table->string('p2')->nullable();
            $table->string('p3')->nullable();
            $table->string('p4')->nullable();
            $table->string('p5')->nullable();
            // 反馈内容类型 1：无分类 2：应用相关 3：功能相关
            $table->tinyInteger('type')->default(1);

            // 处理状态 1：未处理，2：已处理，3：搁置
            $table->tinyInteger('status')->default(1);
            // 处理时间
            $table->dateTime('dealt_at')->nullable();
            // 处理者留言
            $table->text('message')->default('');

            $table->timestamps();

            $table->index('user_id');
            $table->index('type');
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
        Schema::drop('feedbacks');
    }
}
