<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConversationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->increments('id');

            // `{用户1的id}c{用户2的id}` 这里用户1的id永远小于用户2
            $table->string('conversation_id');
            $table->integer('sender_id')->unsigned();
            $table->string('sender_name');
            $table->string('sender_avatar')->nullable();

            $table->string('content');

            $table->timestamps();

            $table->index('sender_id');
            $table->index('conversation_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('conversations');
    }
}
