<?php

function forest_scaling_getmoduleinfo(){
	$info = array(
		"name"=>"Flawless Enemy Boost",
		"version"=>"1.0",
		"author"=>"`2Oliver Brendel",
		"category"=>"Forest",
		"download"=>"",

	);
	return $info;
}

function forest_scaling_install(){
	module_addhook("battle-victory");
	return true;
}

function forest_scaling_uninstall(){
	return true;
}

function forest_scaling_dohook($hookname,$args){
	global $session;
	$u=&$session['user'];
	$dks=$u['dragonkills'];
	switch ($hookname) {
		case "buffbadguy":
			if ($dks>999) {
				if ($dks>1500) $factor=0.5+(max(0,1600-$dks)*0.02);
					else $factor=0.7;
				$args['creatureattack']*=$factor;
				$args['creaturedefense']*=$factor;
				$args['creaturehealth']*=$factor;
				debug("Debuffed up due high dks: ".$boost);
				output_notl($creature['firstroundmessage']);
				
			}
			
			break;
	}
	return $args;
}

function forest_scaling_run(){
	return true;
}

?>
