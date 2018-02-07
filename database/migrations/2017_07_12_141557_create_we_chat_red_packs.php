<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWeChatRedPacks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('we_chat_red_packs', function(Blueprint $table) {
            $table->increments('id');
            $table->string('status')->nullable();
            $table->string('mch_billno')->nullable();
            $table->string('send_name');
            $table->string('re_openid');
            $table->integer('total_num');
            $table->integer('total_amount');
            $table->string('wishing');
            $table->string('act_name');
            $table->string('remark');
            $table->string('send_listid')->nullable();
            $table->string('return_code')->nullable();
            $table->string('return_msg')->nullable();
            $table->string('result_code')->nullable();
            $table->string('err_code')->nullable();
            $table->string('err_code_des')->nullable();
            $table->string('send_time')->nullable();
            $table->string('refund_time')->nullable();
            $table->integer('refund_amount')->nullable();
            $table->string('rcv_time')->nullable();
            $table->timestamps();

            $table->unique('mch_billno');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('we_chat_red_packs');
    }
}
