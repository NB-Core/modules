<?php
/******************************************************
Name: Town Fountain
Author: Eth - ethstavern(at)gmail(dot)com 
Version: 1.1
Release Date: 02-19-2005
About: Just a small fountain for a town/village. 
	   Toss a coin, chat with others, or have a splash.
Translation compatible.
*******************************************************/
require_once("lib/http.php");
require_once("lib/villagenav.php");
require_once("lib/commentary.php");
function townfountain_getmoduleinfo(){
	$info = array(
		"name"=>"Town Fountain",
		"version"=>"1.1",
		"author"=>"Eth",
		"category"=>"Village",
		"download"=>"http://dragonprime.net/users/Eth/townfountain.zip",
		"vertxtloc"=>"http://dragonprime.net/users/Eth/",
		"settings"=>array(
		"Fountain Settings,title",
		"fountainloc"=>"Where does the fountain appear?,location|".getsetting("villagename", LOCATION_FIELDS),
		"Foundtain Odor Settings,title",	
		"You need to have the bathhouse/odor module installed and active for this to work.,note",
		"cleanup"=>"How many points removed from odor for jumping in fountain?,int|3"
		),
		"prefs"=>array(
			"Fountain Preferences,title",
			"tossedcoin"=>"Tossed Coin Today?,bool|0",
			"splashedaround"=>"Played in Fountain Today?,bool|0",					
		)
	);
	return $info;
}
function townfountain_install(){
	module_addhook("village-desc");	
	module_addhook("newday");
	module_addhook("moderate");		
	module_addhook("changesetting");
	return true;
}
function townfountain_uninstall(){
	return true;
}
function townfountain_dohook($hookname,$args){
	global $session;
	switch($hookname){	
	case "changesetting":
	if ($args['setting'] == "villagename") {
		if ($args['old'] == get_module_setting("fountainloc")) {
			set_module_setting("fountainloc", $args['new']);
		}
	}	
	break;
	case "moderate":
	$args['townfountain'] = translate_inline("Village Fountain");
	break;
	case "newday":
	set_module_pref("tossedcoin",0);
	set_module_pref("splashedaround",0);
	break;	
   	case "village-desc":
   		if ($session['user']['location'] == get_module_setting("fountainloc")) {	
			$loc = get_module_setting("fountainloc");			
			output("`n`3You happen to see a fountain in the center of town.");
   			output(" `3Would you like to <a href=\"runmodule.php?module=townfountain\">[visit it]</a>?`n",true);
   			addnav("", "runmodule.php?module=townfountain");	
		}   	
	break;
	}
	return $args;
}
function townfountain_run(){
	global $session;
	$loc = get_module_setting("fountainloc");	
	page_header("$loc Fountain");
	$op = httpget("op");
	$from = "runmodule.php?module=townfountain&";
	$coins = get_module_setting("fountaingold");
	$tossedcoin = get_module_pref("tossedcoin");
	$splashed = get_module_pref("splashedaround");		
	if ($op==""){
		$gold = $session['user']['gold'];
		output("`2Walking over to the large, circular fountain in the center of the town, you take a moment to gaze upon it's calm, reflective waters and the ornate font within its center.`n`n");
		output("`2Near you, villagers are gathered around the fountain engaged in conversation while small children sit along its edge, dangling their feet into it's chilled waters.`n`n");
		addnav("Options");		
		if ($gold>0 && $tossedcoin == 0) addnav("Toss Coin In",$from."op=options&what=tosscoin");
		addnav("Talk with Others", $from."op=options&what=chat");
		if ($splashed == 0) addnav("Jump In", $from."op=options&what=jumpin");
		villagenav();
				
	}else if ($op=="options"){
		switch(httpget('what')){
			case "tosscoin":
			$session['user']['gold']--;
			set_module_pref("tossedcoin",1);
			addnav("Go Back", "runmodule.php?module=townfountain");
			output("`2Pulling a coin from your pouch, you utter a small wish and toss it in.`n`n");
			//this is cheap, I know...
			$cantoss = 1;				
				if ($cantoss == 1){
					switch(e_rand(1,10)){
						case 1: case 2:
						output("`2The coin sinks to the bottom and you wonder if your wish for a safe journey will be granted.`n`n");
						break;
						case 3: case 4:
						output("`2The coin sinks to the bottom and glimmers in the sun.");
						output(" `2You hope your wish for a good adventure will soon come true.`n`n");
						break;
						case 5: 
						output("`2Before your coin can reach the water, a little hooligan snatches it up and runs away laughing!`n`n");
						break;
						case 6:
						output("`2As your coin hits the bottom, your mood improves over the prospect of your wish and you feel a bit more energetic.`n`n");
						output(" `#You gain a forest fight!`n`n");
						$session['user']['turns']++;
						break;
						case 7:						
						output("`2As your coin floats to the bottom, you catch a glimmer of something smaller in the waters.");
						output(" `2Bending over, ever so slightly, you see a gem resting on the bottom!`n`n");
						output(" `2Casually, you bend down as to take a sip of the waters and snatch it up.");
						$session['user']['gems']++;						
						break;
						case 8:
						output("`2Moments after your coin sinks to the bottom, a bandy-legged old man wades into the waters and steals it!`n`n");
						break;
						case 9: case 10:
						require_once("lib/partner.php");
						$lover = get_partner(TRUE);
						output("`2The coin hits the water and you wonder if `5%s `2will be desperately in love with you, as you had wished for.`n`n", $lover);
						break;
					}
				}
			break;
			case "chat":
			addcommentary();
			output("`2You decide to take a moment to talk with others gathered around the fountain.`n`n");
			viewcommentary("townfountain","Speak Quietly",15,"says");
			addnav("Go Back", "runmodule.php?module=townfountain");	
			break;
			case "jumpin":
			$cleanup = get_module_setting("cleanup");
			//just a little fun...
			output("`2Feeling particularly giddy, you decide to cast off your boots and have a splash in the fountain.");
			output(" `2After a few moments of walking around the cool waters and kicking coins about, you decide it would be best to get out before anyone takes notice.`n`n");
			output("`2Spotted or not, your spirits have been raised by the brief respite.`n`n");
			$session['user']['spirits']=2;
			set_module_pref("splashedaround",1);
			if (is_module_active("odor")) {
            output("`3You also feel slightly cleaner now as well.`n`n");
			set_module_pref("odor", (get_module_pref("odor","odor") - $cleanup),"odor");
        	}
			if (e_rand(1,5)==1){
				//yup, someone saw you
				addnews("%s `2was seen splashing around in the town fountain!",$session['user']['name']);
			}
			addnav("Go Back", "runmodule.php?module=townfountain");
			break;
		}
	}	
	page_footer();
}
?>
