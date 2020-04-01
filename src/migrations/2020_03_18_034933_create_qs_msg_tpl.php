<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQsMsgTpl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('qs_msg_tpl');
        Schema::create('qs_msg_tpl', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name',50)->default('');
            $table->string('title',50)->default('');
            $table->string('sms_content',500)->default('');
            $table->string('wx_content',500)->default('');
            $table->string('options',100)->default('');
            $table->integer('create_date')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('qs_msg_tpl');
    }
}
