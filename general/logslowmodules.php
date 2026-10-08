<?php

function logslowmodules_getmoduleinfo(){
	$info = array(
		"name"=>"Log Slow Modules",
		"version"=>"1.0",
		"author"=>"`2Oliver Brendel ",
		"category"=>"Images",
		"download"=>"",
		"Settings"=>array(
			"Log Slow Modules Handler,title",
			"This is only triggered if a SU_DEBUG_OUTPUT user triggers something!,note",
			),
		);
	return $info;
}

function logslowmodules_install(){
	return true;
}

function logslowmodules_uninstall(){
	return true;
}

function logslowmodules_dohook($hookname,$args){
	switch($hookname) {
		default:
			debug("Slow module: '".$args['modulename']."' took ".$args['duration']."s to complete on ".$args['date']);	
			break;
	}
	return $args;
}

