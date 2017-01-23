<?php

function specialtysystem_ice_getmoduleinfo(){
	$info = array(
		"name" => "Specialty System - Ice",
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

function specialtysystem_ice_install(){
	module_addhook("specialtysystem-register");
	return true;
}

function specialtysystem_ice_uninstall(){
	require_once("modules/specialtysystem/uninstall.php");
	specialtysystem_uninstall("specialtysystem_ice");
	return true;
}

function specialtysystem_ice_fightnav(){
	global $session;
	require_once("modules/specialtysystem/functions.php");
	$uses=specialtysystem_availableuses("specialtysystem_ice");
	$name=translate_inline('Hyouton Ninjutsu (Ice)');
	tlschema('module-specialtysystem_ice');
	if ($uses > 0) {
		specialtysystem_addfightheadline($name, $uses,specialtysystem_getskillpoints("specialtysystem_ice"));
		specialtysystem_addfightnav("Hyourou no Jutsu","ice1&cost=1",1);
	}
	if ($uses > 2) {
		specialtysystem_addfightnav("Tsubame Fubuki","ice2&cost=3",3);
	}
	if ($uses > 5) {
		specialtysystem_addfightnav("Haryuu Mouko","ice3&cost=6",6);
	}
	if ($uses > 7) {
		specialtysystem_addfightnav("Ikkaku Hakugei","ice4&cost=8",8);
	}
	if ($uses > 11) {
		specialtysystem_addfightnav("Rouga Nadare no Jutsu","ice5&cost=12",12);
	}
	if ($uses > 15) {
		specialtysystem_addfightnav("Kokuryuu Boufuusetsu","ice6&cost=16",16);
	}
	if ($uses > 19) {
		specialtysystem_addfightnav("Souryuu Boufuusetsu","ice7&cost=20",20);
	}
	tlschema();
	return specialtysystem_getfightnav();
}

function specialtysystem_ice_apply($skillname){
	global $session;
	require_once("modules/specialtysystem/functions.php");
	switch($skillname){
		case "ice1":
			apply_buff('ice1',array(
				"startmsg"=>"`l`i`1Hyou`vton `q-`v Hyourou no Jutsu!`i`n`1You `v trap {badguy} in ice.",
				"name"=>"`lHyourou",
				"rounds"=>1,
				"badguyatkmod"=>0,
				"roundmsg"=>"{badguy} could not attack for one round!",
				"schema"=>"module-specialtysystem_ice"
			));
			break;
		case "ice2":
			apply_buff('ice2',array(
				"startmsg"=>"`l`i`1Hyou`vton `q-`v Tsubame Fubuki no Jutsu!`i`n`1You `v create a cluster of ice needles in the shape of miniature swallows.",
				"name"=>"`1Tsubame Fubuki",
				"rounds"=>5,
				"wearoff"=>"You ran out of swallows.",
				"minbadguydamage"=>5+min(35 ,$session['user']['dragonkills']),
				"maxbadguydamage"=>15+min(75,$session['user']['dragonkills']),
				"minioncount"=>3+$session['user']['level']/5,
				"effectmsg"=>"{badguy} suffers {damage} damage from the sharp wings!",
				"effectnodmgmsg"=>"The swallows miss!",
				"schema"=>"module-specialtysystem_ice"
			));
			break;
		case "ice3":
			apply_buff('ice3',array(
				"startmsg"=>"`i`1Hyou`vton `q-`v Haryuu Mouko no Jutsu!`i`n`1You `vcreate a large tiger out of ice.",
				"name"=>"`1Haryuu Mouko",
				"rounds"=>5,
				"wearoff"=>"The ice tiger scatters.",
				"minbadguydamage"=>15+min(35,$session['user']['dragonkills']),
				"maxbadguydamage"=>30+min(75,$session['user']['dragonkills']),
				"minioncount"=>1,
				"effectmsg"=>"{badguy} suffers {damage} damage from the tiger's sharp claws!",
				"effectnodmgmsg"=>"{badguy} manages to dodge the attack!",
				"schema"=>"module-specialtysystem_ice"
			));
			break;
		case "ice4":
			apply_buff('ice4',array(
				"startmsg"=>"`i`1Hyou`vton `q-`v Ikkaku Hakugei!`i`n`1You `vcreate a massive narwhal from summoned ice that jumps up and falls back down on {badguy}.",
				"name"=>"`lIkkaku Hakugei",
				"rounds"=>1,
				"areadamage"=>true,
				"minbadguydamage"=>30+$session['user']['dragonkills'],
				"maxbadguydamage"=>90+$session['user']['dragonkills'],
				"minioncount"=>1,
				"effectmsg"=>"{badguy} suffers {damage} damage from the impact!",
				"effectnodmgmsg"=>"{badguy} manages to escape to safety!",
				"schema"=>"module-specialtysystem_ice"
			));
			break;
		case "ice5":
			apply_buff('ice5',array(
				"startmsg"=>"`i`1Hyou`vton `q-`v Rouga Nadare no Jutsu!`i`n`1You `vcreate an avalanche of snow wolves.",
				"name"=>"`lRouga Nadare no Jutsu",
				"rounds"=>5,
				"areadamage"=>true,
				"wearoff"=>"The avalanche has stopped.",
				"minbadguydamage"=>15+$session['user']['dragonkills'],
				"maxbadguydamage"=>50+$session['user']['dragonkills'],
				"minioncount"=>4,
				"effectmsg"=>"{badguy} suffers {damage} damage from the biting and crushing!",
				"effectnodmgmsg"=>"The wolves only rush by {badguy}!",
				"schema"=>"module-specialtysystem_ice"
			));
			break;
		case "ice6":
			apply_buff('ice6',array(
				"startmsg"=>"`i`1Hyou`vton `q-`v Kokuryuu Boufuusetsu!`i`n`1You `vcreate an icy black dragon with red eyes and shoot it towards {badguy}.",
				"name"=>"`lKokuryuu Boufuusetsu",
				"rounds"=>1,
				"minbadguydamage"=>60+$session['user']['dragonkills'],
				"maxbadguydamage"=>100+$session['user']['dragonkills'],
				"minioncount"=>3,
				"effectmsg"=>"{badguy} suffers {damage} damage as the dragon rips through the air!",
				"effectnodmgmsg"=>"{badguy} manages to avoid being torn apart!",
				"schema"=>"module-specialtysystem_ice"
			));
			break;
		case "ice7":
			apply_buff('ice7',array(
				"startmsg"=>"`i`1Hyou`vton `q-`v Souryuu Boufuusetsu!`i`n`1You `vreleases two dragons of black snow that merge into a massive tornado at {badguy}.",
				"name"=>"`lSouryuu Boufuusetsu",
				"rounds"=>3,
				"minbadguydamage"=>30+$session['user']['dragonkills'],
				"maxbadguydamage"=>80+$session['user']['dragonkills'],
				"minioncount"=>6,
				"effectmsg"=>"{badguy} suffers {damage} damage from the freezing tornado!",
				"effectnodmgmsg"=>"{badguy} manages to avoid being froze and torn to bits!",
				"schema"=>"module-specialtysystem_ice"
			));
			break;

	}
	specialtysystem_incrementuses("specialtysystem_ice",httpget('cost'));
	return;
}

function specialtysystem_ice_dohook($hookname,$args){
	switch ($hookname) {
	case "specialtysystem-register":
		$args[]=array(
			"spec_name"=>'Hyouton Ninjutsu',
			"spec_colour"=>'`l',
			"spec_shortdescription"=>'`l`bThe cold and merciless technique!`b',
			"spec_longdescription"=>'`5Growing up, you always loved the icy, cold winter ... so you studied ice ninjutsu, killing your enemies in cold blood.',
			"modulename"=>'specialtysystem_ice',
			"fightnav_active"=>'1',
			"newday_active"=>'0',
			"dragonkill-active"=>'0',
			"dragonkill_minimum_requirement"=>'8',
			"stat_requirements"=>array(
				"intelligence"=>12,
				"strength"=>12,
				"constitution"=>16,
				),			
			);
		break;
	}
	return $args;
}

function specialtysystem_ice_run(){
}
?>
