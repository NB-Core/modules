<?php

function halleyscorner_getmoduleinfo(){
	$info = array(
		"name"=>"Halleys Corner (PvP->Travel)",
		"author"=>"`2Oliver Brendel",
		"version"=>"1.0",
		"category"=>"Village|Shops",
		"download"=>"",
		"settings"=>array(
			"Corner Settings,title",
			"owner"=>"Name of the Owner (female),text|`4Ha`1ll`gey",
			"cost"=>"Costs X PvP Fights,floatrange,0,20,1|1",
			"gain"=>"Gives X Travels,floatrange,0,20,1|1",
			),
	);
	return $info;
}

function halleyscorner_install(){
	module_addhook("village-Konohagakure");
	return true;
}
function halleyscorner_uninstall(){
	return true;
}

function halleyscorner_dohook($hookname,$args){
	global $session;
	switch($hookname){
		default:
		if ($session['user']['dragonkills']<1) break;
		tlschema($args['schemas']['marketnav']);
		addnav($args['marketnav']);
		tlschema();
		$halley=get_module_setting("owner");
		addnav(array("%s`0's Corner",$halley),"runmodule.php?module=halleyscorner");
		break;
	}
	return $args;
}

function halleyscorner_run(){
	global $session;
	$op = httpget("op");
	$halley=get_module_setting("owner");
	$cost=get_module_setting("cost");
	$gain=get_module_setting("gain");
	require_once("lib/commentary.php");
	addcommentary();
	page_header("%s' Corner",sanitize($halley));
	output("`b`i`c%s`7's `tCorner`c`i`b`n",$halley);
	villagenav();
	if ($op) addnav(array("Back to %s`0",$halley),"runmodule.php?module=halleyscorner");
	switch ($op) {
		case "drink":
			$session['user']['playerfights']-=$cost;
			increment_module_pref("traveltoday",-$gain,"cities");
			output("`3You ask %s`3 to give you a sip of her well-known root beer.`n`n",$halley);
			output("It tastes great and you feel like you could wander around the country for years!`n`n");
			output("`%You gain %s travels but lose %s pvp for today!",$gain,$cost);
			output("`n`n`3You have %s PvP left now.",$session['user']['playerfights']);
			debuglog("Exchanged $cost pvp for $gain travels module halleycorner");
			break;
		case "talk":
			commentdisplay("`n`n`@Converse with your fellow pacifists.`n","halleyscorner","",20,"chants");
			break;
		default:
		output("`3You enter %s`3's Corner, a beautiful little inn near the village square.`n`n",$halley);
		output("You are greeted by the owner %s`3, who stands behind the bar and winks towards you.",$halley);
		output(" The atmosphere is cosy as usual, only a few fellow comrades have gathered to relax a bit before they go on to travel anew.");
		output(" Most people come here to take a sip of the local drink as it has the reputation of invoking wanderlust and take away the urge to kill.");
		output("`n`nFeeling welcome, you take a seat nearby.");
		addnav("Actions");
		if ($session['user']['playerfights']>=$cost)
			addnav(array("Take a sip of %s`0's drink (%s PvP)",$halley,$cost),"runmodule.php?module=halleyscorner&op=drink");
		addnav("Converse with patrons","runmodule.php?module=halleyscorner&op=talk");
		break;
	}
	page_footer();
}
?>
