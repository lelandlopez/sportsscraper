<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Goutte\Client;

use App\Http\Requests;

use App\Player;

class SportScraperController extends Controller
{
    public function asdf () {
    	$client = new Client();
    	$crawler = $client->request('GET', 'http://espn.go.com/nba/player/gamelog/_/id/3975/stephen-curry');	
    	$crawler->filter('[class*="oddrow"][class*="team"], [class*="evenrow"][class*="team"]')->each(function ($gamerows) {
    		echo "asdf";
	    	$gamerows->filter('td')->each(function ($statcolumn, $i) {
	    		if($i == 1) {
	    			if($statcolumn->filter('li.game-location')->count() != 0) {
		    			print $statcolumn->filter('li.game-location')->first()->text();
		    			print "<br>";
	    			}
	    			if($statcolumn->filter('li.team-name')->count() != 0 && $statcolumn->filter('li.team-name > a')->count() != 0) {
		    			print '<a href="'. $statcolumn->filter('li.team-name > a')->link()->getUri() . '">' . $statcolumn->filter('li.team-name')->first()->text() . '</a>';
		    		}
	    			print "<br>";
	    		}
	    		else {
		    		print $i . " : " . $statcolumn->text();
		    		print "<br>";
	    		}
			});
			print "<br>";
		});
    }

    public function scrapePlayerUrls() {
    	$client = new Client();
    	$crawler = $client->request('GET', 'http://espn.go.com/nba/format/player/design09/dropdown?teamId=undefined&posId=undefined&lang=en');	
    	$crawler->filter('ul.main-items > li > a')->each(function ($teamrows) use ($crawler){
    		$team_name = $teamrows->text();
    		print $team_name;
    		print "<br>";
	    	$crawler->filter('ul.split-level-content-list[id="'. $teamrows->attr("id") .'list"] > li')->each(function ($player) {
	    		$player_name = $player->text();
	    		$player_url = $player->filter('a')->link()->getUri();
	    		print $player_name . ":" . $player_url;
	    		print "<br>";
	    		$player = new Player;
	    		$player->name = $player_name;
	    		$player->url = $player_url;	
	    		$player_id_last_slash = strrpos($player_url, '/');
	    		$player_id_second_last_slash_string = substr($player_url, 0, $player_id_last_slash);
	    		$player_id_second_last_slash = strrpos($player_id_second_last_slash_string, '/');
	    		$player_id = substr($player_url, $player_id_second_last_slash + 1, $player_id_last_slash - $player_id_second_last_slash - 1);
	    		print $player_id;
	    		print "<br>";
	    		//$player->save();
	    	});
	    	print "<br>";

    	});
    }
}
