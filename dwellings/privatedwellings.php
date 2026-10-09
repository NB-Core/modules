<?php
function privatedwellings_getmoduleinfo(){
	$info = array(
		"name"=>"Dwellings made private",
		"version"=>"1.0",
		"author"=>"`2Oliver Brendel",
		"category"=>"Dwellings",
		"download"=>"",
	);
	return $info;
}

function privatedwellings_install(){
	module_addhook("blockcommentarea");			//show in the FAQ
	return true;
}

function privatedwellings_uninstall(){
	return true;
}

function privatedwellings_dohook($hookname,$args){
	global $session;
	switch($hookname){
	case "blockcommentarea":
		debug($args);
		if (strstr($args['section'],"dwellings") || strstr($args['section'],"coffers")) $args['block']="yes";
		break;
	}
	return $args;
}

function privatedwellings_run(){
}
?>
