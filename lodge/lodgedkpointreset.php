<?php

function lodgedkpointreset_getmoduleinfo(){
	$info = array(
		"name"=>"Lodge DK Point Reset",
		"version"=>"1.0",
		"author"=>"`2Oliver Brendel",
		"category"=>"Lodge",
		"download"=>"",
		"settings"=>array(
			"Lodge Reset Settings,title",
			"pointscost"=>"Costs how many DP for one rest,range,0,1000,50|500"
		),
		"prefs"=>array(
			"Lodge Reset Prefs,title",
			"resets"=>"How often has the user used it?,int",
		),
	);
	return $info;
}

function lodgedkpointreset_install(){
	module_addhook("lodge");
//	module_addhook("pointsdesc");
	return true;
}

function lodgedkpointreset_uninstall(){
	return true;
}

function lodgedkpointreset_dohook($hookname,$args){
	global $session;
	$user = &$session['user'];
	$cost=((int)get_module_setting('pointscost'))*((int)get_module_pref('resets')+1);
//	$cost=0;
	switch ($hookname) {
		case "pointsdesc":
			$args['count']++;
			$format = $args['format'];
			$str = translate("If you want to reset your dragonkill points for %s points");
			$str = sprintf($str, $cost);
			output($format, $str, true);
			break;
		case "lodge":
			$available=$user['donation']-$user['donationspent'];
			addnav("Resets");
			if ($cost<=$available) {
				addnav(array("DK point reset (%s points)", $cost),"runmodule.php?module=lodgedkpointreset&op=enter");
			} else {
				addnav(array("DK point reset (%s points)", $cost),"");

			}
			break;
	}
	return $args;
}

function lodgedkpointreset_run() {
	require_once("lib/sanitize.php");
	global $session;
	$op = httpget("op");
	$cost=((int)get_module_setting('pointscost'))*((int)get_module_pref('resets')+1);
//	$cost=0;
	addnav("Navigation");
	page_header("Hunter's Lodge");
	switch ($op) {
		case "enter":
/*			$end=strtotime("2008-12-5 00:00:00");
			$left=$end-strtotime("now");
			$hours=floor($left/3600);
			$minutes=round(($left-($hours*3600))/60,2);
			if ($left<0) {
				output("`\$Sorry, but this offer has expired!");
				addnav("Navigation");
				addnav("Back to the Lodge","lodge.php");
				page_footer();
			}
*/			output("`7J. C. Petersen turns to you. \"`&You can reset all your spent dragonkill points for only %s points,`7\" he says.", $cost);
			$dkpoints=$session['user']['dragonpoints'];
			if ($dkpoints=="" || $dkpoints==array()) {
				output("\"`&You think this is funny? Wanting to reset something but having nothing to reset! `\$Get out!`7\"`n");
				addnav("Navigation");
				addnav("Back to the Lodge","lodge.php");
				page_footer();
			}
			output("`n`nCurrently you have the following distribution:`n`n");
			$distribution=array_count_values($dkpoints);
			$rec=array("at"=>"Attack","de"=>"Defense","str"=>"Strength","int"=>"Intelligence","wis"=>"Wisdom","dex"=>"Dexterity","ff"=>"Forest Fights","con"=>"Constitution","hp"=>"Hitpoints +5");
			rawoutput("<table style='width: 400px;'>");
			foreach ($distribution as $key=>$val) {
				rawoutput("<tr><td>");
				output_notl("`\$".$rec[$key]);
				rawoutput("</td><td>");
				output_notl("`v".(int)$val);
				rawoutput("</td></tr>");
			}
			rawoutput("</table><br/>");
//			output("`n`vTime left for this offer: %s hours and %s minutes`n`n",$hours,$minutes);
			
			output("\"`\$Do you really want to reset?`nThis cannot be undone after you click \"`#`bYes`b`\$\"!`7\"`n`n");
			output("`7`bNote: You will be able to respend your points on the next newday - so only reset when you have nothing more to do this day - else you will be pretty weak possibly.`b");
			addnav("Confirm");
			addnav("Yes", "runmodule.php?module=lodgedkpointreset&op=confirm");
			addnav("No", "lodge.php");
			break;
		case "confirm":
			increment_module_pref("resets",1);
			$session['user']['donationspent'] += $cost;
			debuglog(sprintf("Reset of dk points for %s donation points done!",(int)$cost));
			addnav("L?Return to the Lodge","lodge.php");
			require_once("lib/serverfunctions.class.php");
			ServerFunctions::resetAllDragonkillPoints(array($session['user']['acctid']));
			output("`7\"`&Alright, you're all done ... have a lot of fun!`7\" says J.C. Peterson.");
			output("`n`n`7`bNote: The changes on your stats will show on the next page you hit.`b");
			break;
	}
	page_footer();
}
?>
