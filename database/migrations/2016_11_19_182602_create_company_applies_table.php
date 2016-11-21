<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyAppliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_applies', function (Blueprint $table) {
            $table->increments('id');

            // 申请者
            $table->integer('user_id')->unsigned();
            $table->string('user_name');

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

            // 是否审核通过 [1: 未审核, 2: 已通过, 3: 已拒绝, 4: 已取消]
            $table->tinyInteger('status')->default(1);
            // 审核留言
            $table->text('message')->default('');
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('company_applies');
    }
}
