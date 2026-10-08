<?php
// addnews ready
// mail ready
// translator ready
require_once("lib/villagenav.php");
require_once("lib/commentary.php");

function rp_lastcomment_getmoduleinfo(){
	$info = array(
		"name"=>"RP last comment when/in",
		"author"=>"Oliver Brendel",
		"category"=>"RP Modules",
		"version"=>"1.0",
		"download"=>"",
		"settings"=>array(
			"RP Last Comments Settings,title",
		),
		"prefs"=>array(
			"RP Last Comments Preferences,title",
			"talks"=>"Current number of times user has spoken,int|0",
		),
	);
	return $info;
}

function rp_lastcomment_install(){
	if (!is_module_installed("cities")) {
		output("`\$This module requires the multiple villages mod to be installed before it is used.`0");
		return false;
	}
	module_addhook("footer-village");
	return true;
}

function rp_lastcomment_uninstall(){
	debug("Uninstalling module.");
	return true;
}

function rp_lastcomment_sort($a,$b) {
	if (((int)$a['time'])>=((int)$b['time']))
		return 1;
		else
		return 0;	
}

function rp_lastcomment_dohook($hookname, $args) {
	global $session;

	switch($hookname){
	case "footer-village":
		if ($session['user']['specialinc']!='') break; //not in an event
		$vloc=rp_lastcomment_villagelist();
		$day = 60*60*24;
		$hour = 60*60;
		$min = 60;
		output("`n`\$Last RP village comments:`n");
		$new_out = array();
		foreach($vloc as $loc=>$val) {
			//debug($loc." - ".$session['user']['location']." -- ".$val);
			$sql = "SELECT b.*, a.name from ".db_prefix('commentary')." as b left join ".db_prefix('accounts')." as a on b.author=a.acctid WHERE section='$val' ORDER BY b.commentid DESC LIMIT 1";
			$result = db_query($sql); //we may cache in the future, but now... it will be fast enough, leave it to the server.
			$row = db_fetch_assoc($result);
			if (db_num_rows($result)==0) continue;
			$newout[] = array(
				"village"=>$loc,
				"row"=>$row,
				"time"=>strtotime("now") - strtotime($row['postdate']),
				);	
		}
		usort($newout,"rp_lastcomment_sort");
		foreach ($newout as $set) {
			$row = $set['row'];
			$village_name = $set['village'];
			$timediff = $set['time'];
			$out = '';
			if ($timediff>=$day) {
				$c_day = floor($timediff/$day);
				if ($c_day>1) {
					$out.=$c_day.translate_inline(" days ");
				} else {
					$out.=$c_day.translate_inline(" day ");
				}
				$timediff -= $day*$c_day;
			}
			if ($timediff>=$hour) {
				$c_hour = floor($timediff/$hour);
				if ($c_hour>1) {
					$out.=$c_hour.translate_inline(" hours ");
				} else {
					$out.=$c_hour.translate_inline(" hour ");
				}
				$timediff -= $hour*$c_hour;
			}
			if ($timediff>=$min) {
				$c_min = floor($timediff/$min);
				if ($c_min>1) {
					$out.=$c_min.translate_inline(" minutes ");
				} else {
					$out.=$c_min.translate_inline(" minute ");
				}
				$timediff -= $min*$c_min;
			}
			if ($timediff>0) {
				if ($timediff>1) {
					$out.=$timediff.translate_inline(" seconds ");
				} else {
					$out.=$timediff.translate_inline(" second ");
				}
			}
			if (!isset($row['name']) || $row['name'] == '') $row['name'] = translate_inline('A lost soul');
			if ($row['author'] == 0) {
				output("`2A comment was posted in `\$%s`4 %s`2 ago.`n",$village_name,$out);
			}
			else {
				output("`2%s`2 posted a comment in `\$%s`4 %s`2 ago.`n",$row['name'],$village_name,$out);
			}		
//			debug($loc."-->".$out." ago");
//			debug($sql);
		}
		break;
	}
	return $args;
}

function rp_lastcomment_villagelist() {
		$vloc = array();
		$vname = getsetting("villagename", LOCATION_FIELDS);
		$vloc = modulehook("validlocation", $vloc);
			// this is a different modulehook call because
			// there is more than one "validlocation" modulehook
		$vloc = modulehook("scrylocation", $vloc);
		$vloc[$vname] = "village";
	return $vloc;
}
