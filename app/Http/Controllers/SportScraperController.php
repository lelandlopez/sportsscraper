<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Goutte\Client;

use App\Http\Requests;

use App\Player;
use App\Player_Game_Log;
use App\NBA_Team;
use App\Game;

class SportScraperController extends Controller
{
	public function transfer_info() {
		$games = Game::all();
		foreach($games as $game) {
			$game_log = Player_Game_Log::where('game_id', '=', $game->id)->first();
			if($game_log->home) {
				$game->home_win = true;
				$game->points_home = $game_log->score_for;
				$game->points_away = $game_log->score_opposing;
				$game->save();
			} else {
				$game->home_win = false;
				$game->points_home = $game_log->score_opposing;
				$game->points_away = $game_log->score_for;
				$game->save();

			}
		}

	}

	public function delete_space_in_player_position() {
		$players = Player::all();
		foreach($players as $player) {
			$player->player_position = trim($player->player_position);
			$player->save();
		}

	}

	public function fill_in_player_url() {
		$players = Player::where('url', '=', '')->get();
		foreach($players as $player) {
			print $player->id;
			$player->url = "http://espn.go.com/nba/player/gamelog/_/id/" . $player->id . "/richard-jefferson";
			$player->save();
		}
		$players = Player::where('name', '=', '')->get();
		foreach($players as $player) {
			SportScraperController::scrapePlayerInfo($player->url);
		}
	}

	public function asdf() {
		ini_set('max_execution_time', 600);
		$games = Game::all();
		foreach($games as $game) {
			print $game->game_url;
			print "asdf";
			print "<br>";
			SportScraperController::scrape_nba_teams_from_game($game->game_url);

		}
	}

	public function scrape_nba_teams_from_game($url = "http://espn.go.com/nba/boxscore?id=400829115") {
		$idi = strpos($url, '=');
		$id = substr($url, $idi + 1, strlen($url) - $idi + 1);
    	$client = new Client();
    	$crawler = $client->request('GET', $url);	
		sleep(mt_rand(1,2));
		print $url;
		$game = Game::find($id);
    	$crawler->filter('div.away > div.content > div.team-container > div.team-info > a > span.abbrev')->each(function ($away_team) use ($game) {
    		$at = $away_team->text();
    		$nba_team = NBA_Team::where('nickname', '=', $at)->first();
    		print $nba_team->id;
    		$game->away_team_id = $nba_team->id;
    		print "<br>";
    	});
    	$crawler->filter('div.home > div.content > div.team-container > div.team-info > a > span.abbrev')->each(function ($home_team) use ($game) {
    		$at = $home_team->text();
    		$nba_team = NBA_Team::where('nickname', '=', $at)->first();
    		print $nba_team->id;
    		$game->home_team_id = $nba_team->id;
    		print "<br>";
    	});
    	$crawler->filter('tbody')->each(function ($tbody, $i) {
	    	$tbody->filter('tr:not([highlight]) > td.name > a')->each(function ($player) use ($i) {
	    		print $player->text() . $i;
	    		print "<br>";
	    		$player_url = $player->link()->getUri();
	    		print $player_url;
	    		print "<br>";
	    		$lplayer_urli = strrpos($player_url, '/');
	    		$player_id = substr($player_url, $lplayer_urli + 1, strlen($player_url) - $lplayer_urli + 1);
	    		print $player_id;
	    		print "<br>";
	    		if(!Player::find($player_id)) {
		    		$player = new Player();
		    		$player->id = $player_id;
		    		$player->save();
		    	}
	    	});
    	});
		$game->updated = true;
		$game->save();
	}


	public function refactor_date_for_player_game_logs() {
		$player_game_logs = Player_Game_Log::all();
		foreach($player_game_logs as $player_game_log) {
			$dayi = strpos($player_game_log->date, ' ');
			$date = substr($player_game_log->date, $dayi + 1, strlen($player_game_log->date) - $dayi + 1);
			$player_game_log->date = $date;
			$player_game_log->save();
		}

	}

	public function update_player_urls() {
		$players = Player::all();
		foreach($players as $player) {
			$player_url = $player->url;	
			$playeri = strpos($player_url, '_');
			$player_url = substr_replace($player_url, "gamelog/", $playeri, 0);
			print $player_url;
			print "<br>";
			$player->url = $player_url;
			$player->save();
		}

	}
	public function players_updated() {
		$players = Player::all();
		foreach($players as $player) {
			$player->updated = true;
			$player->save();
		}
	}

	public function test() {
		$games = Game::all();
		foreach($games as $game) {
			$game->year = 2015;
			$game->save();
		}
	}	

	public function init() {
		SportScraperController::scrapePlayerUrls();
		SportScraperController::scrape_nba_teams();
		SportScraperController::update_players_info();

	}

	public function scrape_all_players_game_info($start = 0) {
		ini_set('max_execution_time', 600);
		$players = Player::Where('id', '>', $start)->get();
    	foreach($players as $player) {
    		SportScraperController::scrape_player_game_info($player->url, $player->id);
    		print "asdf";
    	}
	}

	public function scrape_nba_teams() {
    	$client = new Client();
    	$crawler = $client->request('GET', 'http://espn.go.com/nba/format/player/design09/dropdown?teamId=undefined&posId=undefined&lang=en');	
    		sleep(mt_rand(1,2));
    	$crawler->filter('ul.main-items > li > a')->each(function ($teamrows) use ($crawler){
    		$team_name = $teamrows->text();
    		$nba_team = new NBA_Team();
    		$nba_team->name = $team_name;
    		$nba_team->nickname = strtoupper(substr($team_name, 0, 3));
    		if($team_name == "New York") {
    			$nba_team->nickname = "NY";
    		}
    		if($team_name == "Golden State") {
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

	//  scrape_game_info
    public function scrape_player_game_info ($url = "http://espn.go.com/nba/player/gamelog/_/id/3975/stephen-curry", $player_id = 3975, $debug = false) {
    	print $url;
    	print "<br>";
    	$client = new Client();
    	$crawler = $client->request('GET', $url);	
		sleep(mt_rand(1,2));
    	$crawler->filter('[class*="oddrow"][class*="team"], [class*="evenrow"][class*="team"]')->each(function ($gamerows) use ($player_id, $debug) {
	    	$game_log = new Player_Game_Log();
	    	$game_log->player_id = $player_id;
	    	$gamerows->filter('td')->each(function ($statcolumn, $i) use ($game_log, $debug) {
	    		if($i == 0) {
	    			$date = $statcolumn->text();
	    			$game_log->date = $date;
	    			if($debug) {
		    			print $date;
		    			print "<br>";
		    		}
	    		} else if($i == 1) {
	    			if($statcolumn->filter('li.game-location')->count() != 0) {
		    			if($debug) {
			    			print $statcolumn->filter('li.game-location')->first()->text();
			    			print "<br>";
			    		}
		    			$game_location = $statcolumn->filter('li.game-location')->first()->text();
		    			if($game_location == "vs") {
		    				$game_log->home = true;
		    			} else {
		    				$game_log->home = false;
		    			}
	    			}
	    			if($statcolumn->filter('li.team-name')->count() != 0 && $statcolumn->filter('li.team-name > a')->count() != 0) {

		    			if($debug) {
			    			print '<a href="'. $statcolumn->filter('li.team-name > a')->link()->getUri() . '">' . $statcolumn->filter('li.team-name')->first()->text() . '</a>';
			    		}
		    			$team_name = $statcolumn->filter('li.team-name')->first()->text();
		    			$team = NBA_Team::where("nickname", $team_name)->get()->first();
		    			$game_log->opposing_team_id = $team->id;
		    			if($debug) {
			    			print "<br>";
			    			print $statcolumn->filter('li.team-name > a')->link()->getUri();
			    			print "<br>";
			    		}
		    			//TODO add opposing team
		    		}
	    			print "<br>";
	    		} else if($i == 2) {
	    			$wini = strpos($statcolumn->text(), " ");
	    			$win = substr($statcolumn->text(), 0, $wini);
	    			$pointsfori = strpos($statcolumn->text(), "-");
	    			$pointsfor = substr($statcolumn->text(), $wini + 1, $pointsfori - $wini - 1);
	    			$pointsagainst = substr($statcolumn->text(), $pointsfori + 1, strlen($statcolumn->text()) - $pointsfori + 1);
	    			$game_url = $statcolumn->filter('a')->link()->getUri();
	    			$game_idi = strpos($game_url, '=');
	    			$game_id = substr($game_url, $game_idi + 1, strlen($game_url) - $game_idi);
	    			$game_log->game_id = $game_id;
	    			if($debug) {
		    			print $game_id;
		    			print "<br>";
		    		}
	    			if(!Game::find($game_id)) {
	    				$game = new Game();
	    				$game->id = $game_id;
	    				$game->game_type = 1;
	    				$game->game_url = $game_url;
	    				$game->save();
	    			}
	    			if($win == "W") {
	    				$game_log->win = true;
	    			} else if($win == "L") {
	    				$game_log->win = false;
	    			}
    				$game_log->score_for = $pointsfor;
    				$game_log->score_opposing = $pointsagainst;
	    			if($debug) {
		    			print $win;
		    			print "<br>";
		    			print $pointsfor;
		    			print "<br>";
		    			print $pointsagainst;
		    			print "<br>";
		    		}
	    		} else if($i == 3) {
	    			$min = $statcolumn->text();		
    				$game_log->min = $min;
	    			if($debug) {
		    			print $min;
		    			print "<br>";
		    		}
	    		} 
	    		else if($i == 4) {
	    			$fgmi = strpos($statcolumn->text(), "-");
	    			$fgm = substr($statcolumn->text(), 0, $fgmi);
	    			$fga = substr($statcolumn->text(), $fgmi + 1, strlen($statcolumn->text()) - $fgmi);
    				$game_log->fgm = $fgm;
    				$game_log->fga = $fga;
	    			if($debug) {
		    			print $fgm;
		    			print "<br>";
		    			print $fga;
		    			print "<br>";
		    		}

	    		} else if($i == 5) {
	    			$fgp = $statcolumn->text();		
    				$game_log->fgp = $fgp;
	    			if($debug) {
		    			print $fgp;
		    			print "<br>";
		    		}
	    		}
	    		else if($i == 6) {
	    			$tpmi = strpos($statcolumn->text(), "-");
	    			$tpm = substr($statcolumn->text(), 0, $tpmi);
	    			$tpa = substr($statcolumn->text(), $tpmi + 1, strlen($statcolumn->text()) - $tpmi);
    				$game_log->tpm = $tpm;
    				$game_log->tpa = $tpa;
	    			if($debug) {
		    			print $tpm;
		    			print "<br>";
		    			print $tpa;
		    			print "<br>";
		    		}
	    		}
	    		else if($i == 7) {
	    			$tpp = $statcolumn->text();		
    				$game_log->tpp = $tpp;
    				if($debug) {
		    			print $tpp;
		    			print "<br>";
		    		}
	    		}
	    		else if($i == 8) {
	    			$ftmi = strpos($statcolumn->text(), "-");
	    			$ftm = substr($statcolumn->text(), 0, $ftmi);
	    			$fta = substr($statcolumn->text(), $ftmi + 1, strlen($statcolumn->text()) - $ftmi);
    				$game_log->ftm = $ftm;
    				$game_log->fta = $fta;
    				if($debug) {
		    			print $ftm;
		    			print "<br>";
		    			print $fta;
		    			print "<br>";
		    		}
	    		}
	    		else if($i == 9) {
	    			$ftp = $statcolumn->text();		
    				$game_log->ftp = $ftp;
    				if($debug) {
		    			print $ftp;
		    			print "<br>";
		    		}
	    		}
	    		else if($i == 10) {
	    			$reb = $statcolumn->text();		
    				$game_log->reb = $reb;
    				if($debug) {
		    			print $reb;
		    			print "<br>";
		    		}
	    		}
	    		else if($i == 11) {
	    			$ast = $statcolumn->text();		
    				$game_log->ast = $ast;
    				if($debug) {
		    			print $ast;
		    			print "<br>";
		    		}
	    		}
	    		else if($i == 12) {
	    			$blk = $statcolumn->text();		
    				$game_log->blk = $blk;
    				if($debug) {
		    			print $blk;
		    			print "<br>";
		    		}
	    		}
	    		else if($i == 13) {
	    			$stl = $statcolumn->text();		
    				$game_log->stl = $stl;
    				if($debug) {
		    			print $stl;
		    			print "<br>";
		    		}
	    		}
	    		else if($i == 14) {
	    			$pf = $statcolumn->text();		
    				$game_log->pf = $pf;
    				if($debug) {
		    			print $pf;
		    			print "<br>";
		    		}
	    		}
	    		else if($i == 15) {
	    			$to = $statcolumn->text();		
    				$game_log->to = $to;
    				if($debug) {
		    			print $to;
		    			print "<br>";
		    		}
	    		}
	    		else if($i == 16) {
	    			$pts = $statcolumn->text();		
    				$game_log->pts = $pts;
    				if($debug) {
		    			print $pts;
		    			print "<br>";
		    		}
	    		}
	    		else {
    				if($debug) {
			    		print $i . " : " . $statcolumn->text();
			    		print "<br>";
			    	}
	    		}
			});
			if($game_log->opposing_team_id == "") {
				$game_log->opposing_team_id = 31;
			}
			if(Player_Game_Log::where('player_id', $game_log->player_id)->where('game_id', $game_log->game_id)->count() == 0) {
				$game_log->save();
			}

			if($debug) {
				print "<br>";
			}
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
    	$crawler->filter('div.mod-content > h1')->each(function ($name_string) use ($player) { 
    		$player->name = $name_string->text();
    		print $player->name;
    		print "<br>";
    	});
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
