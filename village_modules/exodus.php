<?php
/*
   Exodus Specialty
   File Name exodus.php
   Author: Chris Vorndran (Sichae)
   Date:	10/5/2004

   Exodus' is a module to basically tie Foilwench to the city.
   Kinda like Melagos, it is regged for a certain amount per DK.

 */
require_once("lib/villagenav.php");
require_once("lib/http.php");
function exodus_getmoduleinfo(){
	$info = array(
			"name"=>"Exodus Specialty",
			"author"=>"Chris Vorndran",
			"version"=>"1.3",
			"category"=>"Village",
			"download"=>"http://dragonprime.net/users/Sichae/exodus.zip",
			"vertxtloc"=>"http://dragonprime.net/users/Sichae/",
			"description"=>"Allows a user to spend Gems in order to gain extra use points for their specialty. X amount of times per DK, can it be used.",
			"settings"=>array(
				"dktimes"=>"Amount of times Exodus can be used per DK,int|3",
				"goldcost"=>"Cost in gold for Exodus' Services`ibased on level`i,int|100",
				"gemcost"=>"Cost in gems for Exodus' Services,int|3",
				"exodusloc"=>"Where does Exodus appear,location|".getsetting("villagename", LOCATION_FIELDS)
				),
			"prefs"=>array(
				"times"=>"Times a player has used Exodus,int|0",
				)
		     );
	return $info;
}
function exodus_install(){
	module_addhook("changesetting");
	module_addhook("village");
	module_addhook("dragonkilltext");
	return true;
}
function exodus_uninstall(){
	return true;
}
function exodus_dohook($hookname,$args){
	global $session;
	$times=get_module_pref("times");
	switch($hookname){
		case "changesetting":
			if ($args['setting'] == "villagename") {
				if ($args['old'] == get_module_setting("exodusloc")) {
					set_module_setting("exodusloc", $args['new']);
				}
			}
			break;
		case "village":
			if ($session['user']['location'] == get_module_setting("exodusloc")) {
				tlschema($args['schemas']['marketnav']);
				addnav($args['marketnav']);
				tlschema();
				addnav("Exodus Specialty","runmodule.php?module=exodus&op=enter");
			}
			break;
		case "dragonkilltext":
			output("You wake up in a small clearing. With your Armor and Weapon gone, as is your knowledge.");
			output(" Exodus appears before you and destroys your account in his ledger.");
			set_module_pref("times",0);
			break;
	}
	return $args;
}
function exodus_run(){
	global $session;

	$specialty = modulehook("specialtynames");
	$color = modulehook("specialtycolor");
	$op = httpget("op");

	if ($session['user']['specialty']=='Undecided') {
		$spec = '';
		$op = 'nospec';
	} else {
		if (isset($specialty[$session['user']['specialty']]))
			$spec = $specialty[$session['user']['specialty']];
		else
			$spec = '';
	}
	if (isset($color[$session['user']['specialty']]))
		$ccode = $color[$session['user']['specialty']];
	else
		$ccode = '`x';

	$times = get_module_pref("times");
	$dktimes = get_module_setting("dktimes");
	$goldcost = (get_module_setting("goldcost")*$session['user']['level']);
	$gemcost = get_module_setting("gemcost");
	$theygold = $session['user']['gold'];
	$theygems = $session['user']['gems'];

	page_header("Exodus' Specialty");

	if($op=="enter" && $times<$dktimes){
		output("`7You walk to the edge of the village. ");
		output("You continue walking and you reach the actual edge of the village. ");
		output("You look over the cliff and see nothing, yet a large staircase rushes towards you. ");
		output("A small demon grabs your hand and walks you down the stairs. ");
		output("You reach the bottom of the pit after 3 hours, and you can see no light, except for a large cauldron. ");
		output("A Grand Creature stands before you. ");
		output("A massive beast tied to it's place with huge chains. ");
		output("It's skin is flecked with small containment charms. `n`n");
		output("The beast speaks, \"`&Many years ago, I was taken into captivity by the warriors of old. ");
		output("They locked me in this hell-hole and I haven't been freed since. ");
		output("I have been collecting money, by running a small business down here. ");
		output("I shall teach you the wisdom of ages past, for a price.`7\"`n`n");
		output("`7You still can not take your eyes off of the amazing sight. ");
		output("\"`&My name is Exodus and I shall teach you. ");
		output("My services cost `%%s gems `&and `^%s gold`&. Still wish to learn?`7\"`n`n",$gemcost,$goldcost);
		output(" `7\"`&I will teach you how to advance in %s%s `&pending the loot.`7\"`n",$ccode,$spec);
		output("`7The giant sighs, \"`&Yes, mine to teach.`7\"");
		addnav("Navigation");
		addnav("Slowly Walk Back","runmodule.php?module=exodus&op=decline");
		addnav("Actions");
		addnav("Accept the Teachings","runmodule.php?module=exodus&op=accept");
	}elseif($op=="decline"){
		output("Exodus lifts you and you are thrown from his pit. ");
		output("As you did not wish to see Exodus today, you walk back into the square for a cold pint of ale.");
		addnav("Navigation");
		villagenav();
	}elseif ($theygold<$goldcost){
		output("`7Exodus stares at you blankly.");
		output(" \"`&I may have been here for 7000 years, but I am not stupid.");
		output(" Come back when you have `^%s gold`&, then you can face me.`7\"",$goldcost);
		addnav("Navigation");
		villagenav();
	}elseif ($theygems<$gemcost){
		output("`7Exodus glares at you.");
		output(" \"`&Please do not try to fool me, come back with `%%s gems`&.\"",$gemcost);
		output(" Then I might consider it...`7\"");
		addnav("Navigation");
		villagenav();
	}elseif ($times>=$dktimes){
		output("Exodus begins to get angered at the very sight of you.");
		output("Exodus' fury begins to rattle his chains.");
		output(" \"`&Do not try to fool me, for you shall pay the dearest of prices.`7\"`n`n");
		output("Exodus tosses out a chain that binds you. ");
		output("He throws you from the depths of the hole and you slam into a tree.");
		output("\"`3Maybe I should wait until after I kill the dragon, before I go back.`7\"");
		addnav("Navigation");
		villagenav();
	}elseif ($op=='nospec'){
		output("Exodus shakes his head.");
		output(" \"`&Do not try to fool me, for you shall pay the dearest of prices. You have no specialty.`7\"`n`n");
		addnav("Navigation");
		villagenav();
	}elseif($op=="accept"){
		$special=$session['user']['specialty'];
		output("Exodus glares upon you. ");
		output("`7\"`&So, you wish to accept the Teachings?`7\" he asks in a gruff voice.`n`n");
		output("`7\"`&So be it...`7\" he bellows as you are lifted from your feet. ");
		output("Exodus bores into your soul and discovers your true strength in %s%s`0.`n`n",$ccode,$spec);
		output("`7You wake up in the middle of the square. ");
		output("Your head sears with pain, as a small light dawns upon you.`n");
		output("You have gained an extra skill point in %s%s.",$ccode,$spec);
		$session['user']['gems']-=$gemcost;
		$session['user']['gold']-=$goldcost;
		$times=get_module_pref("times")+1;
		set_module_pref("times",$times);
		debuglog("has lost $gemcost gems and $goldcost gold to exodus");
		require_once("lib/increment_specialty.php");
		increment_specialty("`^");
		addnav("Navigation");
		villagenav();
	}
	page_footer();
}
?>
