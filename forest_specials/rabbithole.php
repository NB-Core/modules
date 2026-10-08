<?php
/* Rabbit Hole - 17April2005
   Author: Robert of Maddrio dot com
   converted from an 097 forest event
*/

function rabbithole_getmoduleinfo(){
	$info = array(
	"name"=>"Rabbit Hole",
	"version"=>"1.0",
	"author"=>"`2Robert",
	"category"=>"Forest Specials",
	"download"=>"http://dragonprime.net/users/robert/rabbithole098.zip",
	"settings"=>array(
	"Rabbit Hole Settings,title",
	"percentage"=>"Chance player gains or loses 1 turn,range,10,100,2|50"
	),
	);
	return $info;
}

function rabbithole_install(){
	module_addeventhook("forest","return 100;");
	return true;
}

function rabbithole_uninstall(){
	return true;
}

function rabbithole_dohook($hookname,$args){
	return $args;
}

function rabbithole_runevent($type){
	global $session;
	$chance = get_module_setting("percentage");
	$rand = e_rand(1,100);
	if ($rand <= $chance) {
	output("`n`n`2 You stumble into a rabbit hole, your foot is stuck! `n`n");
	output(" Twisting and turning to free yourself from this rodents domain, you try your best. `n`n");
	output(" Finally after wasting precious time, you free yourself! `n`n");
	if ($session['user']['turns'] >=1 ) {
		output("`2 You lost time for `^1 `2 forest fight! ");
		$session['user']['turns']--;
		}
	}else{
	output("`n`n`2 You spy a rabbits hole in the ground and avoid stepping into it. `n`n");
	output(" Noticing a shade tree nearby, you decide to sit and rest for awhile. `n`n");
	output(" You watch as birds fly, the insects crawl on the ground and a butterfly flutter around. `n`n");
	output(" Your little break is very refreshing! `n`n You gain `^1 `2 forest fight! ");
	$session['user']['turns']++;
	}
}

function rabbithole_run(){
}
?>