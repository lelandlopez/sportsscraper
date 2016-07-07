<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Goutte\Client;

use App\Http\Requests;

use App\Player;
use App\Player_Game_Log;
use App\NBA_Team;

class SportScraperController extends Controller
{

	public function init() {
		SportScraperController::scrapePlayerUrls();
		SportScraperController::scrape_nba_teams();
		SportScraperController::update_players_info();

	}

	public function scrape_players_game_info() {
    	$players = Player::all();
    	foreach($players as $player) {
    		SportScraperController::scrape_player_game_info($player->url, $player->id);
    		sleep(0.5);
    	}

	}

	public function scrape_nba_teams() {
    	$client = new Client();
    	$crawler = $client->request('GET', 'http://espn.go.com/nba/format/player/design09/dropdown?teamId=undefined&posId=undefined&lang=en');	
    	$crawler->filter('ul.main-items > li > a')->each(function ($teamrows) use ($crawler){
    		$team_name = $teamrows->text();
    		$nba_team = new NBA_Team();
    		$nba_team->name = $team_name;
    		$nba_team->nickname = strtoupper(substr($team_name, 0, 3));
    		if($team_name == "New York") {
    			$nba_team->nickname = "NY";
    		}
    		if($team_name == "Brooklyn") {
    			$nba_team->nickname = "BKN";
    		}
    		if($team_name == "LA Clippers") {
    			$nba_team->nickname = "LAC";
    		}
    		if($team_name == "LA Lakers") {
    			$nba_team->nickname = "LAL";
    		}
    		if($team_name == "San Antonio") {
    			$nba_team->nickname = "SA";
    		}
    		if($team_name == "Oklahoma City") {
    			$nba_team->nickname = "OKC";
    		}
    		if($team_name == "Phoenix") {
    			$nba_team->nickname = "PHX";
    		}
    		if($team_name == "Washington") {
    			$nba_team->nickname = "WSH";
    		}
    		if($team_name == "Utah") {
    			$nba_team->nickname = "Utah";
    		}
    		if($team_name == "New Orleans") {
    			$nba_team->nickname = "NO";
    		}
    		$nba_team->save();
    	});
	}

    public function scrape_player_game_info ($url = "http://espn.go.com/nba/player/gamelog/_/id/3975/stephen-curry", $player_id = 3975) {
    	$client = new Client();
    	$crawler = $client->request('GET', $url);	
    	$crawler->filter('[class*="oddrow"][class*="team"], [class*="evenrow"][class*="team"]')->each(function ($gamerows) use ($player_id) {
	    	$game_log = new Player_Game_Log();
	    	$game_log->player_id = $player_id;
	    	$gamerows->filter('td')->each(function ($statcolumn, $i) use ($game_log) {
	    		if($i == 0) {
	    			$date = $statcolumn->text();
	    			$game_log->date = $date;
	    			print $date;
	    			print "<br>";
	    		} else if($i == 1) {
	    			if($statcolumn->filter('li.game-location')->count() != 0) {
		    			print $statcolumn->filter('li.game-location')->first()->text();
		    			print "<br>";
		    			$game_location = $statcolumn->filter('li.game-location')->first()->text();
		    			if($game_location == "vs") {
		    				$game_log->home = true;
		    			} else {
		    				$game_log->home = false;
		    			}
	    			}
	    			if($statcolumn->filter('li.team-name')->count() != 0 && $statcolumn->filter('li.team-name > a')->count() != 0) {
		    			print '<a href="'. $statcolumn->filter('li.team-name > a')->link()->getUri() . '">' . $statcolumn->filter('li.team-name')->first()->text() . '</a>';
		    			$team_name = $statcolumn->filter('li.team-name')->first()->text();
		    			$team = NBA_Team::where("nickname", $team_name)->get()->first();
		    			$game_log->opposing_team_id = $team->id;
		    			print "<br>";
		    			//TODO add opposing team
		    		}
	    			print "<br>";
	    		} else if($i == 2) {
	    			$wini = strpos($statcolumn->text(), " ");
	    			$win = substr($statcolumn->text(), 0, $wini);
	    			$pointsfori = strpos($statcolumn->text(), "-");
	    			$pointsfor = substr($statcolumn->text(), $wini + 1, $pointsfori - $wini - 1);
	    			$pointsagainst = substr($statcolumn->text(), $pointsfori + 1, strlen($statcolumn->text()) - $pointsfori + 1);
	    			if($win == "W") {
	    				$game_log->win = true;
	    			} else if($win == "L") {
	    				$game_log->win = false;
	    			}
    				$game_log->score_for = $pointsfor;
    				$game_log->score_opposing = $pointsagainst;
	    			print $win;
	    			print "<br>";
	    			print $pointsfor;
	    			print "<br>";
	    			print $pointsagainst;
	    			print "<br>";
	    		} else if($i == 3) {
	    			$min = $statcolumn->text();		
    				$game_log->min = $min;
	    			print $min;
	    			print "<br>";
	    		} 
	    		else if($i == 4) {
	    			$fgmi = strpos($statcolumn->text(), "-");
	    			$fgm = substr($statcolumn->text(), 0, $fgmi);
	    			$fga = substr($statcolumn->text(), $fgmi + 1, strlen($statcolumn->text()) - $fgmi);
    				$game_log->fgm = $fgm;
    				$game_log->fga = $fga;
	    			print $fgm;
	    			print "<br>";
	    			print $fga;
	    			print "<br>";

	    		} else if($i == 5) {
	    			$fgp = $statcolumn->text();		
    				$game_log->fgp = $fgp;
	    			print $fgp;
	    			print "<br>";
	    		}
	    		else if($i == 6) {
	    			$tpmi = strpos($statcolumn->text(), "-");
	    			$tpm = substr($statcolumn->text(), 0, $tpmi);
	    			$tpa = substr($statcolumn->text(), $tpmi + 1, strlen($statcolumn->text()) - $tpmi);
    				$game_log->tpm = $tpm;
    				$game_log->tpa = $tpa;
	    			print $tpm;
	    			print "<br>";
	    			print $tpa;
	    			print "<br>";

	    		}
	    		else if($i == 7) {
	    			$tpp = $statcolumn->text();		
    				$game_log->tpp = $tpp;
	    			print $tpp;
	    			print "<br>";
	    		}
	    		else if($i == 8) {
	    			$ftmi = strpos($statcolumn->text(), "-");
	    			$ftm = substr($statcolumn->text(), 0, $ftmi);
	    			$fta = substr($statcolumn->text(), $ftmi + 1, strlen($statcolumn->text()) - $ftmi);
    				$game_log->ftm = $ftm;
    				$game_log->fta = $fta;
	    			print $ftm;
	    			print "<br>";
	    			print $fta;
	    			print "<br>";
	    		}
	    		else if($i == 9) {
	    			$ftp = $statcolumn->text();		
    				$game_log->ftp = $ftp;
	    			print $ftp;
	    			print "<br>";
	    		}
	    		else if($i == 10) {
	    			$reb = $statcolumn->text();		
    				$game_log->reb = $reb;
	    			print $reb;
	    			print "<br>";
	    		}
	    		else if($i == 11) {
	    			$ast = $statcolumn->text();		
    				$game_log->ast = $ast;
	    			print $ast;
	    			print "<br>";
	    		}
	    		else if($i == 12) {
	    			$blk = $statcolumn->text();		
    				$game_log->blk = $blk;
	    			print $blk;
	    			print "<br>";
	    		}
	    		else if($i == 13) {
	    			$stl = $statcolumn->text();		
    				$game_log->stl = $stl;
	    			print $stl;
	    			print "<br>";
	    		}
	    		else if($i == 14) {
	    			$pf = $statcolumn->text();		
    				$game_log->pf = $pf;
	    			print $pf;
	    			print "<br>";
	    		}
	    		else if($i == 15) {
	    			$to = $statcolumn->text();		
    				$game_log->to = $to;
	    			print $to;
	    			print "<br>";
	    		}
	    		else if($i == 16) {
	    			$pts = $statcolumn->text();		
    				$game_log->pts = $pts;
	    			print $pts;
	    			print "<br>";
	    		}
	    		else {
		    		print $i . " : " . $statcolumn->text();
		    		print "<br>";
	    		}
			});
			if($game_log->opposing_team_id == "") {
				$game_log->opposing_team_id = 31;
			}
			$game_log->save();
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
	    		$player_id_last_slash = strrpos($player_url, '/');
	    		$player_id_second_last_slash_string = substr($player_url, 0, $player_id_last_slash);
	    		$player_id_second_last_slash = strrpos($player_id_second_last_slash_string, '/');
	    		$player_id = substr($player_url, $player_id_second_last_slash + 1, $player_id_last_slash - $player_id_second_last_slash - 1);

	    		//insert into database
	    		if(!Player::find($player_id)) {
		    		print $player_name . ":" . $player_url;
		    		print "<br>";
		    		print $player_id;
		    		print "<br>";
		    		$player = new Player;
		    		$player->name = $player_name;
		    		$player->url = $player_url;	
		    		$player->id = $player_id;
		    		$player->save();
		    	}

	    	});
	    	print "<br>";

    	});
    }

    public function scrapePlayerInfo($url = "http://espn.go.com/nba/player/_/id/261/kevin-garnett") {
		$id = getIdFromUrl($url);
		if(!Player::find($id)) {
			return;
		} else {
			$player = Player::find($id);
		}
    	$client = new Client();
    	$crawler = $client->request('GET', $url);	
    	$crawler->filter('ul.general-info > li')->each(function ($height_string, $i) use ($player){
    		if($i == 0) {
    			$si = strpos($height_string->text(), ' ');
    			$player_position = substr($height_string->text(), $si, strlen($height_string->text()));
    			$player->player_position = $player_position;
	    		print $player_position;
	    		print "<br>";
    		} else if($i == 1) {
    			$foot_index = strpos($height_string->text(), '\'');
    			$player_foot = substr($height_string->text(), 0, $foot_index);
    			$inch_index = strpos($height_string->text(), '"');
    			$player_inch = substr($height_string->text(), $foot_index + 1, $inch_index - $foot_index - 1);
    			$weight_index_start = strpos($height_string->text(), ',');
    			$weight_index_end = strpos($height_string->text(), "lbs");
    			$weight = substr($height_string->text(), $weight_index_start + 2, $weight_index_end + 2 - $weight_index_start - 5);
    			$player->height_feet = $player_foot;
    			$player->height_inches = $player_inch;
    			$player->weight = $weight;
	    		print $player_foot;
	    		print "<br>";
	    		print $player_inch;
	    		print "<br>";
	    		print $weight;
	    		print "<br>";
    		}
    	});
    	$crawler->filter('ul.player-metadata > li')->each(function ($meta_data, $i) use ($player) {
    		$meta_data = $meta_data->text();
    		if(preg_match("/Born/", $meta_data)) {
    			$string_end = strpos($meta_data, "in");
				$birth_date = substr($meta_data, 4, $string_end - 4 - 1);
				print $birth_date;
				$player->birth_date = $birth_date;
    		} else if(preg_match("/Drafted/", $meta_data)) {
    			$string_end = strpos($meta_data, ':');
				$draft_year = substr($meta_data, 7, $string_end - 7);
				print $draft_year;
				$player->draft_year = $draft_year;
				$player->drafted = true;
    		} else if(preg_match("/College/", $meta_data)) {
				$college = substr($meta_data, 7, strlen($meta_data) - 7);
				print $college;
				$player->college = $college;
    		} else if(preg_match("/Experience/", $meta_data)) {
				$experience = substr($meta_data, 10, strlen($meta_data) - 10);
				print $experience;
				$player->experience = $experience;
    		}
    		print "<br>";

    	});
    	$player->save();
    }

    public function update_players_info() {
    	$players = Player::all();
    	foreach($players as $player) {
    		SportScraperController::scrapePlayerInfo($player->url);
    		sleep(0.5);
    	}
    }

    public function clean_up_player_info() {
    	$players = Player::all();
    	foreach($players as $player) {
    		$player->player_position = str_replace(' ', '', $player->player_position);
    		$player->save();
    	}
    }
}
