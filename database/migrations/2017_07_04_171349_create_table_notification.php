<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableNotification extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('msg_id')->comment('消息id');
            $table->string('title');
            $table->string('body');
            $table->string('url')->nullable();
            $table->string('pic')->nullable();
            $table->string('type')->comment('通知类型');
            $table->string('status')->comment('通知状态');

            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
            $table->index('msg_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('notifications');
    }
}
