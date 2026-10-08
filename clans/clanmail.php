<?php
function clanmail_getmoduleinfo(){
	$info = array(
		"name"=>"Clan Mail",
		"version"=>"1.0",
		"author"=>"`2Oliver Brendel",
		"category"=>"Clan",
		"download"=>"",
		"settings"=> array(
			"Clan Mail Settings,title",
			"mailcostgold"=>"Cost Multiplier to Mailcost (Cost=Members*Multiplier),int|10",
			),
	);
	return $info;
}

function clanmail_install(){
	module_addhook("clanhall");
	return true;
}

function clanmail_uninstall(){
	return true;
}

function clanmail_dohook($hookname,$args){
	global $session;
	switch ($hookname) {
	case "clanhall":
		if ($session['user']['clanrank']>=CLAN_LEADER && $session['user']['clanid']!=0) {
			addnav("Clan Mail");
			addnav("Clan Mail Access","runmodule.php?module=clanmail");
		}
		break;
	}
	return $args;
}

function clanmail_run(){
	global $session;
	// Same condition as the clan hall link.
	if ($session['user']['clanrank']<CLAN_LEADER || $session['user']['clanid']==0) {
		redirect("clan.php");
	}
	$op=httpget('op');
	page_header ('Clan Mail');
	addnav("Navigation");
	addnav("Return to the clan hall","clan.php");
	addnav("Actions");
	require("modules/clanmail/case_run.php");
	page_footer();
	
}


?>