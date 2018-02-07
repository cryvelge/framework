<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWeChatPays extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('we_chat_pays', function(Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('detail');
            $table->string('serial_number');
            $table->integer('price');
            $table->boolean('use_as_withdraw')->default(true);
            $table->integer('already_refund')->default(false);
            $table->string('prepay_id')->nullable();
            $table->boolean('notified')->default(false);
            $table->boolean('handled')->default(false);
            $table->string('return_code')->nullable();
            $table->string('return_msg')->nullable();
            $table->string('result_code')->nullable();
            $table->string('err_code')->nullable();
            $table->string('err_code_des')->nullable();
            $table->string('openid')->nullable();
            $table->string('is_subscribe')->nullable();
            $table->string('trade_type')->nullable();
            $table->string('bank_type')->nullable();
            $table->integer('total_fee')->nullable();
            $table->integer('settlement_total_fee')->nullable();
            $table->string('fee_type')->nullable();
            $table->integer('cash_fee')->nullable();
            $table->string('cash_fee_type')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('out_trade_no')->nullable();
            $table->string('attach')->nullable();
            $table->timestamp('time_end')->nullable();
            $table->timestamps();

            $table->unique('serial_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('we_chat_pays');
    }
}
