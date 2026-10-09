<?php

// Custom Weapon and Armor
// 23 Jan 2005
// ver 1.0 by Booger - bigredx (a) sci -dot- fi


require_once("lib/http.php");

function customeq_getmoduleinfo(){
	require("modules/customeq/customeq_getmoduleinfo.php");
	return $info;
}

function customeq_install(){
	module_addhook("lodge");
	module_addhook("pointsdesc");
	module_addhook("footer-weapons");
	module_addhook("footer-armor");
	module_addhook("dragonkilltext");
	module_addhook("charstats");
	return true;
}

function customeq_uninstall(){
	return true;
}

function customeq_dohook($hookname,$args){
	global $session;
	$keep = get_module_setting("keep");
	$weapon = get_module_pref("weaponname");
	$armor = get_module_pref("armorname");
	$keepeq = get_module_pref("keepeq");
	switch($hookname){
	case "pointsdesc":
		require_once("modules/customeq/customeq_pointsdesc.php");
		break;
	case "lodge":
		$wcost = get_module_setting("extraweapon");
		if ($wcost < 1 || !$weapon || $keep)
			$wcost = get_module_setting("weaponcost");
		$acost = get_module_setting("extraarmor");
		if ($acost < 1 || !$armor || $keep)
			$acost = get_module_setting("armorcost");
		addnav(array("Custom Weapon (%s points)", $wcost),
				"runmodule.php?module=customeq&op=buy&subop=weapon");
		addnav(array("Custom Armor (%s points)", $acost),
				"runmodule.php?module=customeq&op=buy&subop=armor");
		break;
	case "footer-weapons":
		$op = httpget("op");
		if (($keep != 2 || $keepeq) && $weapon && $op == "buy"){
			require_once("modules/customeq/customeq_func.php");
			customeq_wchange($weapon);
			output("`n`n`7As you step out the door you realize that your new weapon has magically turned back into `5%s`7!`0`n", $session['user']['weapon']);
		}elseif ($weapon && $op == "buy"){
			if (is_module_active("costumeshop") &&
					get_module_pref("weapon","costumeshop"))
				customeq_wchange($weapon);
			else
				set_module_pref("weaponname","");
		}
		break;
	case "footer-armor":
		$op = httpget("op");
		if (($keep != 2 || $keepeq) && $armor && $op == "buy"){
			require_once("modules/customeq/customeq_func.php");		
			customeq_achange($armor);
			output("`n`n`5As you put on your new armor, it magically changes back into `&%s`5!`0`n", $session['user']['armor']);
		}elseif ($armor && $op == "buy"){
			if (is_module_active("costumeshop") &&
					get_module_pref("armor","costumeshop"))
				customeq_achange($armor);
			else
				set_module_pref("armorname","");
		}
		break;
	case "dragonkilltext":
		if ($keep == 0 || $keepeq){
			require_once("modules/customeq/customeq_func.php");
			if ($weapon) customeq_wchange($weapon);
			if ($armor) customeq_achange($armor);
		}else{
			if ($weapon) set_module_pref("weaponname","");
			if ($armor) set_module_pref("armorname","");
		}
		break;
	case "charstats":
		if (get_module_setting("showlvl")){
			$wmsg = getcharstat("Equipment Info", "Weapon");
			if (!strpos($wmsg," (".$session['user']['weapondmg'].")")){
				$wmsg .= " (".$session['user']['weapondmg'].")";
				setcharstat("Equipment Info", "Weapon", $wmsg);
			}
			$amsg = getcharstat("Equipment Info", "Armor");
			if (!strpos($amsg," (".$session['user']['armordef'].")")){
				$amsg .= " (".$session['user']['armordef'].")";
				setcharstat("Equipment Info", "Armor", $amsg);
			}
		}
		break;
	}
	return $args;
}


function customeq_run(){
	require_once("modules/customeq/customeq_run.php");
}
?>
