<?php

function assassins_getmoduleinfo(){
	$info = array(
		"name"=>"Assassins",
		"author"=>"Chris Vorndran",
		"version"=>"1.0",
		"category"=>"Forest Specials",
		"settings"=>array(
			"Ninja Encounter - Settings,title",
				"gold_var"=>"Multiply level by this var to get Gold cost,int|500",
				"gem_var"=>"Multiply dragonkills by this to get Gem cost,int|2",
				"chance_fail"=>"Chance of failure to get deal due to being good,range,0,100,5|90",
			"Badguy Settings,title",
				"badguy-name"=>"Name of the badguy,text|`~Rogue `)Shinobi",
				"badguy-weapon"=>"Weapon of the badguy,text|`)Shurikens`0",
		),
		"prefs"=>array(
			"Ninja Encounter - Prefs,title",
				"marked"=>"Has this player been marked to be killed?,bool|0",
				"note"=>"Signed note the victim gets,text",
		),
	);
	return $info;
}
function assassins_install(){
	module_addeventhook("forest","return 100;");
	return true;
}
function assassins_uninstall(){
	return true;
}
function assassins_runevent($type){
	global $session;
	$op = httpget('op');
	$revenge = httpget('revenge');
	$id = rawurldecode(httpget('id'));
	$from = "forest.php?";
	$gold_var = get_module_setting("gold_var");
	$gem_var = get_module_setting("gem_var");
	require("modules/assassins/runevent.php");
}
?>