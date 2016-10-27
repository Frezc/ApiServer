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
            $table->integer('message_id')->unsigned();

            $table->integer('sender_id')->unsigned();
            $table->string('sender_name');
            $table->string('sender_avatar')->nullable();

            $table->string('content');

            $table->timestamps();

            $table->index('sender_id');
            $table->index('message_id');
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
