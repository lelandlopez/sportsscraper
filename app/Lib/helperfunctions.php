<?php

//get id from player_url
function getIdFromUrl($player_url) {
	$player_id_last_slash = strrpos($player_url, '/');
	$player_id_second_last_slash_string = substr($player_url, 0, $player_id_last_slash);
	$player_id_second_last_slash = strrpos($player_id_second_last_slash_string, '/');
	$player_id = substr($player_url, $player_id_second_last_slash + 1, $player_id_last_slash - $player_id_second_last_slash - 1);
	return $player_id;
}


