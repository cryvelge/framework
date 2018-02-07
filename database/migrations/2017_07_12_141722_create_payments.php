<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function(Blueprint $table) {
            $table->increments('id');
            $table->string('order_type');
            $table->integer('order_id');
            $table->integer('user_id');
            $table->string('serial_number');
            $table->string('type');
            $table->integer('price');
            $table->string('status')->nullable();
            $table->string('parameters')->nullable();
            $table->string('fail_reason')->nullable();
            $table->string('pay_time')->nullable();
            $table->boolean('can_refund')->default(true);
            $table->timestamps();

            $table->index('order_id');
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
        Schema::drop('payments');
    }
}
