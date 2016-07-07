<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlayerGameLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('player_game_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('game_url');
            $table->integer('game_id');
            $table->integer('opposing_team_id')->unsigned();
            $table->foreign('opposing_team_id')->references('id')->on('nba_teams');
            $table->boolean('home');
            $table->string('date');
            $table->boolean('win');
            $table->integer('score_for');
            $table->integer('score_opposing');
            $table->integer('min');
            $table->integer('fgm');
            $table->integer('fga');
            $table->float('fgp');
            $table->integer('tpm');
            $table->integer('tpa');
            $table->float('tpp');
            $table->integer('ftm');
            $table->integer('fta');
            $table->float('ftp');
            $table->integer('reb');
            $table->integer('ast');
            $table->integer('blk');
            $table->integer('stl');
            $table->integer('pf');
            $table->integer('to');
            $table->integer('pts');
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
    }
}
