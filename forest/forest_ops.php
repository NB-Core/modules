<?php

function forest_ops_getmoduleinfo(){
$info = array(
	"name"=>"Forest Special Ops(=call events)",
	"version"=>"1.0",
	"author"=>"`2Oliver Brendel",
	"category"=>"Forest",
	"download"=>"",
	"settings"=>array(
		"Special Ops Settings,title",
		"dailyuses"=>"How many daily uses?,int|1",
		),
	"prefs"=>array(
		"Special Ops Preferences,title",
		"used"=>"How often used?,int|0",
		),
	);
	return $info;
}

function forest_ops_install(){
	module_addhook("forest");
	module_addhook("newday");
	return true;
}

function forest_ops_uninstall(){
	return true;
}

function forest_ops_dohook($hookname, $args){
	global $session;
	$eventnumber = 5;
	switch ($hookname) {
		case "newday":
			set_module_pref("used",0);
			set_module_pref("event_today",e_rand(1,$eventnumber));
			break;
		case "forest":
			//if ($session['user']['acctid']!=9340 && $session['user']['acctid']!=7) break;
			$used=(int)get_module_pref("used");
			if ($used>=get_module_setting('dailyuses')) break;
			$left = get_module_setting('dailyuses')- $used;
			addnav(array("Special Missions (%s left)",$left));
			switch (get_module_pref('event_today')) {
				case 5:
				addnav(array("Exploration"),"runmodule.php?module=forest_ops&op=exploration");
				break;
				case 4:
				addnav(array("Lost Pets"),"runmodule.php?module=forest_ops&op=lostandfound");
				break;
				case 3:
				addnav(array("Search & Rescue"),"runmodule.php?module=forest_ops&op=sr");
				break;
				case 2:
				addnav(array("Strategy"),"runmodule.php?module=forest_ops&op=mindbomb");
				break;
				case 1:
				addnav(array("Hunt!"),"runmodule.php?module=forest_ops&op=runforrestrun");
				break;
			}
			break;
		break;
	}
	return $args;
}

function forest_ops_run(){
	global $session;
	$op=httpget('op');
	increment_module_pref("used",1);
	switch ($op) {
		case "exploration":
			$events = array("abandoncastle","goldmine","keykeeper");
			$eventid = array_rand($events,1);
			$session['user']['specialinc'] = "module:".$events[$eventid];
			redirect("forest.php");
		break;
		case "lostandfound":
			$events = array("mrblack","ladyerwin");
			$eventid = array_rand($events,1);
			$session['user']['specialinc'] = "module:".$events[$eventid];
			redirect("forest.php");
		break;
		case "sr":
			$events = array("tsunade","distress");
			$eventid = array_rand($events,1);
			$session['user']['specialinc'] = "module:".$events[$eventid];
			redirect("forest.php");
		break;
		case "mindbomb":
			$events = array("riddles");
			$eventid = array_rand($events,1);
			$session['user']['specialinc'] = "module:".$events[$eventid];
			redirect("forest.php");
		break;
		case "runforrestrun":
			$events = array("rabidwerewolf","fuujinraijin");
			$eventid = array_rand($events,1);
			$session['user']['specialinc'] = "module:".$events[$eventid];
			redirect("forest.php");
		break;
		break;
	}
}

?>
