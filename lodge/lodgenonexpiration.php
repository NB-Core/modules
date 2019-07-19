<?php

function lodgenonexpiration_getmoduleinfo(){
	$info = array(
			"name"=>"Buy non-expiration account",
			"version"=>"1.0",
			"author"=>"Oliver B.",
			"category"=>"Lodge",
			"download"=>"",
			"vertxtloc"=>"",
			"settings"=>array(
				"cost"=>"How much does this cost in points?,int|50",
			),
			"prefs"=>array(
				"Healer Discount User Preferences,title",
				"bought"=>"Has the user bought this?,int|0",
			),
		);
	return $info;
}

function lodgenonexpiration_install(){
	module_addhook("lodge");
	module_addhook("motd-link");
	module_addhook("pointsdesc");
	module_addhook("newday"); // yeah, I know
	return true;
}

function lodgenonexpiration_uninstall(){
	return true;
}

function lodgenonexpiration_dohook($hookname,$args){
	global $session;
	$cost = get_module_setting("cost");
	$bought = get_module_pref("bought");
	
	switch($hookname){
		case "newday":
			if ($bought==1) {
				//check
				debuglog("trying to set superuser from ".$session['user']['superuser']." to ".SU_NEVER_EXPIRE);
				$session['user']['superuser'] = (int)$session['user']['superuser'] | SU_NEVER_EXPIRE;
				$sql = "UPDATE accounts SET superuser = ".((int)$session['user']['superuser'])." WHERE acctid=".$session['user']['acctid'];
				db_query($sql);
			}
			break;
		case "lodge":
			addnav("Permanency!");
			if ($bought!=1)
				addnav(array("Account Non-Expiration(%s points)",$cost),"runmodule.php?module=lodgenonexpiration&op=enter");
				else
				addnav(array("Account Non-Expiration(already bought)"),"");
		break;
		case "pointsdesc":
			$args['count']++;
			$format = $args['format'];
			$str = translate("You can have your account made non-expiring for %s points. This does not mean mail etc. will not expire! But your account along with name and gear will remain as long as this site exists.");
			$str = sprintf($str, $cost);
			output($format, $str, true);
		break;
		case "motd-link":
			if ($bought==1) {
				$args['link']="<h3 style='margin:0; color:#FFC700' title='Your char will never expire!'>Eternal Ninja</h3>".$args['link'];
			}
			break;
	}
	
	return $args;
}

function lodgenonexpiration_run(){
	global $session;
	$op = httpget("op");
	$cost = get_module_setting("cost");
	$bought = get_module_pref("bought");
	$pointsavailable = $session['user']['donation'] - $session['user']['donationspent'];
	addnav("Navigation");
	page_header("Mission HQ");
	if ($op=="enter"){
		addnav("L?Return to the HQ","lodge.php");
		output("`7Upon entering, you notice the sign: 'Want to make your name live forever?' which points you to a small desk with a cute young female nin behind.`n`n");
		output("\"`&Well hello there! For the cheap price of %s points I can give your account immortality! (fineprint: It will not expire naturally. Neither will dwellings. Mails and comments will)\"`7, she says.", $cost);
		addnav("Get the Immortal Option");
		if($pointsavailable >= $cost){
			addnav("Yes", "runmodule.php?module=lodgenonexpiration&op=confirm");
		} else {
			addnav("Yes (not enough points)", "");
		}
		addnav("No", "lodge.php");
	}elseif ($op=="confirm"){
		addnav("L?Return to the Lodge","lodge.php");
		$pointsavailable = $session['user']['donation'] - $session['user']['donationspent'];
		set_module_pref("bought", 1);
		$session['user']['donationspent'] += $cost;
		output("`7The young nin rummages through a pile of papers and hand you a certificate.`n`n");
		output("\"`&Keep this, it will make your name and belongings like houses immortal - you won't have to worry about months away now!`7\", she says.`n`n");
		output("`7You feel a bit better now, knowing you won't have to login to keep your name in this world anymore.");
		debuglog("Bought Immortality");
		$session['user']['superuser'] = $session['user']['superuser'] | SU_NEVER_EXPIRE;
		//you need to set this here by sql, else it will not be saved (security feature)
		$sql = "UPDATE accounts SET superuser = ".((int)$session['user']['superuser'])." WHERE acctid=".$session['user']['acctid'];
		db_query($sql);
	}
	page_footer();
}
?>	
