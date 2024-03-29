<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->comment('部门管理');
            $table->increments('id');
            $table->string('name')->nullable()->comment('部门名称');
            $table->string('code')->nullable()->comment('部门代码');
            $table->integer('parent_id')->default(new \Illuminate\Database\Query\Expression('0'))->nullable()->comment('上级部门ID');
            $table->integer('leader_user_id')->nullable()->comment('部门负责人');
            $table->string('third_party_id')->default(new \Illuminate\Database\Query\Expression('0'))->nullable()->comment('三方对应部门ID');
            $table->tinyInteger('type')->default(new \Illuminate\Database\Query\Expression('0'))->nullable()->comment('部门类型');
            $table->string('full_path')->default(new \Illuminate\Database\Query\Expression('-1'))->comment('层级');
            $table->integer('order')->default(new \Illuminate\Database\Query\Expression('0'))->comment('排序');
            $table->tinyInteger('state')->default(new \Illuminate\Database\Query\Expression('1'))->comment('状态0 禁用 1启用');
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
        Schema::dropIfExists('departments');
    }
};
