<?php

function specialtysystem_wind_getmoduleinfo(){
	$info = array(
		"name" => "Specialty System - Wind",
		"author" => "`2Oliver`0",
		"version" => "1.0",
		"download" => "",
		"category" => "Specialty System",
		"requires"=> array(
			"specialtysystem"=>"1.0|Specialty System by `2Oliver Brendel",
			),
	);
	return $info;
}

function specialtysystem_wind_install(){
	module_addhook("specialtysystem-register");
	return true;
}

function specialtysystem_wind_uninstall(){
	require_once("modules/specialtysystem/uninstall.php");
	specialtysystem_uninstall("specialtysystem_wind");
	return true;
}

function specialtysystem_wind_fightnav(){
	global $session;
	require_once("modules/specialtysystem/functions.php");
	$uses=specialtysystem_availableuses("specialtysystem_wind");
	$name=translate_inline('Fuuton Ninjutsu (Wind)');
	tlschema('module-specialtysystem_wind');
	if ($uses > 0) {
		specialtysystem_addfightheadline($name, 
$uses,specialtysystem_getskillpoints("specialtysystem_wind"));
		specialtysystem_addfightnav("Kaze no Yoroi","wind1&cost=1",1);
	}
	if ($uses > 2) {
		specialtysystem_addfightnav("Kamaitachi no Jutsu","wind2&cost=3",3);
	}
	if ($uses > 5) {
		specialtysystem_addfightnav("Kaze Kiri","wind3&cost=6",6);
	}
	if ($uses > 9) {
		specialtysystem_addfightnav("Daikamaitachi no Jutsu","wind4&cost=10",10);
	}
	if ($uses > 14) {
		specialtysystem_addfightnav("Kaze no Yaiba","wind5&cost=15",15);
	}
	if ($uses > 17) {
		specialtysystem_addfightnav("Fuuton - Tatsu no Oshigoto","wind6&cost=18",18);
	}
	if ($uses > 19 && $session['user']['hashorse']==12) { //shukaku
		specialtysystem_addfightnav("Renkuudan","renkuudan&cost=20",20);
	}
	tlschema();
	return specialtysystem_getfightnav();
}

function specialtysystem_wind_apply($skillname){
	global $session;
	require_once("modules/specialtysystem/functions.php");
	switch($skillname){
		case "wind1":
			apply_buff('wind1',array(
				"startmsg"=>"`i`2Kaze no Yoroi!`i`n`qYou `gcreate a barrier of wind 
around yourself.`b",
				"name"=>"`2Kaze no Yoroi",
				"rounds"=>5,
				"wearoff"=>"The wind that surrounds you settles.",
				"defmod"=>1.05,
				"roundmsg"=>"You are protected by the wind!",
				"schema"=>"module-specialtysystem_wind"
			));
			break;
		case "wind2":
			apply_buff('wind2',array(
				"startmsg"=>"`i`2Kamaitachi no Jutsu!`i`n`qYou `gswing your weapon and 
create a windstorm that slices through everything in the area.",
				"name"=>"`2Kamaitachi `gno `2Jutsu",
				"rounds"=>3,
				"wearoff"=>"The windstorm settles.",
				"areadamage"=>true,
				"minbadguydamage"=>5+$session['user']['dragonkills'],
				"maxbadguydamage"=>15+$session['user']['dragonkills'],
				"minioncount"=>3,
				"effectmsg"=>"{badguy} suffers {damage} damage from cuts!",
				"schema"=>"module-specialtysystem_wind"
			));
			break;
		case "wind3":
			apply_buff('wind3',array(
				"startmsg"=>"`i`2K`ga`2z`ge `2K`gi`2r`gi!`i`n`qYou `gswing your weapon 
and send a blade of wind at {badguy}.",
				"name"=>"`2K`ga`2z`ge `2K`gi`2r`gi",
				"rounds"=>1,
				"minbadguydamage"=>15+$session['user']['dragonkills'],
				"maxbadguydamage"=>45+$session['user']['dragonkills'],
				"minioncount"=>1,
				"effectmsg"=>"{badguy} suffers {damage} damage from the cutting wind!",
				"schema"=>"module-specialtysystem_wind"
			));
			break;
		case "wind4":
			apply_buff('wind4',array(
				"startmsg"=>"`i`2Daikamaitachi no Jutsu!`i`n`qYou `gswing your weapon 
and create a huge windstorm that slices through everything in the area.",
				"name"=>"`2Daikamaitachi `gno `2Jutsu",
				"rounds"=>5,
				"wearoff"=>"The windstorm settles.",
				"areadamage"=>true,
				"minbadguydamage"=>15+$session['user']['dragonkills'],
				"maxbadguydamage"=>30+$session['user']['dragonkills'],
				"minioncount"=>3,
				"effectmsg"=>"{badguy} suffers {damage} damage from cuts!",
				"schema"=>"module-specialtysystem_wind"
			));
			break;
		case "wind5":
			apply_buff('wind5',array(
				"startmsg"=>"`i`2Kaze `gno `2Yaiba!`i`n`qYou `gsend an unstoppable blade 
of wind at {badguy}.",
				"name"=>"`2Kaze `gno `2Yaiba",
				"rounds"=>1,
				"areadamage"=>true,
				"minbadguydamage"=>80+$session['user']['dragonkills'],
				"maxbadguydamage"=>120+$session['user']['dragonkills'],
				"minioncount"=>1,
				"effectmsg"=>"{badguy} suffers {damage} damage from the cutting wind!",
				"schema"=>"module-specialtysystem_wind"
			));
			break;
		case "wind6":
			apply_buff('wind6',array(
				"startmsg"=>"`i`gFuuton - `2Tatsu `gno `2Oshigoto!`i`n`qYou `gsummon a 
powerful tornado from the sky.",
				"name"=>"`gFuuton - `2Tatsu `gno `2Oshigoto",
				"rounds"=>5,
				"wearoff"=>"The tornado settles.",
				"areadamage"=>true,
				"minbadguydamage"=>50+$session['user']['dragonkills'],
				"maxbadguydamage"=>90+$session['user']['dragonkills'],
				"minioncount"=>5,
				"effectmsg"=>"{badguy} suffers {damage} damage from being torn apart by 
the violent wind!",
				"schema"=>"module-specialtysystem_wind"
			));
			break;
		case "renkuudan":
			apply_buff('renkuudan',array(
				"startmsg"=>"`i`lRen`gku`ldan!`i`n`q`6Shukaku `ltakes a deep breath 
and shoots a large ball of compressed air and chakra at {badguy}!",
				"name"=>"`lRen`gku`ldan",
				"rounds"=>1,
				"areadamage"=>true,
				"minbadguydamage"=>120+$session['user']['dragonkills']*5,
				"maxbadguydamage"=>160+$session['user']['dragonkills']*5,
				"minioncount"=>1,
				"effectmsg"=>"`q{badguy}`q suffers {damage} damage!",
				"schema"=>"module-specialtysystem_wind"
			));

	}
	specialtysystem_incrementuses("specialtysystem_wind",httpget('cost'));
	return;
}

function specialtysystem_wind_dohook($hookname,$args){
	switch ($hookname) {
	case "specialtysystem-register":
		$args[]=array(
			"spec_name"=>'Fuuton Ninjutsu',
			"spec_colour"=>'`2',
			"spec_shortdescription"=>'`2The cutting wind!',
			"spec_longdescription"=>'`5Growing up, you always loved to feel the wind 
blowing in your face ... so you studied fuuton ninjutsu, sweeping everything 
in your path away.',
			"modulename"=>'specialtysystem_wind',
			"fightnav_active"=>'1',
			"newday_active"=>'0',
			"dragonkill-active"=>'0',
			"dragonkill_minimum_requirement"=>'12',
			"stat_requirements"=>array(
				"intelligence"=>14,
				"wisdom"=>12,
				"dexterity"=>15,
				),
			);
		break;
	}
	return $args;
}

function specialtysystem_wind_run(){
}
?>

