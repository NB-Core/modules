<?php
declare(strict_types=1);

/**
 * Module information.
 *
 * @return array<string, mixed>
 */
function specialtysystem_lightning_getmoduleinfo(): array {
	$info = array(
		"name" => "Specialty System - Lightning",
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
function specialtysystem_lightning_install(): bool {
	module_addhook("specialtysystem-register");
	return true;
}

/**
 * Uninstall the module.
 *
 * @return bool
 */
function specialtysystem_lightning_uninstall(): bool {
	require_once("modules/specialtysystem/uninstall.php");
	specialtysystem_uninstall("specialtysystem_lightning");
	return true;
}

/**
 * Build fight navigation entries.
 *
 * @return array
 */
function specialtysystem_lightning_fightnav(): array {
	global $session;
	require_once("modules/specialtysystem/functions.php");
	$uses=specialtysystem_availableuses("specialtysystem_lightning");
	$name=translate_inline('Rai Ninjutsu (Lightning)');
	tlschema('module-specialtysystem_lightning');
	if ($uses > 0) {
		specialtysystem_addfightheadline($name, $uses,specialtysystem_getskillpoints("specialtysystem_lightning"));
		specialtysystem_addfightnav("Raigeki no Yoroi","lightning1&cost=1",1);
	}
	if ($uses > 2) {
		specialtysystem_addfightnav("Raikyuu","lightning2&cost=3",3);
	}
	if ($uses > 5) {
		specialtysystem_addfightnav("Raiton: Hiraishin","lightning3&cost=6",6);
	}
	if ($uses > 9) {
		specialtysystem_addfightnav("Ikazuchi no Kiba","lightning4&cost=10",10);
	}
	if ($uses > 14) {
		specialtysystem_addfightnav("Raizou Ikazuchi wo Utte","lightning5&cost=15",15);
	}
	if ($uses > 15) {
		specialtysystem_addfightnav("Raiton: Kaminari Shibari","lightning6&cost=16",16);
	}
	if ($uses > 17) {
		specialtysystem_addfightnav("Rairyuu no Tatsumaki","lightning7&cost=18",18);
	}
	if ($uses > 22) {
		specialtysystem_addfightnav("Ikazuchi Hakai","lightning8&cost=23",23);
	}
	tlschema();
	return specialtysystem_getfightnav();
}

/**
 * Apply a selected specialty skill.
 *
 * @param string $skillname
 */
function specialtysystem_lightning_apply(string $skillname): void {
	global $session;
	require_once("modules/specialtysystem/functions.php");
	switch($skillname){
		case "lightning1":
			apply_buff('lightning1',array(
				"startmsg"=>"`i`tRaigeki no Yoroi!`i`n`qYou `vsurround yourself with electricity.`b",
				"name"=>"`tRaigeki no Yoroi",
				"rounds"=>5,
				"wearoff"=>"The electricity around you was neutralized.",
				"damageshield"=>0.20,
				"effectmsg"=>"{badguy} suffers {damage} damage from the electric shock!",
				"schema"=>"module-specialtysystem_lightning"
			));
			break;
		case "lightning2":
			apply_buff('lightning2',array(
				"startmsg"=>"`i`tRaikyuu!`i`n`qYou `vcreate a ball of electrical energy and launch it at {badguy}.",
				"name"=>"`tRaikyuu",
				"rounds"=>10,
				"wearoff"=>"The electricity in {badguy}'s body has neutralized.",
				"minbadguydamage"=>5,
				"maxbadguydamage"=>15,
				"minioncount"=>1,
				"effectmsg"=>"{badguy} suffers {damage} damage from the electric shock!",
				"schema"=>"module-specialtysystem_lightning"
			));
			break;
		case "lightning3":
			apply_buff('lightning3',array(
				"startmsg"=>"`i`t`^Raiton: `yHiraishin!`i`n`qYou `vsummon lightning from the sky to your hand and then shoot it at your opponent.",
				"name"=>"`^Raiton: `tHiraishin",
				"rounds"=>1,
				"minbadguydamage"=>60+$session['user']['dragonkills'],
				"maxbadguydamage"=>80+$session['user']['dragonkills'],
				"minioncount"=>1,
				"effectmsg"=>"The lightning hits {badguy}, doing {damage} damage!",
				"schema"=>"module-specialtysystem_lightning"
			));
			break;
		case "lightning4":
			apply_buff('lightning4',array(
				"startmsg"=>"`i`tIkazuchi no Kiba!`i`n`qYou `vsend an electrical essence into the clouds, allowing you to create lightning strikes.",
				"name"=>"`tIkazuchi no Kiba",
				"rounds"=>5,
				"areadamage"=>true,
				"wearoff"=>"The electrical essence in the clouds has neutralized.",
				"minbadguydamage"=>15+$session['user']['dragonkills'],
				"maxbadguydamage"=>30+$session['user']['dragonkills'],
				"minioncount"=>1,
				"effectmsg"=>"{badguy} suffers {damage} damage from the lightning strike!",
				"schema"=>"module-specialtysystem_lightning"
			));
			break;
		case "lightning5":
			apply_buff('lightning5',array(
				"startmsg"=>"`i`tRaizou Ikazuchi wo Utte!`i`n`qYou `vcreate several thunderbolts that cut through the ground and chase after {badguy}.",
				"name"=>"`tRaizou Ikazuchi wo Utte",
				"rounds"=>10,
				"wearoff"=>"The thunderbolts were neutralized.",
				"minbadguydamage"=>15+$session['user']['dragonkills'],
				"maxbadguydamage"=>30+$session['user']['dragonkills'],
				"minioncount"=>e_rand(1,4),
				"effectmsg"=>"{badguy} suffers {damage} damage from the rushing current!",
				"effectnodmgmsg"=>"{badguy} manages to dodge the thunderbolts!",
				"schema"=>"module-specialtysystem_lightning"
			));
			break;
		case "lightning6":
			apply_buff('lightning6',array(
				"startmsg"=>"`i`^Raiton`i: `^K`taminari `^S`thibari! `i`n`qYou `vcreate a three sided wall of electricity to bind {badguy}.",
				"name"=>"`^Raiton: `^K`taminari `^S`thibari",
				"rounds"=>3,
				"wearoff"=>"The wall has been broken.",
				"invulnerable"=>true,
				"minbadguydamage"=>5,
				"maxbadguydamage"=>15,
				"minioncount"=>1,
				"effectmsg"=>"{badguy} suffers {damage} damage from touching the wall!",
				"schema"=>"module-specialtysystem_lightning"
			));
			break;
		case "lightning7":
			apply_buff('lightning7',array(
				"startmsg"=>"`i`tRairyuu no Tatsumaki!`i`n`qYou `vspin around very quickly, forming the electricity around you into a likeness of a dragon's head.",
				"name"=>"`tRairyuu no Tatsumaki",
				"rounds"=>5,
				"areadamage"=>true,
				"wearoff"=>"The electrical vortex has neutralized.",
				"minbadguydamage"=>30+$session['user']['dragonkills'],
				"maxbadguydamage"=>60+$session['user']['dragonkills'],
				"minioncount"=>e_rand(2,6),
				"effectmsg"=>"{badguy} suffers {damage} damage from painful vortex of electricity!",
				"effectnodmgmsg"=>"The electric current did not have any effect on {badguy}!",
				"schema"=>"module-specialtysystem_lightning"
			));
			break;
		case "lightning8":
			apply_buff('lightning8',array(
				"startmsg"=>"`^I`tkazuchi `^H`takai!`i`n`qYou `vplace your hands on the ground and send an enormous bolt of lightning that cuts through the ground towards {badguy}.`b",
				"name"=>"`^I`tkazuchi `^H`takai",
				"rounds"=>1,
				"areadamage"=>true,
				"minbadguydamage"=>180+$session['user']['dragonkills'],
				"maxbadguydamage"=>220+$session['user']['dragonkills'],
				"minioncount"=>1,
				"effectmsg"=>"The bolt of lightning causes devastating destruction on its path towards {badguy}, generated with heat and power that does {damage} damage!",
				"schema"=>"module-specialtysystem_lightning"
			));
			break;

	}
	specialtysystem_incrementuses("specialtysystem_lightning",httpget('cost'));
	return;
}

/**
 * Handle module hooks.
 *
 * @param string $hookname
 * @param array $args
 * @return array
 */
function specialtysystem_lightning_dohook(string $hookname, array $args): array {
	switch ($hookname) {
	case "specialtysystem-register":
		$args[]=array(
			"spec_name"=>'Rai Ninjutsu',
			"spec_colour"=>'`t',
			"spec_shortdescription"=>'`t`bThe shocking technique!`b',
			"spec_longdescription"=>'`5Growing up, you always loved watching lightning strikes ... so you studied rai ninjutsu, giving you the power to control lightning.',
			"modulename"=>'specialtysystem_lightning',
			"fightnav_active"=>'1',
			"newday_active"=>'0',
			"dragonkill-active"=>'0',
			"dragonkill_minimum_requirement"=>'9'
			);
		break;
	}
	return $args;
}

/**
 * Module runtime.
 */
function specialtysystem_lightning_run(): void {
}
?>
