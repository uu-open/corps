<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->comment('员工信息管理');
            $table->increments('id');
            $table->string('employee_id')->nullable()->comment('员工ID');
            $table->string('avatar')->nullable()->comment('头像');
            $table->string('name')->nullable()->comment('姓名');
            $table->tinyInteger('gender')->default(0)->nullable()->comment('性别');
            $table->string('email')->nullable()->comment('邮箱地址');
            $table->string('mobile')->nullable()->comment('手机号码');
            $table->string('department_ids', 2000)->nullable()->comment('部门');
            $table->integer('department_id')->nullable()->comment('第一部门')->default(0);
            $table->string('position')->nullable()->comment('职位');
            $table->date('join_date')->nullable()->comment('入职日期');
            $table->string('dingtalk_id')->nullable()->comment('钉钉ID');
            $table->string('lark_id')->nullable()->comment('飞书ID');
            $table->string('wechat_work_id')->nullable()->comment('企业微信ID');
            $table->tinyInteger('state')->nullable()->comment('状态 0 未启用 1 正常 2 已禁用 3 已离职');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employees');
    }
};
