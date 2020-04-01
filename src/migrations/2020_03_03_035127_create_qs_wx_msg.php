<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQsWxMsg extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('qs_wx_msg', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->string('openid',50)->default('');
            $table->string('description',50)->default('');
            $table->string('to_user_name',50)->default('');
            $table->string('msg_type',50)->default('');
            $table->string('event',50)->default('');
            $table->string('status',50)->default('');
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
        Schema::dropIfExists('qs_wx_msg');
    }
}
