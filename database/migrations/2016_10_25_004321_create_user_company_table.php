<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserCompanyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 用户和企业的对应表（多对多）
        Schema::create('user_company', function (Blueprint $table) {
            $table->increments('id');
            // 用户
            $table->integer('user_id');
            $table->string('user_name');
            // 企业
            $table->integer('company_id');
            $table->string('company_name');
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
        Schema::drop('user_company');
    }
}
