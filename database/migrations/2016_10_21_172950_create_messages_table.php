<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('sender_id')->unsigned()->nullable();
            $table->string('sender_name');
            $table->string('sender_avatar')->nullable();

            $table->integer('receiver_id')->unsigned();

            // 消息的类型 ['notification', 'conversation']
            $table->string('type')->default('notification');
            $table->string('content');
            // 未读信息数
            $table->tinyInteger('unread')->default(0);

            $table->timestamps();

            $table->index('receiver_id');
            $table->index('sender_id');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('messages');
    }
}
