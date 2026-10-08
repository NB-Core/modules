<?php

function flawlesspause_getmoduleinfo(){
	$info = array(
		"name"=>"Flawless Enemy Pause",
		"version"=>"1.0",
		"author"=>"`2Oliver Brendel",
		"category"=>"Forest",
		"download"=>"",

	);
	return $info;
}

function flawlesspause_install(){
	module_addhook("forestfight-start");
	module_addhook("battle-victory");
	return true;
}

function flawlesspause_uninstall(){
	return true;
}

function flawlesspause_dohook($hookname,$args){
	global $session;
	switch ($hookname) {
		case "battle-victory":
			if ($args['diddamage']==0) {
				$session['done_flawless']++; //dynamic tracker, unlikely somebody will log out just after each fight.
			} else $session['done_flawless']=0;
			break;
		case "forestfight-start":
			if (isset($session['done_flawless'])) {
				if ($session['done_flawless']>=e_rand(3,5)){
					debug("Marked non-flawless");
					$args['options']['denyflawless']=translate_inline("Puh, you need some time to rest after all these fights and receive NO extra turn.");
					$session['done_flawless']=0;
				}
			} else $session['done_flawless']=0;
			// debug("T: ".$session['done_flawless']);
			break;
	}
	return $args;
}

function flawlesspause_run(){
	return true;
}

?>
