<?php
use Lotgd\Security\Csrf;

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

/**
 * Whether the request is a posted clan mail with a valid token.
 * Lotgd\Security\Csrf came with the core of September 2026; an older core gets the POST check alone.
 */
function clanmail_validpost(){
	if (!class_exists(Csrf::class)) {
		return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
	}
	return Csrf::validatePostRequest("module:clanmail");
}

/**
 * The hidden token field for this module's forms; empty on a core without Lotgd\Security\Csrf.
 */
function clanmail_tokenfield(){
	return class_exists(Csrf::class) ? Csrf::hiddenField("module:clanmail") : "";
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