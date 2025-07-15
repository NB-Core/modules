<?php
declare(strict_types=1);

/**
 * Module information.
 *
 * @return array<string, mixed>
 */
function specialtysystem_water_getmoduleinfo(): array {
	$info = array(
		"name" => "Specialty System - Water",
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
function specialtysystem_water_install(): bool {
	module_addhook("specialtysystem-register");
	return true;
}

/**
 * Uninstall the module.
 *
 * @return bool
 */
function specialtysystem_water_uninstall(): bool {
	require_once("modules/specialtysystem/uninstall.php");
	specialtysystem_uninstall("specialtysystem_water");
	return true;
}

/**
 * Build fight navigation entries.
 *
 * @return array
 */
function specialtysystem_water_fightnav(): array {
	global $session;
	require_once("modules/specialtysystem/functions.php");
	$uses=specialtysystem_availableuses("specialtysystem_water");
	$name=translate_inline('Suiton Ninjutsu (Water)');
	tlschema('module-specialtysystem_water');
	if ($uses > 0) {
		specialtysystem_addfightheadline($name, $uses,specialtysystem_getskillpoints("specialtysystem_water"));
		specialtysystem_addfightnav("Kirigakure no Jutsu","water1&cost=1",1);
	}
	if ($uses > 2) {
		specialtysystem_addfightnav("Kaihoudan","water2&cost=3",3);
	}
	if ($uses > 4) {
		specialtysystem_addfightnav("Suijinheki","water3&cost=5",5);
	}
	if ($uses > 9) {
		specialtysystem_addfightnav("Suiryuudan no Jutsu","water4&cost=10",10);
	}
	if ($uses > 14) {
		specialtysystem_addfightnav("Goshokuzame","water5&cost=15",15);
	}
	if ($uses > 15) {
		specialtysystem_addfightnav("Daibakufu no Jutsu","water6&cost=16",16);
	}
	if ($uses > 17) {
		specialtysystem_addfightnav("Daibaku no Jutsu","water7&cost=18",18);
	}
	if ($uses > 19) {
		specialtysystem_addfightnav("Daibakure no Jutsu","water8&cost=20",20);
	}
	tlschema();
	return specialtysystem_getfightnav();
}

/**
 * Apply a selected specialty skill.
 *
 * @param string $skillname
 */
function specialtysystem_water_apply(string $skillname): void {
	global $session;
	require_once("modules/specialtysystem/functions.php");
	switch($skillname){
		case "water1":
			apply_buff('water1',array(
				"startmsg"=>"`1`i`!Sui`1ton `&-`! Kirigakure no Jutsu!`i`n`qYou `\$envelop the surrounding area in a dense mist.",
				"name"=>"`1Kirigakure no Jutsu",
				"rounds"=>5,
				"wearoff"=>"The mist clears away.",
				"badguyatkmod"=>0.85,
				"badguydefmod"=>0.85,
				"roundmsg"=>"The mist conceals you from {badguy} a bit!",
				"schema"=>"module-specialtysystem_water"
			));
			break;
		case "water2":
			apply_buff('water2',array(
				"startmsg"=>"`1`i`!Sui`1ton `&-`! Kaihoudan!`i`n`qYou `\$shoot a strong stream of water from your mouth at {badguy}.",
				"name"=>"`1Suiton - Kaihoudan",
				"rounds"=>1,
				"areadamage"=>true,
				"minbadguydamage"=>15+$session['user']['level'],
				"maxbadguydamage"=>30+$session['user']['level']*2,
				"minioncount"=>1,
				"effectmsg"=>"{badguy} suffers {damage} damage from the blast!",
				"schema"=>"module-specialtysystem_water"
			));
			break;
		case "water3":
			apply_buff('water3',array(
				"startmsg"=>"`1`i`!Sui`1ton `&-`! Suijinheki!`i`n`qYou `\$create a water barrier to protect yourself from attacks.",
				"name"=>"`1Suiton - Suijinheki",
				"rounds"=>5,
				"wearoff"=>"The water barrier disappears.",
				"defmod"=>1.80,
				"schema"=>"module-specialtysystem_water"
			));
			break;
		case "water4":
			apply_buff('water4',array(
				"startmsg"=>"`1`i`!Sui`1ton `&-`! Suiryuudan no Jutsu!`i`n`qYou `\$create a huge current of water in the form of a dragon and sends it towards {badguy}.",
				"name"=>"`1Suiton - Suiryuudan no Jutsu",
				"rounds"=>5,
				"areadamage"=>true,
				"wearoff"=>"The dragon returns to water.",
				"minbadguydamage"=>15+$session['user']['level'],
				"maxbadguydamage"=>25+$session['user']['level'],
				"minioncount"=>3,
				"effectmsg"=>"{badguy} suffers {damage} damage from the blast!",
				"effectnodmgmsg"=>"{badguy} manages to dodge out of the way.",
				"schema"=>"module-specialtysystem_water"
			));
			break;
		case "water5":
			apply_buff('water5',array(
				"startmsg"=>"`1`i`!Sui`1ton `&-`! Goshokuzame!`i`n`qYou `\$trap {badguy} in water and send five attacking sharks after him.",
				"name"=>"`1Suiton - Goshokuzame",
				"rounds"=>5,
				"wearoff"=>"The sharks disappear.",
				"minbadguydamage"=>15+$session['user']['level'],
				"maxbadguydamage"=>25+$session['user']['level'],
				"minioncount"=>5,
				"effectmsg"=>"{badguy} suffers {damage} damage from the shark attack!",
				"effectnodmgmsg"=>"The shark miss!",
				"schema"=>"module-specialtysystem_water"
			));
			break;
		case "water6":
			apply_buff('water6',array(
				"startmsg"=>"`1`i`!Sui`1ton `&-`! Daibakufu no Jutsu!`i`n`qYou `\$create a massive blast of water.",
				"name"=>"`1Suiton - Daibakufu no Jutsu",
				"rounds"=>5,
				"areadamage"=>true,
				"wearoff"=>"The massice blast of water washes everything away.",
				"minbadguydamage"=>40+$session['user']['level']+$session['user']['dragonkills'],
				"maxbadguydamage"=>60+$session['user']['level']+$session['user']['dragonkills'],
				"minioncount"=>1,
				"effectmsg"=>"{badguy} suffers {damage} damage from the blast!",
				"schema"=>"module-specialtysystem_water"
			));
			break;
		case "water7":
			apply_buff('water7',array(
				"startmsg"=>"`1`i`!Sui`1ton `&-`! Daibaku no Jutsu!`i`n`qYou `\$create a massive tidal wave.",
				"name"=>"`1Suiton - Daibaku no Jutsu",
				"rounds"=>5,
				"areadamage"=>true,
				"wearoff"=>"You can't create anymore waves.",
				"minbadguydamage"=>20+$session['user']['level']+$session['user']['dragonkills'],
				"maxbadguydamage"=>40+$session['user']['level']+$session['user']['dragonkills'],
				"minioncount"=>5,
				"effectmsg"=>"{badguy} suffers {damage} damage from the crushing wave!",
				"effectnodmgmsg"=>"{badguy} escapes the crushing wave!",
				"schema"=>"module-specialtysystem_water"
			));
			break;
		case "water8":
			apply_buff('water8',array(
				"startmsg"=>"`1`i`!Sui`1ton `&-`! Daibakure no Jutsu!`i`n`qYou `\$create an enormous  inescapable maelstrom.",
				"name"=>"`1Suiton - Daibakure no Jutsu",
				"rounds"=>5,
				"areadamage"=>true,
				"wearoff"=>"The maelstrom disappears.",
				"minbadguydamage"=>25+$session['user']['level']+$session['user']['dragonkills'],
				"maxbadguydamage"=>40+$session['user']['level']+$session['user']['dragonkills'],
				"minioncount"=>6,
				"effectmsg"=>"{badguy} suffers {damage} damage from drowning!",
				"effectnodmgmsg"=>"{badguy} manages to stay afloat!",
				"schema"=>"module-specialtysystem_water"
			));
			break;

	}
	specialtysystem_incrementuses("specialtysystem_water",httpget('cost'));
	return;
}

/**
 * Handle module hooks.
 *
 * @param string $hookname
 * @param array $args
 * @return array
 */
function specialtysystem_water_dohook(string $hookname, array $args): array {
	switch ($hookname) {
	case "specialtysystem-register":
		$args[]=array(
			"spec_name"=>'Suiton Ninjutsu',
			"spec_colour"=>'`1',
			"spec_shortdescription"=>'`1The power that has no form!',
			"spec_longdescription"=>'`5Growing up, you always loved playing with water ... so you studied water ninjutsu, washing your enemies away.',
			"modulename"=>'specialtysystem_water',
			"fightnav_active"=>'1',
			"newday_active"=>'0',
			"dragonkill-active"=>'0',
			"dragonkill_minimum_requirement"=>'1',
			"stat_requirements"=>array(
				"intelligence"=>11,
				),
			);
		break;
	}
	return $args;
}

/**
 * Module runtime.
 */
function specialtysystem_water_run(): void {
}
?>
