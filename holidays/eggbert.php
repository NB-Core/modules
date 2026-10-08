<?php

//template stripped down

function eggbert_getmoduleinfo(){
	$info = array(
		"name"=>"Eggbert",
		"author"=>"Oliver Brendel",
		"version"=>"1.0",
		"category"=>"Holidays|Easter Holiday",
		"download"=>"",
		"description"=>"",
		"settings"=>array(
			"Module Settings,title",
			"basechance"=>"Base chance,range,1,50,1|10",
			"location"=>"Where is Eggbert located,location|".getsetting("villagename",LOCATION_FIELDS),
			"name"=>"Vendor Name,text|`lEggbert",
		),
		"prefs"=>array(
			"Module prefs,title",
			"visited"=>"Been here what year,int|2011",
		),
	);
	return $info;
}
function eggbert_install(){
	module_addhook("village");
	return true;
}
function eggbert_uninstall(){
	return true;
}
function eggbert_dohook($hookname,$args){
	global $session;
	//if ($session['user']['acctid']!=7) return $args;
	switch ($hookname){
		case "village":
			$diff=datedifference_events("04-27",false);
			$name = get_module_setting('name');
			if ($diff>0 && $diff<28) {
				$timeleft = strtotime('27 April')-strtotime('now');
				$hours = floor($timeleft/3600);
				$minutes = ceil($timeleft/60-$hours*60);
				if ($hours>1) $hourname="hours";
					else $hourname="hour";
				if ($minutes>1) $minutename="minutes";
					else $minutename="minute";
				if ($session['user']['location'] == get_module_setting("location")){
					if (isset($args['schema']['fightnav'])) tlschema($args['schema']['fightnav']);
					addnav($args['fightnav']);
					if (isset($args['schema']['fightnav'])) tlschema();
					addnav(array("%s`0's Eggs",get_module_setting("name")),"runmodule.php?module=eggbert&op=enter");
					output("`c`\$%s`\$ is in town for %s %s %s %s!`c",$name,$hours,$hourname,$minutes,$minutename);
				} else {
					output("`c`\$%s`\$ is in %s for %s %s %s %s!`c",$name, get_module_setting("location"), $hours,$hourname,$minutes,$minutename);
				}
			} elseif ($diff>=28 && $diff<35) {
				output("`c%s`\$ will soon be in %s for Easter...`c",$name,get_module_setting("location"));
			}
			break;
	}
	return $args;
}
function eggbert_run(){
	global $session;
	$op = httpget('op');
	
	$name = get_module_setting('name');
	addnav("Navigation");
	villagenav();
	addnav("Actions");
	page_header("%s's Eggs",sanitize($name));
	
	$dp=200;
	$gems=30;
	
	$visited = get_module_pref('visited');
	$itemid = (int) get_module_setting("eggitem", "easteregg");

	require_once("modules/inventory/lib/itemhandler.php");
	$items = get_inventory_item($itemid);

	switch ($op){
	
		case "reallysell":
			output("`lYou hand over the egg.`n`n");
			switch(httpget('desired')) {
				case "dp":
					output("You receive %s donationpoints!",$dp);
					debuglog("granted $dp donationpoints for the easter egg");
					$session['user']['donation']+=$dp;
					break;
				case "gems":
					output("You receive %s gems!",$gems);
					debuglog("got $gems gems from easter egg");
					$session['user']['gems']+=$gems;
					break;
			}
			set_module_pref('visited',date('Y'));
			remove_item_by_id($itemid);
			break;
		case "sell":
			output("`lHe wizzles closer, \"`QAh, you want to sell this one? Nice, very nice. I can offer you the following compensations. But I only take 1 egg per customer, so choose wisely!`l\"");
			addnav(array("Sell the egg for %s donationpoints.",$dp),"runmodule.php?module=eggbert&op=reallysell&desired=dp");
			addnav(array("Sell the egg for %s gems.",$gems),"runmodule.php?module=eggbert&op=reallysell&desired=gems");
			
			break;
		case "enter":
				output("`lYou enter a nice small store that has set up for a couple of days in the year.`n`n");
				output("As you look around, a chubby round ... Chicken-Guy?... walks over to you, and extends a hand.");
				output("\"`QMy name is %s`Q, may I be of any service to you`l?\"",$name);
				output("You notice this shop has quite a collection of eggs... most coloured brightly.");
				if (isset($items['quantity']) && $items['quantity']>0) {
					output("`n`n`\$It seems you have something of interest to him.");
					if ($visited != date('Y')) {
						addnav("Sell your egg","runmodule.php?module=eggbert&op=sell");
					} else {
						output("`n`n`\$Sadly you have already been here this year...");
					}
				} else	{
					output("`n`n`\$It seems you have nothing of interest to him.");
				}
			break;
			
			
		}
	page_footer();
}
