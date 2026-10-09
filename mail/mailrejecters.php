<?php

if (!defined("OVERRIDE_FORCED_NAV")) define("OVERRIDE_FORCED_NAV",true);


function mailrejecters_getmoduleinfo(){
	$info = array(
		"name"=>"mailrejecters",
		"override_forced_nav"=>true,
		"version"=>"1.01",
		"author"=>"`2Oliver Brendel`0",
		"category"=>"Mail",
		"download"=>"",
		"settings"=>array(
			"mailrejecters - Preferences,title",
			"text"=>"Text to display at the create-form hook,textarea",
			),

		);
	return $info;
}

function mailrejecters_install(){
	module_addhook("create-form");
	return true;
}

function mailrejecters_uninstall() {

	return true;
}


function mailrejecters_dohook($hookname, $args){
	global $session;
	$ext=get_module_setting('text');
	switch ($hookname)	{
		default:
			output_notl(str_replace(chr(13),"`n",$ext));
			break;
	}
	return $args;
}

function mailrejecters_run(){

}

?>
