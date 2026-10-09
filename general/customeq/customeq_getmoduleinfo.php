<?php
	$info = array(
		"name"=>"Custom Equipment",
		"author"=>"Booger",
		"version"=>"1.0",
		"category"=>"Lodge",
		"download"=>"core_module",
		"settings"=>array(
			"Custom Equipment Module Settings,title",
			"keep"=>"When will the player lose the equipment?,enum,0,Never,1,After killing the dragon,2,After upgrading",
			"showlvl"=>"Does the equipment level appear in Equipment Info?,bool|1",
			"weaponcost"=>"How many points will the first custom weapon cost?,int|100",
			"armorcost"=>"How many points will the first custom armor cost?,int|100",
			"The costs for subsequent buys will only be used if the equipment carries over upgrades and DKs!,note",
			"extraweapon"=>"How many points will subsequent weapon changes cost?,int|0",
			"extraarmor"=>"How many points will subsequent armor changes cost?,int|0",
			// add a new setting to use color codes in the equipment info
			"color"=>"Use color codes in equipment info?,bool|1",
		),
		"prefs"=>array(
			"Custom Equipment Preferences,title",
			"weaponname"=>"Players custom weapon,|",
			"armorname"=>"Players custom armor,|",
			"keepeq"=>"Player can keep equipment over upgrades and DKs regardless of game settings,bool|0",
		),
	);
?>
