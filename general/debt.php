<?php
function debt_getmoduleinfo(){
	$info = array(
		"name"=>"Alignment Modifications",
		"version"=>"1.0",
		"author"=>"`2Oliver Brendel",
		"category"=>"Alignment",
		"download"=>"",
		"settings"=>array(
					"Alignment Countermeasures - Preferences, title",
					"debt"=>"How many alignment points are lost when you have debts at DK, int|7",
					"chaotic"=>"How many demeanor points are lost when you have debts at DK, int|3",
					
					),
		"requires"=>array(
			"alignment"=>"1.9|Aligment Core by `~Godfather `2Chris `vVorndran",
			),
	);
	return $info;
}

function debt_install(){
	module_addhook_priority("runevent_fairy", 100);
	module_addhook_priority("runevent_abigail", 100);
	module_addhook_priority("runevent_distress", 100);
	module_addhook_priority("footer_crazyaudrey", 100);
	module_addhook_priority("runevent_crazyaudrey", 100);
	module_addhook_priority("runevent_ladyerwin", 100);
	module_addhook_priority("runevent_mrblack", 100);
	module_addhook_priority("runevent_erosennin", 100);
	module_addhook("dragonkilltext");
	return true;
}

function debt_uninstall(){
	return true;
}

function debt_dohook($hookname,$args){
	global $session;
	$op=httpget('op');
	require_once("modules/alignment/func.php"); 
	switch ($hookname) {
	case "dragonkilltext":
		if (is_module_active("slayerguild")) {
			if (get_module_pref("apply","slayerguild")) {
				if ($session['user']['goldinbank']<-499) {
					align(-get_module_setting("debt"));
					demeanor(-get_module_setting("chaotic"));
				}
			}
		}
		break;
	case "runevent_distress":
		$intop=(int)$op;
		if ($intop==1 || $intop==2 || $intop==3) { //good
			demeanor(5); //more lawful
		} elseif ($op=='no') {
			demeanor(-5);
		}
		break;
	case "runevent_abigail":
		if ($op=='shout') {
			demeanor(-2); //more chaotic
		}elseif ($op=='shop') {
			align(2);
		}
		break;		
	case "runevent_fairy":
		if ($op=='give') { //good
			align(5); //more good
			demeanor(3); //more lawful
		} elseif ($op=='dont') {
			align(-3);
			demeanor(-5);
		}
		break;	
	case "footer_crazyaudrey":
		if ($op=='play') {
			demeanor(-2); //more chaotic
		}
		break;		
	case "runevent_ladyerwin":
		if ($op=='kick') {
			demeanor(-4);
		}
		break;	
	case "runevent_mrblack":
		if ($op=='kick') {
			demeanor(-2);
		}
		break;	
	case "runevent_erosennin":
		if ($op=='disturb') { //lawful
			align(1); //more good
			demeanor(5); //more lawful
		} elseif ($op=='peek') {
			align(-1);
			demeanor(-1);
		}
		break;		
	}
	return $args;
}

function debt_run(){
}


?>
