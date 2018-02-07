<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWeChatRefunds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('we_chat_refunds', function(Blueprint $table) {
            $table->increments('id');
            $table->string('status')->nullable();
            $table->string('out_trade_no')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('out_refund_no')->nullable();
            $table->integer('total_fee')->nullable();
            $table->integer('refund_fee')->nullable();
            $table->string('refund_fee_type')->nullable();
            $table->string('refund_desc')->nullable();
            $table->string('refund_account')->nullable();
            $table->string('return_code')->nullable();
            $table->string('return_msg')->nullable();
            $table->string('result_code')->nullable();
            $table->string('err_code')->nullable();
            $table->string('err_code_des')->nullable();
            $table->string('refund_id')->nullable();
            $table->string('refund_recv_accout')->nullable();
            $table->string('refund_success_time')->nullable();
            $table->timestamps();

            $table->index('out_trade_no');
            $table->index('transaction_id');
            $table->unique('out_refund_no');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('we_chat_refunds');
    }
}
