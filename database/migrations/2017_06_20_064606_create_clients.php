<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClients extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('open_id');
            $table->string('type');
            $table->string('session_key')->nullable();

            $table->boolean('subscribe')->default(false);
            $table->dateTime('subscribe_at')->nullable();
            $table->dateTime('unsubscribe_at')->nullable();
            $table->dateTime('first_subscribe_at')->nullable();

            $table->timestamps();

            $table->unique(['type', 'open_id']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
