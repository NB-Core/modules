<?php


function newdaylog_getmoduleinfo(){
    $info = array(
        "name"=>"Newday Log (put certain stuff into the debuglog on a player newday to track stuff down)",
        "version"=>"1.0",
        "author"=>"`2Oliver Brendel",
        "category"=>"Administrative",
        "download"=>"http://lotgd-downloads.com",
      
    );
    return $info;
}

function newdaylog_install(){
	module_addhook_priority("newday",INT_MAX);
    return true;
}

function newdaylog_uninstall(){
    return true;
}

function newdaylog_dohook($hookname,$args){
    global $session;
    switch($hookname){
		case "newday":
			require_once("lib/debuglog.php");
			$u=&$session['user'];
			restore_buff_fields();
			$message=sprintf(
				"%s (STR %s, DEX %s, CON %s, INT %s, WIS %s) started the newday as Level %s (%s experience) with %s DKs as Clanrank %s, with %s gold on hand and %s gold at the bank, %s gems, %s maxhp, level %s weapon, level %s armor, %s favors, mount %s, %s donationpoints unused and %s total.",
				sanitize($u['name']),
				$u['strength'],
				$u['dexterity'],
				$u['constitution'],
				$u['intelligence'],
				$u['wisdom'],
				$u['level'],
				$u['experience'],
				$u['dragonkills'],
				$u['clanrank'],
				$u['gold'],
				$u['goldinbank'],
				$u['gems'],
				$u['maxhitpoints'],
				$u['weapondmg'],
				$u['armordef'],
				$u['deathpower'],
				$u['hashorse'],
				$u['donation']-$u['donationspent'],
				$u['donation']
			);
			debuglog($message,$session['user']['acctid']);
			calculate_buff_fields();
		break;
	}
    return $args;
}

function newdaylog_run () {
}

?>
