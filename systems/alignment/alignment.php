<?php

function alignment_getmoduleinfo(){
	$info = array(
		"name"=>"Alignment Core",
		"author"=>"Chris Vorndran<br/>`6Original Script by: `QWebPixie",
		"version"=>"1.9",
		"category"=>"Alignment",
		"download"=>"http://dragonprime.net/index.php?module=Downloads;sa=dlview;id=64",
		"vertxtloc">"http://dragonprime.net/users/Sichae/",
		"description"=>"This module will display the alignment of a character (Evil, Neutral, Good). Certain events in the LotGD universe will affect this alignment.",
		"settings"=>array(
			"Alignment Settings,title",
				"evilalign"=>"What number is evil alignment,int|33",
				"Any number under the evil number will make the user show up evil. You can use negative numbers.,note",
				"goodalign"=>"What number is good alignment,int|66",
				"Any number above the good number will make the user show up good,note",
				"Any number between evil and good number the user shows up neutral,note",
				"display-num"=>"Display a number alongside of the Alignment statement?,bool|0",
			"Demeanor Settings,title",
				"chaosalign"=>"What number is chaotic demeanor,int|33",
				"Any number under the chaotic number will make the user show up chaotic. You can use negative numbers.,note",
				"lawfulalign"=>"What number is lawful demeanor,int|66",
				"Any number above the lawful number will make the user show up lawful,note",
				"Any number between chaotic and lawful number the user shows up neutral,note",
			"Maximum/Minimum Settings,title",
				"reset"=>"Reset user's alignment/demeanor if it goes over/under the maximum/minimum (see below),bool|0",
				"max-num"=>"What is the maximum alignment/demeanor?,int|100",
				"min-num"=>"What is the minimum alignment/demeanor?,int|-100",
			"Other Settings,title",
				"shead"=>"What Stat heading does this go under,text|Vital Info",
				"pvp"=>"Does PVP affect Alignment,bool|1",
				"de-pvp"=>"Does PVP affect Demeanor,bool|1",
				"Whether to remove or add is based on a comparison of the warrior's alignment.,note",
				"How much to remove or add is based from the character's level divided by two.,note",
				"In the neutral case it is a 50/50 chance either way. Level is divided by 3 for amount to change.,note",
		),
		"prefs-mounts"=>array(
			"Mount Alignment Settings,title",
			"Please note that this change happens at newday.,note",
			"al"=>"How much does having this mount affect a person's alignment?,int|0",
			"de"=>"How much does having this mount affect a person's demeanor?,int|0",
			"0 This value to disable. You may also set negative numbers.,note",
		),
		"prefs-creatures"=>array(
			"Creature Alignment Settings,title",
			"al"=>"How much does slaying this creature affect a person's alignment?,int|0",
			"de"=>"How much does slaying this creature affect a person's demeanor?,int|0",
			"0 This value to disable. You may also set negative numbers.,note",
		),
		"prefs"=>array(
		    "Alignment user preferences,title",
			"alignment"=>"Current alignment number,text|50",
			"demeanor"=>"Current demeanor number,text|50",
		),
	);
	return $info;
}

function alignment_install(){
	module_addhook("biostat");
	module_addhook("charstats");
	module_addhook("newday");
	module_addhook("pvpwin");
    return true;
}

function alignment_uninstall(){
    return true;
}

function alignment_dohook($hookname,$args){
	global $session,$badguy;
    require("modules/alignment/dohook/$hookname.php");
	return $args;
}
?>