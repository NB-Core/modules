<?php

function clanleadersecretroom_getmoduleinfo(){
	$info = array(
		"name"=>"Clanleader Secret Room (Chatroom)",
		"author"=>"`2Oliver Brendel",
		"version"=>"1.0",
		"category"=>"Gypsy",
		"download"=>"",
	);
	return $info;
}

function clanleadersecretroom_install(){
	module_addhook_priority("gypsy",50);
	return true;
}
function clanleadersecretroom_uninstall(){
	return true;
}

function clanleadersecretroom_dohook($hookname,$args){
	global $session;
	switch($hookname){
	case "gypsy":
		if ($session['user']['clanrank']<30 || $session['user']['clanid']==0) break;
		addnav("Clanleader Rooms");
		for ($i=1;$i<3;$i++) {
			addnav(array("Secret Conversation Room #%s",$i),"runmodule.php?module=clanleadersecretroom&op=secretroom-$i");
		}
		output("`n`nBehind a secret curtain, you see the `vClanleader`5 Rooms you are allowed to enter.");
		break;
	}
	return $args;
}

function clanleadersecretroom_run(){
	global $session;
	$op = httpget("op");
	require_once("lib/commentary.php");
	addcommentary();	
	page_header("Clanleader Rooms");
	output("`b`i`c`vClanleader `gRooms`c`i`b`n");
	$secretroom=substr($op,strpos($op,"-")+1);
	output("`b`i`c`vSecret Conversation Room #%s`c`i`b`n`n",$secretroom);
	output("`4This is one of the secret conversation rooms Clanleaders created in order to talk secretly among themselves, make alliances, talk casual about all kind of things.");
	output("`nMost things told here are not for public ears, and some seen here are not for public eyes either.");
	output("`nYou gather along fellow `vClanleaders`4 and `\$Founders`4 and even higher shinobis...`n`n");
	villagenav();
	addnav("Return to the gypsy","gypsy.php");
	addnav("Clanleader Rooms");
		for ($i=1;$i<3;$i++) {
			addnav(array("Secret Conversation Room #%s",$i),"runmodule.php?module=clanleadersecretroom&op=secretroom-$i");
		}	
	if(strstr($op,"secretroom")) {
		commentdisplay("`n`n`@Converse among the clanleaders.`n","clsecretroom-$secretroom","",30,"converses");
	}
	page_footer();
}
?>
