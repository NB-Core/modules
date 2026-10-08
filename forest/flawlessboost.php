<?php

function flawlessboost_getmoduleinfo(){
	$info = array(
		"name"=>"Flawless Enemy Boost",
		"version"=>"1.0",
		"author"=>"`2Oliver Brendel",
		"category"=>"Forest",
		"download"=>"",

	);
	return $info;
}

function flawlessboost_install(){
	module_addhook("buffbadguy");
	module_addhook("battle-victory");
	return true;
}

function flawlessboost_uninstall(){
	return true;
}

function flawlessboost_dohook($hookname,$args){
	global $session;
	switch ($hookname) {
		case "battle-victory":
			if ($args['diddamage']==0) {
				$session['done_flawless']++; //dynamic tracker, unlikely somebody will log out just after each fight.
			} else $session['done_flawless']=0;
			break;
		case "buffbadguy":
			if (isset($session['done_flawless'])) {
				$factor=1.15;
				$boost=pow($factor,$session['done_flawless']);
				$args['creatureattack']*=$boost;
				$args['creaturedefense']*=$boost;
				debug("Buffed up due to flawless streak: ".$boost);
				output_notl($creature['firstroundmessage']);
				
			} else $session['done_flawless']=0;
			
			break;
	}
	return $args;
}

function flawlessboost_run(){
	return true;
}

?>
