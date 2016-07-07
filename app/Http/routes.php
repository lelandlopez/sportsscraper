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


Route::get('/scrape_game_info', 'SportScraperController@scrape_player_game_info');
Route::get('/scrape_nba_teams', 'SportScraperController@scrape_nba_teams');
Route::get('/playerUrls', 'SportScraperController@scrapePlayerUrls');
Route::get('/player_info', 'SportScraperController@scrapePlayerInfo');
Route::get('/update_players_info', 'SportScraperController@update_players_info');
Route::get('/clean_players_info', 'SportScraperController@clean_up_player_info');
Route::get('/init', 'SportScraperController@init');