<?php

function specialtysystem_basic_getmoduleinfo(){
	$info = array(
		"name" => "Specialty System - Basic Techniques",
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

function specialtysystem_basic_install(){
	module_addhook("specialtysystem-register");
	return true;
}

function specialtysystem_basic_uninstall(){
	require_once("modules/specialtysystem/uninstall.php");
	specialtysystem_uninstall("specialtysystem_basic");
	return true;
}

function specialtysystem_basic_fightnav(){
	global $session;
	require_once("modules/specialtysystem/functions.php");
	$uses=specialtysystem_availableuses("specialtysystem_basic");
	$name=translate_inline('Basic Ninjutsu');
	tlschema('module-specialtysystem_basic');
	if ($uses > 0) {
		specialtysystem_addfightheadline($name, $uses,specialtysystem_getskillpoints("specialtysystem_basic"));
		specialtysystem_addfightnav("Kawarimi no Jutsu","basic1&cost=1",1);
		specialtysystem_addfightnav("Kakuremino no Jutsu","basic2&cost=1",1);
		specialtysystem_addfightnav("Bunshin no Jutsu","basic3&cost=1",1);
	}
	tlschema();
	return specialtysystem_getfightnav();
}

function specialtysystem_basic_apply($skillname){
	global $session;
	require_once("modules/specialtysystem/functions.php");
	$u=&$session['user'];
	switch($skillname){
		case "basic1":
			apply_buff('basic1',array(
				"startmsg"=>"`v`iKawarimi no Jutsu!`i`n`tYou `qswitch places with a nearby item but can neither attack nor defend.",
				"name"=>"`vKawarimi no Jutsu",
				"rounds"=>1,
				"badguyatkmod"=>max(0.1,0.5-($u['intelligence']/100)),
				"badguydefmod"=>0,
				"atkmod"=>0,
				"schema"=>"module-specialtysystem_basic"
			));
			break;
		case "basic2":
			apply_buff('basic2',array(
				"startmsg"=>"`v`iKakuremino no Jutsu!`i`n`tYou `qmake yourself invisible to get into a better attack position.",
				"name"=>"`vKakuremino no Jutsu",
				"rounds"=>2,
				"wearoff"=>"You fail to hide any longer.",
				"badguyatkmod"=>0.8,
				"roundmsg"=>"{badguy} is not completely sure where you are!",
				"schema"=>"module-specialtysystem_basic"
			));
			break;
		case "basic3":
			/*apply_buff('basic3',array( //to be revised
				"startmsg"=>"`v`iBunshin no Jutsu!`i`n`tYou `qcreate some clones to cover up your current position",
				"name"=>"`vBunshin no Jutsu",
				"rounds"=>5,
				"wearoff"=>"Your bunshins crumble.",
				"defmod"=>1.1,
				"roundmsg"=>"Your bunshin cover up for you a bit!",
				"schema"=>"module-specialtysystem_basic"
			));*/
			$amount=min(5,round($u['intelligence']/7));
			$comp=array(
				"name"=>$session['user']['name'].translate_inline(" Bunshin"),
				"attack"=>0,
				"defense"=>0,
				"hitpoints"=>5,
				"maxhitpoints"=>5,
				"companionactive"=>1,
				"jointext"=>'',
				"cannotdie"=>0,
				"cannotbehealed"=>1,
				"dyingtext"=>"*poof*",
				"expireafterfight"=>1,
				"abilities"=>array(
					"fight"=>0,
					"heal"=>0,
					"magic"=>0,
					"defend"=>1,
					),
				"schema"=>"module-specialtysystem_basic"
			);
			for ($i=1;$i<=$amount;$i++) {
				$new=$comp;
				$new['name'].=" #".$i;
				apply_companion(sanitize($new['name']),$new,true);
			}
			break;
		break;
	}
	specialtysystem_incrementuses("specialtysystem_basic",httpget('cost'));
	return;
}

function specialtysystem_basic_dohook($hookname,$args){
	switch ($hookname) {
	case "specialtysystem-register":
		$args[]=array(
			"spec_name"=>'Basic Ninjutsu',
			"spec_colour"=>'`v',
			"spec_shortdescription"=>'-internal-',
			"spec_longdescription"=>'-internal-',
			"modulename"=>'specialtysystem_basic',
			"fightnav_active"=>'1',
			"newday_active"=>'0',
			"dragonkill-active"=>'0',
			"basic_uses"=>1,
			"dragonkill_minimum_requirement"=>-1
			);
		break;
	}
	return $args;
}

function specialtysystem_basic_run(){
}
?>
