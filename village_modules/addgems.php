<?php
// add gems one off
/* ver 1.0 by Shannon Brown => SaucyWench -at- gmail -dot- com */
/* 29 Nov 2004 */

require_once("lib/http.php");
require_once("lib/villagenav.php");

function addgems_getmoduleinfo(){
	$info = array(
		"name"=>"Add Gems module",
		"version"=>"1.0",
		"author"=>"Shannon Brown",
		"category"=>"Village",
		"download"=>"core_module",
		"prefs"=>array(
			"Add Gems User Preferences,title",
			"gotgems"=>"Has the player received their gems yet?,bool|0",
		)
	);
	return $info;
}

function addgems_install(){
	module_addhook("newday");
	return true;
}

function addgems_uninstall(){
	return true;
}

function addgems_dohook($hookname,$args){
	global $session;
	switch($hookname){
	case "newday":
		$gotgems=get_module_pref("gotgems");
		if ($gotgems==0) {
			global $session;
			$plus=round($session['user']['dragonkills']/2);
			if ($plus>20) {
				$plus=20;
			}
			$plus=1;
			$session['user']['gems']+=$plus;
			$session['user']['gold']+=2000;
			output("`^`n`n`n`c`v* * * * * * * * * * * * * * * * * * * * * * * * *`n");
			output("* * * * * * * * * * * * * * * * * * * * * * * * *`n");
			output("`n`%Sorry for all the last outtage which brought the database server down. Though no data lost. As compensation (referring to your Oro Kills), you get`q %s gem`% and `g2000 gold`%!`n`n`^Thanks for your support!`n`n`v",$plus);
			output("* * * * * * * * * * * * * * * * * * * * * * * * *`n");
			output("* * * * * * * * * * * * * * * * * * * * * * * * *`c`n`n`n");
			set_module_pref("gotgems",1);
		}
		break;
	}
	return $args;
}

?>
