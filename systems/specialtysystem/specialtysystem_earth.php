<?php

function specialtysystem_earth_getmoduleinfo(){
	$info = array(
		"name" => "Specialty System - Earth",
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

function specialtysystem_earth_install(){
	module_addhook("specialtysystem-register");
	return true;
}

function specialtysystem_earth_uninstall(){
	require_once("modules/specialtysystem/uninstall.php");
	specialtysystem_uninstall("specialtysystem_earth");
	return true;
}

function specialtysystem_earth_fightnav(){
	global $session;
	require_once("modules/specialtysystem/functions.php");
	$uses=specialtysystem_availableuses("specialtysystem_earth");
	$name=translate_inline('Doton Ninjutsu (Earth)');
	tlschema('module-specialtysystem_earth');
	if ($uses > 0) {
		specialtysystem_addfightheadline($name, $uses,specialtysystem_getskillpoints("specialtysystem_earth"));
		specialtysystem_addfightnav("Retsudotenshou","earth1&cost=1",1);
	}
	if ($uses > 2) {
		specialtysystem_addfightnav("Doroku Gaeshi","earth2&cost=3",3);
	}
	if ($uses > 4) {
		specialtysystem_addfightnav("Iwayado Kuzushi","earth3&cost=5",5);
	}
	if ($uses > 9) {
		specialtysystem_addfightnav("Doroudoumu","earth4&cost=10",10);
	}
	if ($uses > 14) {
		specialtysystem_addfightnav("Doryuu Dango","earth5&cost=15",15);
	}
	if ($uses > 15) {
		specialtysystem_addfightnav("Doryuuheki","earth6&cost=16",16);
	}
	if ($uses > 17) {
		specialtysystem_addfightnav("Doryuudan","earth7&cost=18",18);
	}
	tlschema();
	return specialtysystem_getfightnav();
}

function specialtysystem_earth_apply($skillname){
	global $session;
	require_once("modules/specialtysystem/functions.php");
	switch($skillname){
		case "earth1":
			apply_buff('earth1',array(
				"startmsg"=>"`i`qDo`Qton `q-`t Restudotenshou!`i`n`qYou`Q attack {badguy} with nearby rocks.",
				"name"=>"`qRetsudotenshou",
				"rounds"=>1,
				"areadamage"=>true,
				"minbadguydamage"=>round(max($session['user']['level'],10)/3)+1,
				"maxbadguydamage"=>round(max($session['user']['level'],10)/3)+5,
				"minioncount"=>4,
				"effectmsg"=>"{badguy} suffers {damage} damage from the rocks!",
				"effectnodmgmsg"=>"The rocks barely hit!",
				"schema"=>"module-specialtysystem_earth"
			));
			break;
		case "earth2":
			apply_buff('earth2',array(
				"startmsg"=>"`i`qDo`Qton `q-`t Doroku Gaeshi!`i`n`qYou`Q create a large wall of earth.",
				"name"=>"`qDoroku Gaeshi",
				"rounds"=>5,
				"wearoff"=>"The wall breaks.",
				"defmod"=>1.5,
				"roundmsg"=>"`qThe wall protects you.",
				"schema"=>"module-specialtysystem_earth"
			));
			break;
		case "earth3":
			apply_buff('earth3',array(
				"startmsg"=>"`i`qDo`Qton `q-`t Iwayado Kuzushi!`i`n`QRocks are dislodged from above {badguy}.",
				"name"=>"`qIwayado Kuzushi",
				"rounds"=>1,
				"areadamage"=>true,
				"maxbadguydamage"=>round($session['user']['attack']*3,0),
				"minbadguydamage"=>round($session['user']['attack']*1.5,0),
				"minioncount"=>2,
				"effectmsg"=>"{badguy} suffers {damage} damage from falling a rock!",
				"effectnodmgmsg"=>"The rock misses!",
				"schema"=>"module-specialtysystem_earth"
			));
			break;
		case "earth4":
			apply_buff('earth4',array(
				"startmsg"=>"`i`qDo`Qton `q-`t Doroudoumu!`i`n`qYou`Q trap {badguy} inside a self-repairing dome of earth.",
				"name"=>"`qDoroudoumu",
				"rounds"=>10,
				"areadamage"=>true,
				"wearoff"=>"The dome falls apart.",
				"badguyatkmod"=>0.75,
				"minbadguydamage"=>5,
				"maxbadguydamage"=>10,
				"minioncount"=>1,
				"effectmsg"=>"{badguy} loses {damage} hitpoints!",
				"schema"=>"module-specialtysystem_earth"
			));
			break;
		case "earth5":
			apply_buff('earth5',array(
				"startmsg"=>"`i`qDo`Qton `q-`t Doryuu Dango!`i`n`qYou`Q hurl a large dumpling-shaped chunk of earth the size of a mausoleum at {badguy}.",
				"name"=>"`qDoryuu Dango",
				"rounds"=>1,
				"areadamage"=>true,
				"maxbadguydamage"=>round($session['user']['dragonkills']*3+$session['user']['level'],0)+50,
				"minbadguydamage"=>round($session['user']['dragonkills']*1.5+$session['user']['level'],0)+75,
				"minioncount"=>1,
				"effectmsg"=>"{badguy} suffers {damage} damage from being crushed!",
				"schema"=>"module-specialtysystem_earth"
			));
			break;
		case "earth6":
			apply_buff('earth6',array(
				"startmsg"=>"`i`qDo`Qton `q-`t Doryuuheki!`i`n`qYou`Q spit out a stream of mud that quickly grows and solidifies into a strong protective wall.",
				"name"=>"`qDoryuuheki",
				"rounds"=>10,
				"wearoff"=>"The wall breaks.",
				"defmod"=>2,
				"badguyatkmod"=>0.5,
				"effectnodmgmsg"=>"The wall protects you from harm!",
				"schema"=>"module-specialtysystem_earth"
			));
			break;
		case "earth7":
			apply_buff('earth7',array(
				"startmsg"=>"`i`qDo`Qton `q-`t Doryuudan!`i`n`qYou`Q create a likeness of a dragon's head that launches mud balls from its mouth at {badguy}.",
				"name"=>"`qDoryuudan",
				"rounds"=>1,
				"areadamage"=>true,
				"maxbadguydamage"=>round($session['user']['dragonkills']*3+$session['user']['level'],0)+55,
				"minbadguydamage"=>round($session['user']['dragonkills']*1.5+$session['user']['level'],0)+65,
				"minioncount"=>3,
				"effectmsg"=>"The mud ball impact {badguy} for {damage} damage!",
				"effectnodmgmsg"=>"The mud ball misses!",
				"schema"=>"module-specialtysystem_earth"
			));
			break;

	}
	specialtysystem_incrementuses("specialtysystem_earth",httpget('cost'));
	return;
}

function specialtysystem_earth_dohook($hookname,$args){
	switch ($hookname) {
	case "specialtysystem-register":
		$args[]=array(
			"spec_name"=>'Doton Ninjutsu',
			"spec_colour"=>'`q',
			"spec_shortdescription"=>'`qThe great earth!',
			"spec_longdescription"=>'`5Growing up, you always loved throwing stones and dirt at others ... so you studied earth ninjutsu, giving you the power protect yourself and crush other with land beneath you.',
			"modulename"=>'specialtysystem_earth',
			"fightnav_active"=>'1',
			"newday_active"=>'0',
			"dragonkill-active"=>'0',
			"dragonkill_minimum_requirement"=>'3',
			"stat_requirements"=>array(
				"strength"=>12,
				"constitution"=>12,
				),			
			);
		break;
	}
	return $args;
}

function specialtysystem_earth_run(){
}
?>
