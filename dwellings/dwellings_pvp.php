<?php
//Corrected a bug with the dwellings_get_coffers, changed it back to $row['gold/gems']

function dwellings_pvp_getmoduleinfo(){
	require("modules/dwellings_pvp/getmoduleinfo.php");
	return $info;
}
function dwellings_pvp_install(){
	require("modules/dwellings_pvp/install.php");
	return true;
}
function dwellings_pvp_uninstall(){
	return true;
}
function dwellings_pvp_dohook($hookname,$args){
	global $session;
	require("modules/dwellings_pvp/dohook/$hookname.php");
	return $args;
}
function dwellings_pvp_run(){
	global $session,$badguy,$pvptime,$pvptimeout;
	$pvptime = getsetting("pvptimeout",600);
	$pvptimeout = date("Y-m-d H:i:s",strtotime("-$pvptime seconds"));
	$last = date("Y-m-d H:i:s", strtotime("-".getsetting("LOGINTIMEOUT", 900)." sec"));
	$ac = db_prefix("accounts");
	$mu = db_prefix("module_userprefs");
	$dw = db_prefix("dwellings");
	$cl = db_prefix("clans");
	$op = httpget('op');
	$dwid = httpget('dwid');
	page_header("Dwellings PvP");
	
	if ($op!='' && $op != "fight1" && $op != "fight") require_once("modules/dwellings_pvp/run/case_$op.php");
	
	if ($op == "fight1"){
		$name = rawurldecode(httpget('name'));
		require_once("modules/dwellings/lib.php");
		if (is_numeric($name)) $name = getlogin($name);
		require_once("lib/pvpsupport.php");
		$badguy = setup_target($name);
		if ($badguy=='') {
		//	output("Sorry, an internal error happened, please notify the admin.");
			output("`n`nSorry, your target is not available right now. Try later.");
			villagenav();
			page_footer();
		}
		$session['user']['badguy'] = createstring($badguy);
		$session['user']['playerfights']--;
		$op = "fight";
	}
	if ($op == "fight"){
		$battle = true;
		global $options;
		$options['type']='pvp';
	}	
    if (isset($battle) && $battle){
        include("battle.php");
        if ($victory){
			$killedin = sprintf("%s Dwellings",$session['user']['location']);
			require_once("lib/pvpsupport.php");
			pvpvictory($badguy, $killedin,array('type'=>'pvp'));
            addnews("`4%s`3 defeated `4%s`3 while they were sleeping in their Dwelling.", $session['user']['name'], $badguy['creaturename']);
            $badguy = array();
			addnav("Leave");
			addnav("Hamlet Registry","runmodule.php?module=dwellings&op=list&ref=hamlet");
        }elseif ($defeat){
			$killedin = sprintf("%s Dwellings",$session['user']['location']);
			require_once("lib/taunt.php");
			$taunt = select_taunt_array();
			require_once("lib/pvpsupport.php");
			pvpdefeat($badguy, $killedin, $taunt,array('type'=>'pvp'));
            addnews("`4%s`3 was defeated while attacking `4%s`3 as they were sleeping in their Dwelling.`n%s", $session['user']['name'], $badguy['creaturename'], $taunt);
			output("`n`n`&You are sure that someone, sooner or later, will stumble over your corpse and return it to %s for you." , $session['user']['location']);
			addnav("Return to the Shades","shades.php");
        }else{
			$script = "runmodule.php?module=dwellings_pvp&op=fight";
			require_once("lib/fightnav.php");
	        fightnav(false,false,$script);
        }
    }
	page_footer();
}
?>
