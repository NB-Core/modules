<?php

function specialtysystem_sand_getmoduleinfo(){
	$info = array(
		"name" => "Specialty System - Sand",
		"author" => "`2Oliver Brendel`0",
		"version" => "1.0",
		"download" => "",
		"category" => "Specialty System",
		"requires"=> array(
			"specialtysystem"=>"1.0|Specialty System by `2Oliver Brendel",
			),
	);
	return $info;
}

function specialtysystem_sand_install(){
	module_addhook("specialtysystem-register");
	return true;
}

function specialtysystem_sand_uninstall(){
	require_once("modules/specialtysystem/uninstall.php");
	specialtysystem_uninstall("specialtysystem_sand");
	return true;
}

function specialtysystem_sand_fightnav(){
	global $session;
	require_once("modules/specialtysystem/functions.php");
	$uses=specialtysystem_availableuses("specialtysystem_sand");
	$name=translate_inline('Suna Ninjutsu (Sand)');
	tlschema('module-specialtysystem_sand');
	if ($uses > 0) {
		specialtysystem_addfightheadline($name, $uses,specialtysystem_getskillpoints("specialtysystem_sand"));
		specialtysystem_addfightnav("Suna Shuriken","sand1&cost=1",1);
	}
	if ($uses > 2) {
		specialtysystem_addfightnav("Suna no Yoroi","sand2&cost=3",3);
	}
	if ($uses > 4) {
		specialtysystem_addfightnav("Suna Bunshin","sand3&cost=5",5);
	}
	if ($uses > 9) {
		specialtysystem_addfightnav("Suna no Tate","sand4&cost=10",10);
	}
	if ($uses > 14) {
		specialtysystem_addfightnav("Ryuusa Bakuryuu","sand5&cost=15",15);
	}
	if ($uses > 17) {
		specialtysystem_addfightnav("Sabaku Kyuu","sand6&cost=18",18);
	}
	if ($uses > 19) {
		specialtysystem_addfightnav("Sabaku Sousou","sand7&cost=20",20);
	}
	tlschema();
	return specialtysystem_getfightnav();
}

function specialtysystem_sand_apply($skillname){
	global $session;
	require_once("modules/specialtysystem/functions.php");
	switch($skillname){
		case "sand1":
			apply_buff('sand1',array(
				"startmsg"=>"`6`iSuna Shuriken`i!`n`qYou `Qthrow shuriken made from sand.",
				"name"=>"`6Suna Shuriken",
				"rounds"=>1,
				"areadamage"=>true,
				"minbadguydamage"=>5+min($session['user']['dragonkills'],25),
				"maxbadguydamage"=>10+min($session['user']['dragonkills'],25),
				"minioncount"=>3,
				"effectmsg"=>"{badguy} suffers {damage} damage from the shuriken!",
				"effectnodmgmsg"=>"The shuriken misses!",
				"schema"=>"module-specialtysystem_sand"
			));
			break;
		case "sand2":
			apply_buff('sand2',array(
				"startmsg"=>"`6`iSuna no Yoroi`i!`n`qYou `Qcover yourself in a compacted layer of sand, providing you with additional defense.",
				"name"=>"`6Suna no Yoroi",
				"rounds"=>5,
				"wearoff"=>"The compacted layer of sand falls apart.",
				"defmod"=>2,
				"roundmsg"=>"Your sand armor covers you!",
				"schema"=>"module-specialtysystem_sand"
			));
			break;
		case "sand3": //to be revised
			apply_buff('sand3',array(
				"startmsg"=>"`6`iSuna Bunshin`i!`n`qYou `Qcreate a clone out of sand.",
				"name"=>"`6Suna Bunshin",
				"rounds"=>10,
				"wearoff"=>"The clone falls apart.",
				"minbadguydamage"=>5+$session['user']['dragonkills'],
				"maxbadguydamage"=>15+$session['user']['dragonkills'],
				"minioncount"=>1,
				"effectmsg"=>"Your clone hit {badguy} for {damage} damage!",
				"effectnodmgmsg"=>"Your clone misses!",
				"schema"=>"module-specialtysystem_sand"
			));
			break;
		case "sand4":
			apply_buff('sand4',array(
				"startmsg"=>"`6`iSuna no Tate`i!`n`qYou `Qsurround and protect yourself with sand.",
				"name"=>"`6Suna no Tate",
				"rounds"=>10,
				"wearoff"=>"You lost the protection of the sand.",
				"atkmod"=>0.5,
				"defmod"=>3,
				"roundmsg"=>"Your shield gives you ample protection",
				"schema"=>"module-specialtysystem_sand"
			));
			break;
		case "sand5":
			apply_buff('sand5',array(
				"startmsg"=>"`6`iRyuusa Bakuryuu`i!`n`qYou `Qcreate a massive amount of sand and send it towards {badguy} in the form of a wave.",
				"name"=>"`6Ryuusa Bakuryuu",
				"rounds"=>5,
				"areadamage"=>true,
				"wearoff"=>"The wave left the entire forest covered in sand.",
				"minbadguydamage"=>30+$session['user']['dragonkills'],
				"maxbadguydamage"=>60+$session['user']['dragonkills'],
				"minioncount"=>6,
				"effectmsg"=>"{badguy} was swallowed by the sand and suffers {damage} damage!",
				"effectnodmgmsg"=>"The sand only rushes by {badguy}!",
				"schema"=>"module-specialtysystem_sand"
			));
			break;
		case "sand6":
			apply_buff('sand6',array(
				"startmsg"=>"`6`iSabaku Kyuu`i!`n`qYou `Qcover {badguy}'s entire body in sand.",
				"name"=>"`6Sabaku Kyuu",
				"rounds"=>5,
				"wearoff"=>"The sand falls apart.",
				"badguyatkmod"=>0,
				"badguydefmod"=>0,
				"roundmsg"=>"The sand renders {badguy} immobile!",
				"schema"=>"module-specialtysystem_sand"
			));
			break;
		case "sand7":
			apply_buff('sand7',array(
				"startmsg"=>"`6`iSabaku Sousou`i!`n`qYou `Qcover {badguy}'s entire body in sand and implode, crushing whatever is within",
				"name"=>"`6Sabaku Sousou",
				"rounds"=>1,
				"minbadguydamage"=>150+$session['user']['dragonkills']*4,
				"maxbadguydamage"=>180+$session['user']['dragonkills']*4,
				"minioncount"=>1,
				"effectmsg"=>"{badguy} was crushed and suffers {damage} damage!",
				"schema"=>"module-specialtysystem_sand"
			));
			break;

	}
	specialtysystem_incrementuses("specialtysystem_sand",httpget('cost'));
	return;
}

function specialtysystem_sand_dohook($hookname,$args){
	switch ($hookname) {
	case "specialtysystem-register":
		$args[]=array(
			"spec_name"=>'Suna Ninjutsu',
			"spec_colour"=>'`6',
			"spec_shortdescription"=>'`6The sand that smells of blood!',
			"spec_longdescription"=>'`5Growing up, you always loved playing with sand ... so you studied sand ninjutsu, giving you the power to protect yourself and destroy others with sand.',
			"modulename"=>'specialtysystem_sand',
			"fightnav_active"=>'1',
			"newday_active"=>'0',
			"dragonkill-active"=>'0',
			"dragonkill_minimum_requirement"=>6,
			"stat_requirements"=>array(
				"constitution"=>16,
				),
			);
		break;
	}
	return $args;
}

function specialtysystem_sand_run(){
}
?>
