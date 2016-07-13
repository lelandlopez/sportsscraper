<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTeamIdsToGamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('games', function($table)
        {
            $table->integer('home_team_id')->unsigned();
            $table->foreign('home_team_id')->references('id')->on('nba_teams');
            $table->integer('away_team_id')->unsigned();
            $table->foreign('away_team_id')->references('id')->on('nba_teams');
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
