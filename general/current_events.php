<?php

function current_events_getmoduleinfo(){
	$info = array(
		"name"=>"Display current events",
		"version"=>"1.0",
		"author"=>"`2Oliver Brendel",
		"category"=>"Calendar",
		"download"=>"",
		"settings"=>array(
			"text"=>"Current Event Text,text|",
			"enddate"=>"End of event,date|2016-01-10",
		),
	);
	return $info;
}

function current_events_install(){
	module_addhook("village");
	return true;
}

function current_events_uninstall(){
	return true;
}

function current_events_dohook($hookname,$args){
	global $session;
	$htime = strtotime(get_module_setting('enddate'));
	$days = ceil(($htime - time())/86400);
	switch ($hookname) {
		case "village":
//			if ($session['user']['acctid']==7) 
				current_events_display($days);
			break;
		case "newday-intercept":
			if ($session['user']['dragonkills']==0 && !get_module_pref('displayed')) {
				page_header("Welcome new shinobis!");
				current_events_display();
				set_module_pref("displayed",1);
				require_once("modules/inventory/lib/itemhandler.php");
				add_item_by_name("`4Kun`)ai");
				add_item_by_name("`4Kun`)ai");
				add_item_by_name("`2Apple");
				add_item_by_name("`QExplosive `qTag");
				add_item_by_name("`!Shuriken");
				add_item_by_name("`!Shuriken");
				addnav("","newday.php");
				$enter=translate_inline("Click here to enter the realm!");
				rawoutput("<br><br> <center><h3><a href='newday.php'>$enter</a></center>");
				page_footer();
			}
			break;
	}
	return $args;
}

function current_events_run(){
	return true;
}

function current_events_display($days){
	$text = get_module_setting("text");
	if ($text!="" && $days >=0 ) { //display
		rawoutput("<div style='border-style: dashed; border-width:3px; border-color: #A00;margin-left:auto; margin-right:auto;text-align:center;'>");
		output("`xCurrent Events(`\$Days left: %s`x):`0`n`n",$days);
		output($text);	
		rawoutput("</div>");
	} else {
		return;
	}
	return;
}


?>
