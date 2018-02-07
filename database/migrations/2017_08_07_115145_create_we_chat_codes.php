<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWeChatCodes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('we_chat_codes', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->nullable();
            $table->string('type')->nullable();
            $table->string('handler');
            $table->string('description');
            $table->string('scene');
            $table->json('data')->nullable();
            $table->string('ticket');
            $table->string('url');
            $table->dateTime('expire_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->unique('scene');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('we_chat_codes');
    }
}
