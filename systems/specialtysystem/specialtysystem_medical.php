<?php
declare(strict_types=1);

/**
 * Module information.
 *
 * @return array<string, mixed>
 */
function specialtysystem_medical_getmoduleinfo(): array {
	$info = array(
		"name" => "Specialty System - Medical",
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

/**
 * Install the module.
 *
 * @return bool
 */
function specialtysystem_medical_install(): bool {
	module_addhook("specialtysystem-register");
	return true;
}

/**
 * Uninstall the module.
 *
 * @return bool
 */
function specialtysystem_medical_uninstall(): bool {
	require_once("modules/specialtysystem/uninstall.php");
	specialtysystem_uninstall("specialtysystem_medical");
	return true;
}

/**
 * Build fight navigation entries.
 *
 * @return array
 */
function specialtysystem_medical_fightnav(): array {
	global $session;
	require_once("modules/specialtysystem/functions.php");
	$uses=specialtysystem_availableuses("specialtysystem_medical");
	$name=translate_inline('Medical Ninjutsu');
	tlschema('module-specialtysystem_medical');
	if ($uses > 0) {
		specialtysystem_addfightheadline($name, $uses,specialtysystem_getskillpoints("specialtysystem_medical"));
		specialtysystem_addfightnav("Chikatsu Saisei no Jutsu","medical1&cost=1",1);
	}
	if ($uses > 1) {
		specialtysystem_addfightnav("Dokugiri","medical2&cost=2",2);
	}
	if ($uses > 4) {
		specialtysystem_addfightnav("Chakra Kyuuin no Jutsu","medical3&cost=5",5);
	}
	if ($uses > 7) {
		specialtysystem_addfightnav("Ranshinshou","medical4&cost=8",8);
	}
	if ($uses > 14) {
		specialtysystem_addfightnav("In'yu Shoumetsu","medical5&cost=15",15);
	}
	if ($uses > 15) {
		specialtysystem_addfightnav("Shousen Jutsu: attack muscles","medical6&cost=16",16);
	}
	if ($uses > 17) {
		specialtysystem_addfightnav("Shousen Jutsu: attack organs","medical7&cost=18",18);
	}
	if ($uses > 19) {
		specialtysystem_addfightnav("Souzou Saisei","medical8&cost=20",20);
	}
	tlschema();
	return specialtysystem_getfightnav();
}

/**
 * Apply a selected specialty skill.
 *
 * @param string $skillname
 */
function specialtysystem_medical_apply(string $skillname): void {
	global $session;
	require_once("modules/specialtysystem/functions.php");
	switch($skillname){
		case "medical1":
			apply_buff('medical1',array(
				"startmsg"=>"`!`iChikatsu Saisei no Jutsu!`i`n`tYou concentrate healing chakra to the palm of your hand.",
				"name"=>"`!Chikatsu Saisei no Jutsu",
				"rounds"=>5,
				"wearoff"=>"You stopped healing yourself.",
				"regen"=>$session['user']['level']+1,
				"effectmsg"=>"You regenerate for {damage} health.",
				"effectnodmgmsg"=>"You have no wounds to heal.",
				"schema"=>"module-specialtysystem_medical"
			));
			break;
		case "medical2":
			apply_buff('medical2',array(
				"startmsg"=>"`!`iDokugiri!`i`n`tYou blow a cloud of poison gas at the enemy.",
				"name"=>"`@Dokugiri",
				"rounds"=>5,
				"wearoff"=>"The poison gas clears away.",
				"areadamage"=>true,
				"minbadguydamage"=>5+min(50,$session['user']['dragonkills']*3),
				"maxbadguydamage"=>10+min(100,$session['user']['dragonkills']*3),
				"minioncount"=>1,
				"effectmsg"=>"{badguy} suffers {damage} damage from poisoning!",
				"schema"=>"module-specialtysystem_medical"
			));
			break;
		case "medical3":
			apply_buff('medical3',array(
				"startmsg"=>"`!`iChakra Kyuuin no Jutsu!`i`n`tYou grab on to {badguy} and begin absorbing chakra.",
				"name"=>"`!Chakra `1Kyuuin `vno `!Jutsu",
				"rounds"=>5,
				"wearoff"=>"You stopped absorbing chakra.",
				"regen"=>$session['user']['level']*2,
				"badguyatkmod"=>0.60,
				"effectmsg"=>"{badguy} loses chakra and is unable to attack well!",
				"effectnodmgmsg"=>"{badguy} loses chakra and is unable to attack well!",
				"schema"=>"module-specialtysystem_medical"
			));
			break;
		case "medical4":
			apply_buff('medical4',array(
				"startmsg"=>"`!`iRanshinshou!`i`n`tYou convert a small portion of chakra into electricity and hit the enemy's brain stem.",
				"name"=>"`!Ran`1shin`!shou",
				"rounds"=>10,
				"wearoff"=>"{badguy}'s movement returned to normal.",
				"badguyatkmod"=>0.60,
				"badguydefmod"=>0.60,
				"roundmsg"=>"{badguy} could not move properly!",
				"schema"=>"module-specialtysystem_medical"
			));
			break;
		case "medical5":
			apply_buff('medical5',array(
				"startmsg"=>"`!`iIn'yu Shoumetsu!`i`n`tYou concentrate healing chakra to the palm of your hand.",
				"name"=>"`!In'`1yuu `!Sho`1me`!tsu",
				"rounds"=>12,
				"wearoff"=>"You stopped regenerating.",
				"regen"=>round($session['user']['maxhitpoints']/10),
				"effectmsg"=>"You regenerate for {damage} health.",
				"effectnodmgmsg"=>"You have no wounds to regenerate.",
				"schema"=>"module-specialtysystem_medical"
			));
			break;
		case "medical6":
			apply_buff('medical6',array(
				"startmsg"=>"`!`iShousen Jutsu: attack muscles!`i`n`tYou focus your chakra into a blade and damage the enemy's muscles.",
				"name"=>"`!Shousen Jutsu: `\$attack muscles",
				"rounds"=>10,
				"wearoff"=>"{badguy}'s damaged muscles have healed.",
				"badguyatkmod"=>0.25,
				"effectnodmgmsg"=>"{badguy} could not attack well!",
				"schema"=>"module-specialtysystem_medical"
			));
			break;
		case "medical7":
			apply_buff('medical7',array(
				"startmsg"=>"`!`iShousen Jutsu: attack organs!`i`n`tYou focus your chakra into a blade and damage the enemy's organs.",
				"name"=>"`!Shousen Jutsu: `\$attack organs",
				"rounds"=>10,
				"wearoff"=>"{badguy}'s damaged organs have healed.",
				"badguydefmod"=>0.25,
				"roundmsg"=>"{badguy} can not defend well!",
				"schema"=>"module-specialtysystem_medical"
			));
			break;
		case "medical8":
		if ($session['user']['hitpoints']<$session['user']['maxhitpoints']) {
   $session['user']['hitpoints']=$session['user']['maxhitpoints'];
}
			apply_buff('medical8',array(
				"startmsg"=>"`!`iSouzou Saisei!`i`n`tYou release all the chakra you have stored up and heal all your wounds almost instantaneously.",
				"name"=>"`!Sou`1zou `!Sa`1i`!se`1i",
				"rounds"=>10,
				"wearoff"=>"You have exhausted all of your energy.",
				"defmod"=>3.0,
				"schema"=>"module-specialtysystem_medical"
			));
			break;

	}
	specialtysystem_incrementuses("specialtysystem_medical",httpget('cost'));
	return;
}

/**
 * Handle module hooks.
 *
 * @param string $hookname
 * @param array $args
 * @return array
 */
function specialtysystem_medical_dohook(string $hookname, array $args): array {
	switch ($hookname) {
	case "specialtysystem-register":
		$args[]=array(
			"spec_name"=>'Medical Ninjutsu',
			"spec_colour"=>'`!',
			"spec_shortdescription"=>'`!The power to give life!',
			"spec_longdescription"=>'`5Growing up, you always loved taking care of injured animals ... so you studied medical ninjutsu, protecting yourself and saving your team mates with your medical knowledge.',
			"modulename"=>'specialtysystem_medical',
			"fightnav_active"=>'1',
			"newday_active"=>'0',
			"dragonkill-active"=>'0',
			"dragonkill_minimum_requirement"=>4,
			"stat_requirements"=>array(
				"intelligence"=>14,
				"wisdom"=>12,
				),
			);
		break;
	}
	return $args;
}

/**
 * Module runtime.
 */
function specialtysystem_medical_run(): void {
}
?>
