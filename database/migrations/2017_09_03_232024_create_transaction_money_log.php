<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionMoneyLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_money_log', function (Blueprint $table) {

            $table->increments('id');
            $table->integer('user_id');
            $table->integer('to_user_id');
            $table->integer('order_id');
            $table->string('log');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::drop('transaction_money_log');

    }
}
