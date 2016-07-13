<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});



Route::get('/transfer_info', 'SportScraperController@transfer_info');
Route::get('/remove_spaces_from_player_position', 'SportScraperController@delete_space_in_player_position');
Route::get('/asdf', 'SportScraperController@asdf');
Route::get('/players_updated', 'SportScraperController@players_updated');
Route::get('/fill_in_player_url', 'SportScraperController@fill_in_player_url');
Route::get('/scrape_nba_teams_from_game', 'SportScraperController@scrape_nba_teams_from_game');
Route::get('/refactor_date_for_player_game_logs', 'SportScraperController@refactor_date_for_player_game_logs');
Route::get('/update_players_url', 'SportScraperController@update_player_urls');
Route::get('/test', 'SportScraperController@test');
Route::get('/scrape_all_players_game_info/{start}', 'SportScraperController@scrape_all_players_game_info');
Route::get('/scrape_player_game_info', 'SportScraperController@scrape_player_game_info');
Route::get('/scrape_nba_teams', 'SportScraperController@scrape_nba_teams');
Route::get('/playerUrls', 'SportScraperController@scrapePlayerUrls');
Route::get('/player_info', 'SportScraperController@scrapePlayerInfo');
Route::get('/update_players_info', 'SportScraperController@update_players_info');
Route::get('/clean_players_info', 'SportScraperController@clean_up_player_info');
Route::get('/init', 'SportScraperController@init');