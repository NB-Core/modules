<?php

function specialtysystem_fire_getmoduleinfo(){
	$info = array(
		"name" => "Specialty System - Fire",
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

function specialtysystem_fire_install(){
	module_addhook("specialtysystem-register");
	return true;
}

function specialtysystem_fire_uninstall(){
	require_once("modules/specialtysystem/uninstall.php");
	specialtysystem_uninstall("specialtysystem_fire");
	return true;
}

function specialtysystem_fire_fightnav(){
	global $session;
	require_once("modules/specialtysystem/functions.php");
	$uses=specialtysystem_availableuses("specialtysystem_fire");
	$name=translate_inline('Katon Ninjutsu (Fire)');
	tlschema('module-specialtysystem_fire');
	if ($uses > 0) {
		specialtysystem_addfightheadline($name, $uses,specialtysystem_getskillpoints("specialtysystem_fire"));
		specialtysystem_addfightnav("Gōkakyū no Jutsu","fire1&cost=1",1);
	}
	if ($uses > 2) {
		specialtysystem_addfightnav("Hōsenka no Jutsu","fire2&cost=3",3);
	}
	if ($uses > 5) {
		specialtysystem_addfightnav("Ryūka no Jutsu","fire3&cost=6",6);
	}
	if ($uses > 9) {
		specialtysystem_addfightnav("Haisekishō","fire4&cost=10",10);
	}
	if ($uses > 14) {
		specialtysystem_addfightnav("`\$Kar`qyū `\$En`qdan","fire5&cost=15",15);
	}
	if ($uses > 17) {
		specialtysystem_addfightnav("`\$Kar`qyū`Qdan","fire6&cost=18",18);
	}
	if ($uses > 10 && $session['user']['hashorse']==3) { //gamabunta
		specialtysystem_addfightnav("`@Gamayo `\$Emu`qdan","gamaemudan&cost=11",11);
	}
	tlschema();
	return specialtysystem_getfightnav();
}

function specialtysystem_fire_apply($skillname){
	global $session;
	require_once("modules/specialtysystem/functions.php");
	switch($skillname){
		case "fire1":
			apply_buff('fire1',array(
				"startmsg"=>"`i`qKa`\$ton `q-`\$ Gōkakyū no Jutsu!`i`n`qYou `\$utilize your chakra and exhale a large ball of `4fire `\$from your mouth.",
				"name"=>"`\$Katon `4- `\$Gōkakyū `4no `\$Jutsu",
				"rounds"=>1,
				"areadamage"=>true,
				"minbadguydamage"=>15,
				"maxbadguydamage"=>20,
				"minioncount"=>1,
				"effectmsg"=>"{badguy} suffers {damage} damage from burns!",
				"schema"=>"module-specialtysystem_fire"
			));
			break;
		case "fire2":
			apply_buff('fire2',array(
				"startmsg"=>"`i`qKa`\$ton `q-`\$ Hōsenka!`i`n`qYou `\$ send multiple balls of `4fire `\$at {badguy}.",
				"name"=>"`\$Katon `4- `\$Hōsenka `4no `\$Jutsu",
				"rounds"=>5,
				"minbadguydamage"=>$session['user']['level'],
				"maxbadguydamage"=>$session['user']['level']+5,
				"minioncount"=>floor($session['user']['level']/3+1),
				"effectmsg"=>"{badguy} suffers {damage} damage from burns!",
				"effectnodmgmsg"=>"The fire ball misses {badguy}.",
				"schema"=>"module-specialtysystem_fire"
			));
			break;
		case "fire3":
			apply_buff('fire3',array(
				"startmsg"=>"`i`qKa`\$ton `q-`\$ Ryūka no Jutsu!`i`n`qYou `\$breath out a burst of flame towards {badguy}.",
				"name"=>"`\$Katon `4- `\$Ryūka `4no `\$Jutsu",
				"rounds"=>1,
				"areadamage"=>true,
				"minbadguydamage"=>15+$session['user']['dragonkills'],
				"maxbadguydamage"=>40+$session['user']['dragonkills'],
				"minioncount"=>1,
				"effectmsg"=>"{badguy} suffers {damage} damage from burns!",
				"schema"=>"module-specialtysystem_fire"
			));
			break;
		case "fire4":
			apply_buff('fire4',array(
				"startmsg"=>"`i`qKa`\$ton `q-`\$ Hai`~seki`tshō!`i`n`qYou `\$breathe out a cloud of superheated ash.",
				"name"=>"`\$Katon `4- `\$Ha`4i`~seki`tshō",
				"rounds"=>5,
				"areadamage"=>true,
				"wearoff"=>"The ash was blown away by the wind.",
				"minbadguydamage"=>10+$session['user']['dragonkills'],
				"maxbadguydamage"=>20+$session['user']['dragonkills'],
				"minioncount"=>5,
				"effectmsg"=>"{badguy} suffers {damage} damage from burns!",
				"effectnodmgmsg"=>"{badguy} manages to protect itself from the ash.",
				"schema"=>"module-specialtysystem_fire"
			));
			break;
		case "fire5":
			apply_buff('fire6',array(
				"startmsg"=>"`\$Katon - `4Ka`\$ryū `4En`\$dan! You shoot an enormous ball of flame in the shape of a dragon from your mouth at {badguy}.",
				"name"=>"`\$Katon - `4Ka`\$ryū `4En`\$dan",
				"rounds"=>1,
				"areadamage"=>true,
				"minbadguydamage"=>90+$session['user']['dragonkills'],
				"maxbadguydamage"=>150+$session['user']['dragonkills'],
				"minioncount"=>1,
				"effectmsg"=>"{badguy} suffers {damage} damage from burns!",
				"schema"=>"module-specialtysystem_fire"
			));
			break;
		case "fire6":
			apply_buff('fire7',array(
				"startmsg"=>"`\$Katon - `4Ka`\$ryū`4dan, You create a likeness of a dragon's head out of mud and ignite the mud balls that it launches from its mouth.",
				"name"=>"`\$Katon - `4Ka`\$ryū `4En`\$dan",
				"rounds"=>1,
				"areadamage"=>true,
				"minbadguydamage"=>90+$session['user']['dragonkills'],
				"maxbadguydamage"=>120+$session['user']['dragonkills'],
				"minioncount"=>3,
				"effectmsg"=>"{badguy} suffers {damage} damage from the devastating attack!",
				"effectnodmgmsg"=>"The flaming mud ball misses {badguy}.",
				"schema"=>"module-specialtysystem_fire"
			));
			break;
		case "gamaemudan":
			apply_buff('gamayoemudan',array(
				"startmsg"=>"`i`qKa`\$ton `q-`\$  `\$Gama`qyou `\$Emu`qdan!`i`n`qYou `\$sent a fire blast from your mouth to Gamabunta's oil-blast... and engulf {badguy} in flames!",
				"name"=>"`\$Gama`qyou `\$Emu`qdan",
				"rounds"=>10,
				"wearoff"=>"The fire extinguishes and only the ash remains.",
				"areadamage"=>true,
				"minbadguydamage"=>7+$session['user']['dragonkills'],
				"maxbadguydamage"=>5+$session['user']['level']*3+$session['user']['dragonkills'],
				"minioncount"=>3,
				"effectmsg"=>"`q{badguy}`q suffers {damage} damage!",
				"effectnodmgmsg"=>"`qYou only produce hot air.",
				"schema"=>"module-specialtysystem_fire"
			));

	}
	specialtysystem_incrementuses("specialtysystem_fire",httpget('cost'));
	return;
}

function specialtysystem_fire_dohook($hookname,$args){
	switch ($hookname) {
	case "specialtysystem-register":
		$args[]=array(
			"spec_name"=>'Katon Ninjutsu',
			"spec_colour"=>'`$',
			"spec_shortdescription"=>'`$Flames of destruction!',
			"spec_longdescription"=>'`5Growing up, you always loved playing with fire ... so you studied katon ninjutsu, turning everything in your path into ashes.',
			"modulename"=>'specialtysystem_fire',
			"fightnav_active"=>'1',
			"newday_active"=>'0',
			"dragonkill-active"=>'0',
			"dragonkill_minimum_requirement"=>'6',
			"stat_requirements"=>array(
				"intelligence"=>12,
				"strength"=>12,
				"dexterity"=>12,
				),			
			);
		break;
	}
	return $args;
}

function specialtysystem_fire_run(){
}
?>
