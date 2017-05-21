<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->nullable();

            $table->double('money')->unsigned()->default(0);
            // 手机号
            $table->string('phone', 20)->nullable(); 
            // 昵称
            $table->string('nickname', 32)->default('guy');
            $table->string('password');
            // 头像url
            $table->string('avatar')->nullable();
            // 个性签名
            $table->string('sign')->nullable();
            $table->date('birthday')->nullable();
            // 地点
            $table->string('location')->nullable();
            // 性别 0 为男 1为女
            $table->tinyInteger('sex')->default(0);
            // 公司id
            $table->integer('company_id')->unsigned()->nullable();
            // 公司名字
            $table->string('company_name')->nullable();
            // 是否通过邮箱，0：未通过 1：已通过
            $table->tinyInteger('email_verified')->default(0);
            // 角色的id
            $table->tinyInteger('role_id')->default(1);
            //发布者的职位
            $table->string('position')->default('招聘者');
            // 是否通过实名验证
            $table->tinyInteger('real_name_verified')->default(0);

            $table->timestamps();

            $table->unique('email');
            $table->unique('phone');
            $table->index('nickname');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users');
    }
}
