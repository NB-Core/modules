<?php

function merry_xmas_getmoduleinfo(){
	$info = array(
		"name"=>"Merry Christmas on HP (timelocked)",
		"version"=>"1.1",
		"author"=>"`2Oliver Brendel",
		"category"=>"Holidays|Christmas",
		"download"=>"",
		"settings"=>array(
			"X-Mas Settings,title",
			"This module will just display the javascript banner on the page, but it's a nice effect,note",
			"start"=>"Activation start date (mm-dd)|12-1",
			"end"=>"Activation end date (mm-dd)|12-31",
		),
		"requires"=>array(
			"datemanager"=>"1.0|By Oliver Brendel",
		),
	);
	return $info;
}

function merry_xmas_install(){
	module_addhook("index");
	return true;
}

function merry_xmas_uninstall(){
	return true;
}

function merry_xmas_dohook($hookname,$args){
	global $session;

	$start = get_module_setting("start");
	$end = get_module_setting("end");
	require_once("modules/datemanager.php");
	$check=datemanager_datecheck($start,$end,0);

	//check for activation date
	if ($check!=1) return $args;

	$pos=strtok($_SERVER['REQUEST_URI'],"?");
	$pos=substr($pos, 1);
	if ($pos=='forest.php') return $args;
	$name='';
	if (isset($session['user']['name']) && $session['user']['name']!='') $name=", ".sanitize($session['user']['name']);
	$text=appoencode("Merry Christmas$name!");
        rawoutput(
                "<div><center><h1><p id='merry-xmas-banner'>$text</p></h1></center></div>"
        );
        output_notl("<script src=\"modules/merry_xmas/assets/rainbow-banner.js\" defer></script>", true);
        return $args;
}

function merry_xmas_run(){

}
?>
