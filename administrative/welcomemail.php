<?php
function welcomemail_getmoduleinfo(){
	$info = array(
		"name"=>"Welcome Mail",
		"version"=>"1.0",
		"author"=>"`2Oliver Brendel",
		"category"=>"Mail",
		"download"=>"",
	);
	return $info;
}

function welcomemail_install(){
	module_addhook("process-create");
	return true;
}

function welcomemail_uninstall(){
	return true;
}

function welcomemail_dohook($hookname,$args){
	global $session;
	switch ($hookname) {
		case "process-create":
			$subject = translate_mail(array("Welcome To Shinobi Legends"),0);
			$text=translate_mail(array(
				"`\$Welcome to Shinobi Legends.`n`n`jafter the first few clicks, and the inital welcome message, here are a few things you might want to know:`n`n`4*Petition if you have game errors, weird codes or anything else.`n*Call for Moderators (button) if you find content offensive`n`j*Visit the wiki to grab more information: http://wiki.shinobilegends.com`n`n`2Enjoy playing!`n`kThe Staff of Shinobi Legends"
			),0);
			require_once("lib/systemmail.php");
			systemmail($args['acctid'],$subject,$text);
				
		break;
	}
	return $args;
}

function welcomemail_run(){
}


?>
