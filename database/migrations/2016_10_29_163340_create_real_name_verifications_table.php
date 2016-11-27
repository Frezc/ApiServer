<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRealNameVerificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('real_name_verifications', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('user_name');
            $table->string('real_name', 16);
            $table->string('id_number', 24);
            // 验证图片的url
            $table->string('verifi_pic');
            // 是否审核通过 [1: 未审核, 2: 已通过, 3: 已拒绝, 4: 已取消]
            $table->tinyInteger('status')->default(1);
            // 审核者留言
            $table->text('message')->default('');
            $table->timestamps();
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('real_name_verifications');
    }
}
