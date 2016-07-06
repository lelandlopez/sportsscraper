<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNbaPlayers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('players', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('url');
            $table->timestamps();
            $table->string('player_position');
            $table->string('college');
            $table->string('weight');
            $table->string('birth_date');
            $table->integer('height_feet');
            $table->integer('height_inches');
            $table->integer('experience');
            $table->integer('draft_year');
            $table->boolean('drafted');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
