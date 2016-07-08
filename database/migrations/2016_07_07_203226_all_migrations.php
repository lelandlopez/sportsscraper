<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AllMigrations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('games', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('game_type');
            $table->string('game_url');
            $table->timestamps();
        });
        Schema::create('nba_teams', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('nickname');
            $table->timestamps();
        });
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
        Schema::create('player_game_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('game_url');
            $table->integer('player_id')->unsigned();
            $table->foreign('player_id')->references('id')->on('players');
            $table->integer('game_id')->unsigned();
            $table->foreign('game_id')->references('id')->on('games');
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
        Schema::drop('player_game_logs');
        Schema::drop('players');
        Schema::drop('nba_teams');
        Schema::drop('games');
    }
}
