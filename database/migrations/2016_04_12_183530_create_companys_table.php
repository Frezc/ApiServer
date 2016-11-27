<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companys', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            // 公司主页
            $table->string('url')->nullable();
            // 公司地址
            $table->string('address')->nullable();
            // 公司的logo
            $table->string('logo')->nullable();
            // 公司简介
            $table->string('description')->nullable();
            // 联系人
            $table->string('contact_person')->nullable();
            // 联系方式
            $table->string('contact')->nullable();
            // 营业执照
            $table->string('business_license');
            $table->timestamps();

            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('companys');
    }
}
