<?php

function chipmunks_getmoduleinfo(){
	$info = array(
		"name"=>"Chipmunks",
		"version"=>"1.0",
		"author"=>"`2Oliver Brendel",
		"category"=>"Forest Specials",
		"prefs"=>
			array(
			"goldstolen"=>"Gold caught from that user,int",
			),
	);
	return $info;
}

function chipmunks_install(){
	module_addeventhook("forest", "return 100;");
	module_addeventhook("village", "return 100;");
	return true;
}

function chipmunks_uninstall(){
	return true;
}

function chipmunks_dohook($hookname,$args){
	return $args;
}

function chipmunks_runevent($type,$link){
	global $session;
	$op=httpget('op');
	$from=$type.".php?";
	switch ($op) {
	
		case "continue":
			rawoutput("<center><h1>");
			output("Lirpa Loof! ;)");
			rawoutput("</h1></center>");
			$session['user']['specialinc'] = "";
			break;
		default:
			$session['user']['specialinc'] = "module:chipmunks";
			if ($session['user']['gold']>0) {
				$gold=number_format(min(e_rand(94,104),e_rand(1,$session['user']['gold'])),0);			
				output("`qYou suddenly feel a pull and before you realize a `gC`lhipmunk`q rallies out of your pocket with `^%s gold`q in its paws!`0`n`n", $gold);
				$session['user']['gold']-=$gold;
				increment_module_pref("goldstolen",$gold);
				//addnav("Navigation");
				//addnav("Continue",$from."op=continue");
			} else {
				output("`qYou suddenly feel a pull and before you realize a `gC`lhipmunk`q rallies out of your pocket! Gladly you had no cash with you!`0`n`n");
			}
			$session['user']['specialinc'] = "";
	}
	
}

function chipmunks_run(){
}
?>
