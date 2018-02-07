<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointTallies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_tallies', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('point');
            $table->string('from');
            $table->string('to');
            $table->string('link_type')->nullable();
            $table->integer('link_id')->nullable();
            $table->string('operator');
            $table->string('remark')->nullable();
            $table->string('type')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index(['link_type', 'link_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_tallies');
    }
}
