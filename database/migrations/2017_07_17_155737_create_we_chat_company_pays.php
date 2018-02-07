<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWeChatCompanyPays extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('we_chat_company_pays', function(Blueprint $table) {
            $table->increments('id');
            $table->string('status');
            $table->string('partner_trade_no');
            $table->string('openid');
            $table->string('check_name');
            $table->string('re_user_name')->nullable();
            $table->integer('amount');
            $table->string('desc');
            $table->string('spbill_create_ip');
            $table->string('return_code')->nullable();
            $table->string('return_msg')->nullable();
            $table->string('result_code')->nullable();
            $table->string('err_code')->nullable();
            $table->string('err_code_des')->nullable();
            $table->string('payment_no')->nullable();
            $table->dateTime('payment_time')->nullable();
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
        Schema::drop('we_chat_company_pays');
    }
}
