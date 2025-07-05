<?php

function xmasdiscount_getmoduleinfo(){
	$info = array(
			"name"=>"Xmas Discount on mounts (timelocked)",
			"version"=>"1.0",
			"author"=>"Oliver Brendel",
			"category"=>"Holidays|Christmas",
			"download"=>"",
			"vertxtloc"=>"",
			"settings"=>array(
				"Christmas Holiday Settings,title",
				"discount_gold"=>"Discount of gold cost in percent?,floatrange,1,32,1|25",
				"discount_gems"=>"Discount of gem cost in percent?,floatrange,1,32|25",
				"Note: Core buyback is at 66% - no discount greater than 34%,note",
				"start"=>"Activation start date (mm-dd)|12-24",
				"end"=>"Activation end date (mm-dd)|12-27",
			),
			"prefs"=>array(
			),
			"requires"=>array(
				"datemanager"=>"1.0|By Oliver Brendel",
			),
		);
	return $info;
}

function xmasdiscount_install(){
	module_addhook("mount-modifycosts");
	return true;
}

function xmasdiscount_uninstall(){
	return true;
}

function xmasdiscount_dohook($hookname,$args){
	global $session;

	// Check if the date is in the range
	require_once("modules/datemanager.php");
	$start = get_module_setting("start");
	$end = get_module_setting("end");
	$check=datemanager_datecheck($start,$end,0);

	//check for activation date
	if ($check!=1) return $args;

	$discount_gems = get_module_setting("discount_gems");
	$discount_gems = (1 - ($discount_gems/100));
	$discount_gold = get_module_setting("discount_gold");
	$discount_gold = (1 - ($discount_gold/100));
	
	switch($hookname){
		case "mount-modifycosts":
			output("`\$M`2erry `\$X`2-mas ... you get a discount of %s %% for gold and %s %% for gem cost!!`n`n",(1-$discount_gold)*100,(1-$discount_gems)*100);
			$args['mountcostgold']=max(0,round($args['mountcostgold']*$discount_gold));
			$args['mountcostgems']=max(0,round($args['mountcostgems']*$discount_gems));
		break;
	}
	
	return $args;
}

function xmasdiscount_run(){
}
?>	
