<?php

function specialtysystem_genjutsu_getmoduleinfo(){
	$info = array(
		"name" => "Specialty System - Genjutsu",
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

function specialtysystem_genjutsu_install(){
	module_addhook("specialtysystem-register");
	return true;
}

function specialtysystem_genjutsu_uninstall(){
	require_once("modules/specialtysystem/uninstall.php");
	specialtysystem_uninstall("specialtysystem_genjutsu");
	return true;
}

function specialtysystem_genjutsu_fightnav(){
	global $session;
	require_once("modules/specialtysystem/functions.php");
	$uses=specialtysystem_availableuses("specialtysystem_genjutsu");
	$name=translate_inline('Genjutsu');
	tlschema('module-specialtysystem_genjutsu');
	if ($uses > 0) {
		specialtysystem_addfightheadline($name, $uses,specialtysystem_getskillpoints("specialtysystem_genjutsu"));
		specialtysystem_addfightnav("Narakumi no Jutsu","genjutsu1&cost=1",1);
	}
	if ($uses > 2) {
		specialtysystem_addfightnav("Kokoni Arazu no jutsu","genjutsu2&cost=3",3);
	}
	if ($uses > 5) {
		specialtysystem_addfightnav("Nijuu Kokoni Arazu no Jutsu","genjutsu3&cost=6",6);
	}
	if ($uses > 9) {
		specialtysystem_addfightnav("Jubaku Satsu","genjutsu4&cost=10",10);
	}
	if ($uses > 14) {
		specialtysystem_addfightnav("Suzu - Kiri","genjutsu5&cost=15",15);
	}
	if ($uses > 17) {
		specialtysystem_addfightnav("Jigoku Kouka no Jutsu","genjutsu6&cost=18",18);
	}
	if ($uses > 19) {
		specialtysystem_addfightnav("Kokuangyou no Jutsu","genjutsu7&cost=20",20);
	}
	tlschema();
	return specialtysystem_getfightnav();
}

function specialtysystem_genjutsu_apply($skillname){
	global $session;
	require_once("modules/specialtysystem/functions.php");
	switch($skillname){
		case "genjutsu1":
			apply_buff('genjutsu1',array(
				"startmsg"=>"`%`iNarakumi no Jutsu`i`n`vAn imaginary circle of leaves spin around and envelop the enemy, falling away shortly after.",
				"name"=>"`%Narakumi no Jutsu",
				"rounds"=>5,
				"wearoff"=>"{badguy} snaps out of the illusion.",
				"badguyatkmod"=>0.75,
				"badguydefmod"=>0.75,
				"roundmsg"=>"{badguy} sees a horrible vision and can not move well!",
				"schema"=>"module-specialtysystem_genjutsu"
			));
			break;
		case "genjutsu2":
			apply_buff('genjutsu1',array(
				"startmsg"=>"`%`iKokoni Arazu no Jutsu`i`n`vYou disappear into the surroundings by changing the appearance of the area.",
				"name"=>"`%Kokoni Arazu no Jutsu",
				"rounds"=>5,
				"wearoff"=>"The illusion disappears.",
				"badguydefmod"=>0.60,
				"roundmsg"=>"You attacked while {badguy} was off-guard!",
				"schema"=>"module-specialtysystem_genjutsu"
			));
			break;
		case "genjutsu3":
			apply_buff('genjutsu1',array(
				"startmsg"=>"`%`iNi`5juu `%Ko`5ko`%ni `5A`%ra`5zu `%no Jutsu`i`n`vYou disappear into the surroundings and create an illusion around the area.",
				"name"=>"`%Ni`5juu `%Ko`5ko`%ni `5A`%ra`5zu `%no Jutsu",
				"rounds"=>5,
				"wearoff"=>"The illusion disappears.",
				"badguyatkmod"=>0.50,
				"badguydefmod"=>0.50,
				"roundmsg"=>"Your illusion conceals you from {badguy}!",
				"schema"=>"module-specialtysystem_genjutsu"
			));
			break;
		case "genjutsu4":
			apply_buff('genjutsu1',array(
				"startmsg"=>"`%`iJu`5ba`%ku `5Sa`%tsu`i`n`vA tree sprouts from underneath {badguy}.",
				"name"=>"`%Ju`5ba`%ku `5Sa`%tsu",
				"rounds"=>3,
				"wearoff"=>"{badguy} snaps out of the illusion.",
				"invulnerable"=>true,
				"roundmsg"=>"{badguy} is bound by the tree and is unable to harm you!",
				"schema"=>"module-specialtysystem_genjutsu"
			));
			break;
		case "genjutsu5":
			apply_buff('genjutsu1',array(
				"startmsg"=>"`%`iSuzu `5- `%Kiri`i`n`vYou disappear into a cloud of `R`brose petals`b.",
				"name"=>"`%Suzu `5- `%Kiri",
				"rounds"=>10,
				"wearoff"=>"The illusion disappears.",
				"badguyatkmod"=>0.25,
				"badguydefmod"=>0.25,
				"roundmsg"=>"Your illusion conceals you from {badguy}!",
				"schema"=>"module-specialtysystem_genjutsu"
			));
			break;
		case "genjutsu6":
			apply_buff('genjutsu1',array(
				"startmsg"=>"`%`i`%Jigoku `5Kouka `%no `5Jutsu`i`n`v`\$A large fire ball descend from the heavens and turns the area into a `4fiery hell.",
				"name"=>"`%Jigoku `5Kouka `%no `5Jutsu",
				"rounds"=>3,
				"wearoff"=>"The illusion disappears.",
				"badguydefmod"=>0,
				"roundmsg"=>"`%{badguy} ran right into your trap while trying to escape the raging flames!",
				"schema"=>"module-specialtysystem_genjutsu"
			));
			break;
		case "genjutsu7":
			apply_buff('genjutsu1',array(
				"startmsg"=>"`%`i`%Kokuangyou `5no `%Jutsu`i`n`vYou blind {badguy} with total `~`bdarkness`b.",
				"name"=>"`%Kokuangyou `5no `%Jutsu",
				"rounds"=>10,
				"wearoff"=>"{badguy} snaps out of the illusion.",
				"badguyatkmod"=>0.25,
				"badguydefmod"=>0,
				"roundmsg"=>"`%Your illusion conceals you from {badguy} totally!",
				"schema"=>"module-specialtysystem_genjutsu"
			));
			break;

	}
	specialtysystem_incrementuses("specialtysystem_genjutsu",httpget('cost'));
	return;
}

function specialtysystem_genjutsu_dohook($hookname,$args){
	switch ($hookname) {
	case "specialtysystem-register":
		$args[]=array(
			"spec_name"=>'Genjutsu',
			"spec_colour"=>'`%',
			"spec_shortdescription"=>'`%Prey on weak minds!',
			"spec_longdescription"=>'`5Growing up, you always loved playing tricks on the minds of others more than anything else ... so you studied genjutsu, deceiving your enemies and attacking their minds.',
			"modulename"=>'specialtysystem_genjutsu',
			"fightnav_active"=>'1',
			"newday_active"=>'0',
			"dragonkill-active"=>'0',
			"dragonkill_minimum_requirement"=>'0'
			);
		break;
	}
	return $args;
}

function specialtysystem_genjutsu_run(){
}
?>
