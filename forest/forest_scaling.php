<?php

function forest_scaling_getmoduleinfo(){
	$info = array(
		"name"=>"Forest Enemy Scaling",
		"version"=>"1.1",
		"author"=>"`2Oliver Brendel",
		"category"=>"Forest",
		"download"=>"",

	);
	return $info;
}

function forest_scaling_install(){
	// buffbadguy is the hook that lets a module change a new enemy's stats.
	module_addhook("buffbadguy");
	module_drophook("battle-victory");
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
				// 0.7 up to 1500 dragon kills, then down to 0.5 at 1600 and above.
				if ($dks>1500) $factor=0.5+(max(0,1600-$dks)*0.002);
					else $factor=0.7;
				$args['creatureattack']=(int)round($args['creatureattack']*$factor);
				$args['creaturedefense']=(int)round($args['creaturedefense']*$factor);
				$args['creaturehealth']=(int)round($args['creaturehealth']*$factor);
				debug("Enemy weakened for high dragon kills by factor $factor");
			}
			break;
	}
	return $args;
}

function forest_scaling_run(){
	return true;
}

?>
