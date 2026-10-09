<?php

function findfoolgold_getmoduleinfo(){
	$info = array(
		"name"=>"Find Fools Gold",
		"version"=>"1.0",
		"author"=>"`2Oliver Brendel",
		"category"=>"Holidays|Forest Specials",
	);
	return $info;
}

function findfoolgold_install(){
	module_addeventhook("forest", "\$chance=(date(\"m-d\")==\"04-01\"?100:0);return \$chance;");
	module_addeventhook("village", "\$chance=(date(\"m-d\")==\"04-01\"?20:0);return \$chance;");
	return true;
}

function findfoolgold_uninstall(){
	return true;
}

function findfoolgold_dohook($hookname,$args){
	return $args;
}

function findfoolgold_runevent($type,$link){
	global $session;
	$op=httpget('op');
	$from=$type.".php?";
	debug(date("m-d"));
	switch ($op) {
	
		case "continue":
			rawoutput("<center><h1>");
			output("Lirpa Loof! ;)");
			rawoutput("</h1></center>");
			$session['user']['specialinc'] = "";
			break;
		default:
			$session['user']['specialinc'] = "module:findfoolgold";
			$gold=number_format(e_rand(10000,INT_MAX));			
			output("`qFortune smiles on you and you find a small bag containing `^%s gold`q!", $gold);
			$gems=number_format(e_rand(100,INT_MAX/1000));			
			output("`n`nAs you wander by, there are also `% %s gems`q in a large bag!",$gems);
			addnav("Navigation");
			addnav("Continue",$from."op=continue");
	}
	
}

function findfoolgold_run(){
}
?>
