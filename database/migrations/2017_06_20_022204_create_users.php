<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function(Blueprint $table) {
            $table->increments('id');
            $table->string('serial_number');
            $table->string('union_id');

            $table->string('name')->nullable();
            $table->string('mobile')->nullable();

            $table->string('nickname')->nullable();
            $table->string('avatar')->nullable();
            $table->tinyInteger('gender')->nullable();

            $table->string('country')->nullable();
            $table->string('province')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();

            $table->timestamps();

            $table->unique('serial_number');
            $table->unique('union_id');
            $table->index('nickname');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users');
    }
}
