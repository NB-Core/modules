<?php

function clanrooms_getmoduleinfo(){
	$info = array(
		"name"=>"Clan Rooms (Chatroom)",
		"author"=>"`2Oliver Brendel",
		"version"=>"1.0",
		"category"=>"Clan",
		"download"=>"",
	);
	return $info;
}

function clanrooms_install(){
	module_addhook_priority("clanhall",50);
	return true;
}
function clanrooms_uninstall(){
	return true;
}

function clanrooms_dohook($hookname,$args){
	global $session;
	switch($hookname){
	case "clanhall":
		if (!defined("CLAN_ADMINISTRATIVE")) $rank=25; //legacy support to DP
			else $rank=CLAN_ADMINISTRATIVE;
			
		if ($session['user']['clanrank']<$rank) break;
		addnav("Clan Rooms");
		addnav("Secret Conversation Room","runmodule.php?module=clanrooms&op=clanroom");
			output("`n`nBehind a secret curtain, you see the `vClan`5 Rooms you are allowed to enter.");
		break;
	}
	return $args;
}

function clanrooms_run(){
	global $session;
	$op = httpget("op");
	require_once("lib/commentary.php");
	addcommentary();	
	page_header("Secret Conversation Room");
	output("`b`i`c`vSecret Conversation Room`c`i`b`n`n");
	output("`4This is one of the secret conversation rooms the clans created in order to talk secretly among themselves.");
	output("`nMost things told here are not for public ears, and some seen here are not for public eyes either.");
	villagenav();
	addnav("Return to the clanhall","clan.php");
	addnav("Clan Rooms");
	addnav("Secret Conversation Room","runmodule.php?module=clanrooms&op=clanroom");
	commentdisplay("`n`n`@Talk about internal clan stuff.`n","clanrooms-".$session['user']['clanid'],"",30,"converses");

	page_footer();
}
?>
