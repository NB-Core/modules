<?php

function fightingzone_shades_getmoduleinfo(){
	$info = array(
		"name"=>"Fighting Zone (Shades) and Clanhall Shades",
		"author"=>"`2Oliver Brendel",
		"version"=>"1.0",
		"category"=>"Village",
		"download"=>"",
		"settings"=>array(
			"Fighting Zone Settings,title",
			"rulesurl,Address of the zone rules (empty: no rules link)|",
		),
	);
	return $info;
}

function fightingzone_shades_install(){
	module_addhook_priority("shades",50);
	return true;
}
function fightingzone_shades_uninstall(){
	return true;
}

function fightingzone_shades_dohook($hookname,$args){
	global $session;
	switch($hookname){
	case "shades":
		addnav("Battlefield");
		addnav("Undead Fighting Zone","runmodule.php?module=fightingzone_shades");
		if ($session['user']['clanrank']>0 && $session['user']['clanid']>0) {
			addnav("Places");
			addnav("Visit Clanhall","clan.php");
		}
		break;
	}
	return $args;
}

function fightingzone_shades_run(){
	global $session;
	$op = httpget("op");
	require_once("lib/commentary.php");
	addcommentary();	
	page_header("Undead Fighting Zone");
	output("`b`i`c`)Undead `\$Fighting `!Zone`c`i`b`n");
	if ($op=="") 
		$zone=translate_inline("Main");
		else
		$zone=substr($op,strpos($op,"-")+1);
	output("`b`i`c`!Zone %s`c`i`b`n`n",$zone);
	output("`2Here you are allowed to fight, use jutsus and whatever you like, as long as you can do it with words only.");
	output("`nRemember: You are ghosts of your former self here... souls that are ready to return to the living world.");
	output("`nDo not use rude language... you may taunt your enemy, but stick to normal language and don't use trash talk or nasty words. Thank you.`n`n");
	villagenav();
	$rulesurl = trim((string) get_module_setting('rulesurl'));
	if ($rulesurl !== '') {
		addnav("`bRules`b");
		addnav("Rules for the Zone",$rulesurl,false,true,"");
	}	
	addnav("Areas");
		addnav("`4Main Zone`0","runmodule.php?module=fightingzone_shades");
	// for ($i=1;$i<8;$i++) {
		// addnav(array("Undead Fighting Ground %s",$i),"runmodule.php?module=fightingzone_shades&op=ground-$i");
	// }
	addnav("Fighting Grounds");
	addnav("#1 `tFresh Meat","runmodule.php?module=fightingzone_shades&op=ground-1");
	addnav("#2 `1Vengeance","runmodule.php?module=fightingzone_shades&op=ground-2");
	addnav("#3 `\$Fire Forest","runmodule.php?module=fightingzone_shades&op=ground-3");
	addnav("#4 `#Nature Calls","runmodule.php?module=fightingzone_shades&op=ground-4");
	addnav("#5 `\$Volcano","runmodule.php?module=fightingzone_shades&op=ground-5");
	addnav("#6 `!Rain","runmodule.php?module=fightingzone_shades&op=ground-6");
	addnav("#7 `!Spooky","runmodule.php?module=fightingzone_shades&op=ground-7");
	
	if ($op=="")  {
		//output("`2This is the main area...you see several areas beside it.`n`n");
		output("`2Undead Main zone- `^Your soul floats in, perhaps seeking vengeance on those who have killed you before?  The zone has many `)skeletons `^littering the floor flesh still attached to the bleach `jwhite `^bones, bugs of all sorts crawling in and out of the bodies.");
		commentdisplay("`n`n`@Shout out your actions.`n","fightingzone_shades","Shout out",20,"shouts");
	} elseif(strstr($op,"ground")) {
		switch ($zone) {
			case 1:
				output("`2Undead zone 1- `^Your soul floats in perhaps seeking vengeance?  The zone is literally drenched in `4blood `^of the fallen shinobi in the realm.  A small `1river `^is to the side of the zone its water mixed with the heavy stench of the `4blood `^raining from the sky.");
				break;
			case 2:
				output("`2Undead Zone 2- `^Your soul floats in perhaps seeking vengeance?  All throughout the zone there are `jbones `^jutting out of the ground of all assorted sizes some very large or some as small as a hand.  They cover the entire zone and even lodged into `2trees. `^Perhaps the remains of the Kaguya clan?");
				break;
			case 3:
				output("`2Undead Zone 3- `^Your soul floats in, perhaps seeking vengeance?  This zones temperature is an almost un-bearable temperature.  Several large balls of `\$fire `^rain down from the `)cloudy`^ sky.  Many skeletons hang down from the trees some of them missing skulls or legs.");
				break;
			case 4:
				output("`2Undead Zone 4- `^Your soul floats in, perhaps seeking vengeance?  This zone is being torn apart by a large tornado tearing down trees and generally `4destroying `^the zone totally.  The earth is dug up revealing dark brown earth with many different kinds of `~bugs `^squirming around in it.");
				break;
			case 5:
				output("`2Undead Zone 5- `^your soul floats in, perhaps seeking vengeance?  The zone is located by a nearby active `4volcano,`^ large streams of molten `\$lava `^flow through the zone creating deep rifts in the earth and many dead trees seem to be thriving by the `\$lava `^somehow close but never being destroyed by the lava.");
				break;
			case 6:
				output("`2Undead Zone 6- `^Your soul floats in, perhaps seeking vengeance?  The zone is being rained upon by a heavy `1rain `^that seems to `\$burn `^to the touch, then upon further inspection of a rock you notice the rain is literally eating away at the rock slowly.  The rain seems to be going on and off, leaving just enough of you so you won't `~rot `^away.");
				break;
			case 7:
				output("`2Undead Zone 7- `^Your soul floats in, perhaps seeking vengeance?  The zone gives off an `)ominous `^aura.  There are large rifts in the ground which occasionally spew out a red hot `\$flame `^randomly and many half dead souls are chained to trees and rocks, pulling at their restraints.");
				break;
							
		
		}
		commentdisplay("`n`n`@Shout out your actions.`n","fight_shades-$zone","Shout out",20,"shouts");
	}
	page_footer();
}
?>
