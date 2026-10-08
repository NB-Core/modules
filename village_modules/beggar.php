<?php
/**************
Name: Old Beggar
Author: Eth - ethstavern(at)gmail(dot)com 
Version: 1.3
Rerelease Date: 01-20-2005
About: Find an old beggar in an alley way. Give him some gold or gems
       for humorous results. Nothing spectacular, really.
Bugs: None that I know of at the moment. 
Translation ready!

Version History
---------------
1.0 - Original release - Eth
1.1 - Added Basic Alignment code - Zanzaras
1.2 - New release with new option to give away all gold to beggar
1.3 - Added option to give a gem to the beggar, and some new outcomes
1.4 - Added option to give all gold to the beggar, and some new outcomes
	   Added alignment settings for giving gold/gem/all gold/nothing
	   Added a note about the alignment settings only being used if the Basic Alignment module is active
1.5 - Updates to new core version - NB
*****************/
require_once("lib/villagenav.php");
if (is_module_active("alignment")){
	require_once("./modules/alignment/func.php");
}

function beggar_getmoduleinfo(){
	$info = array(
		"name"=>"Needy Beggar",
		"version"=>"1.4",
		"author"=>"Eth",
		"category"=>"Village Specials",
		"download"=>"http://www.dragonprime.net/users/Eth/beggar.zip",
		"vertxtloc"=>"http://dragonprime.net/users/Eth/",
		"settings"=>array(	
		"Beggar - Main Settings,title",	
		"beggarchance"=>"Chance to see old beggar?,range,0,100,1|20",
		"beggarloc"=>"Where does the beggar appear?,location|".getsetting("villagename", LOCATION_FIELDS),
		"locall"=>"OR should he appear everywhere?,bool|0",
	    "Beggar - Alignment Settings,title",
        "The following settings are only used if the \"Basic Alignment\" module has been activated.,note",
        "gavebeggargold"=>"Alignment points gained for giving gold to the beggar,range,0,5,1|1",
        "gavebeggargem"=>"Alignment points gained for giving a gem to the beggar,range,0,5,1|2",
        "gavebeggarallgold"=>"Alignment points gained for giving away all gold to the beggar,range,0,5,1|3",
        "gavebeggarnothing"=>"Alignment points lost for not giving anything to the beggar,range,0,5,1|1",

		),
		"prefs"=>array(
			"beggar Preferences,title",	
			"seenbeggar"=>"Seen Beggar Today?,bool|0",		
		)
	);
	return $info;
}
function beggar_install(){
	module_addeventhook("village", "require_once(\"modules/beggar.php\"); return beggar_test();");
	module_addhook("newday");
	module_addhook("changesetting");
	return true;
}
function beggar_uninstall(){
	return true;
}
function beggar_test(){
	global $session;	
	$chance = get_module_setting("beggarchance","beggar");
	if (get_module_setting("locall","beggar") == 0){
		if (get_module_pref("seenbeggar","beggar") || $session['user']['location']!=get_module_setting("beggarloc","beggar")) return 0; 
	}else if (get_module_setting("locall","beggar")){
		if (get_module_pref("seenbeggar","beggar")) return 0;
	}
	return $chance; 
}
function beggar_dohook($hookname,$args){
	switch($hookname){
	case "newday":
		set_module_pref("seenbeggar",0);
	break;	
	case "changesetting":
		if ($args['setting'] == "villagename") {
			if ($args['old'] == get_module_setting("beggarloc")) {
				set_module_setting("beggarloc", $args['new']);
			}
		}
	break;
	}
	return $args;
}
function beggar_runevent($type)
{
	global $session;
	$sex = translate_inline($session['user']['sex']?"missy":"sonny");
	$sex2 = translate_inline($session['user']['sex']?"he'll":"she'll");
	$sex3 = translate_inline($session['user']['sex']?"her":"his");
	require_once("lib/partner.php");
	$lover = get_partner();
	$seenbeggar = get_module_pref("seenbeggar");
	$from = "runmodule.php?module=beggar&";
	if ($type == "village") $from = "village.php?";	
	$session['user']['specialinc'] = "module:beggar";
	$op = httpget('op');	
	switch ($type) {
	case "village":
	if ($op=="" || $op=="search"){
		output_notl("`n");
		output("`2While taking a shortcut between buildings, a bony hand clutches at your ankle.");
		output(" `2Looking down, you see the grizzled face of a frail old man in tattered clothes.`n`n");			
		output("`2\"Spare a coin, %s?\" the old man rasps.`n`n", $sex);
		output("`2Will you be generous and give the old man a coin?`n`n");
		addnav("Options");
		if ($session['user']['gold']>0){addnav("Give Coin",$from . "op=givecoin");}
		if ($session['user']['gems']>0){addnav("Give Gem",$from . "op=givegem");}
		if ($session['user']['gold']>=1000){addnav("Give All Gold",$from . "op=giveall");}
		addnav("Other");
		addnav("Decline",$from . "op=decline");					
	}else if ($op=="givecoin"){
		output("`2Smiling, you pull a coin from your coin pouch and hand it to him.`n`n");
		if (is_module_active('alignment'))align(get_module_setting("gavebeggargold"));
		$session['user']['gold']--;
		set_module_pref("seenbeggar","1");			
		switch (e_rand(1,17)){
			case 1:
			case 2:
			case 3:
			output("`2The old beggar eagerly snatches the coin from you, pushes himself up on his cane, then stumbles away.");
			output(" `2Some gratitude, not even a simple thank you from the old codger!`n`n");
			output("`2Shrugging, you head on your way.`n`n");
			$session['user']['specialinc'] = "";
			break;
			case 4: 
			case 5:
			output("`2The old beggar is eager to snatch the coin from you, handing you a small rock in exhange.");
			output("`2 \"Takes that,\" he rasps. \"Found it once when I was a youngin' like yourself. 'Tis special!\"`n`n");
			output("`2With that said, he hobbles away."); 
			output(" `2Looking at the rock, you conclude there's nothing special about it and chuck it to the ground.`n`n");
			$session['user']['specialinc'] = "";			
			break;
			case 6:
			case 7:
			output("`2The old man quickly snatches the coin from you and then reaches into his robes."); 
			output(" `2A moment later he pulls out what looks like a chunk of glass and hands it to you.`n`n");
			output("`2\"Take this here,\" he says. \"'Tis something special I once found!\"`n`n");
			output("`2He hobbles away, leaving you to contemplate the chunk of glass in your hand.");
			output(" `2You give it a momentary glance and are shocked to discover it's a gem! The old man must've been half-blind...`n`n");
			$session['user']['gems']++;
			$session['user']['specialinc'] = "";			
			break;
		}
	}else if ($op=="givegem"){
		output("`2Feeling extra generous, you reach you into your gempouch and remove a glittering ruby.");
		output(" `2With a broad smile, you hand it to the old man.`n`n");
		if (is_module_active('alignment'))align(get_module_setting("gavebeggargem"));
		$session['user']['gems']--;
		$session['user']['charm']+=2;
		set_module_pref("seenbeggar","1");
		addnews("`#Kind-hearted %s `&gave a `%gem `&to a `6needy old beggar!",$session['user']['name']);
		$gemswitch = e_rand(1,6);
		switch ($gemswitch){
			case 1:
			case 2:
			output("`2The old man's eyes glimmer as he takes the gem from you.");
			output("`2 He cradles it in the palm of his hand, eyes locked on it's shimmering facets.`n`n");
			output("`2\"Why, I don't know what to say!\" he croaks. \"Aint no one ever given me a shiny piece of glass before!\"`n`n");
			output("`2\"Glass? Why no, that's a...\" you say, before being cut off by the beggar.`n`n");
			output("`2\"...genuine finely-crafted piece of glass! Thank you kindly, %s! I'll treasure it always!\"`n`n", $sex);
			output("`2He hobbles off down the alley still admiring the jewel, leaving you to shake your head in confusion.");
			output("`2 He needs to get his senses checked one of these days, you conclude.`n`n");
			output("`2However, you know upon telling %s, %s will most likely find you more charming.`n`n", $lover, $lover);
			$session['user']['charm']++;
			$session['user']['specialinc'] = "";
			break;
			case 3:
			case 4:
			output("`2The old beggar's eyes focus on the gem as he takes it from you.");
			output("`2 A moment later his brow furrows and he looks up at you.`n`n");
			output("`2\"I ask ye for a coin, and ya hand me a chunk of glass?\" he says, throwing your precious gem into the storm drain.");
			output("`2 \"You young folk, no common sense among the lot of ya!\"`n`n");
			output("`2Without another word, he hobbles down the alley way grumbling under his breath; leaving you with a look of utter confusion on your face.`n`n");
			output("`2No matter though, you know upon telling %s, %s be bound to find you more charming.`n`n", $lover, $sex2);
			$session['user']['charm']++;
			$session['user']['specialinc'] = "";
			break;
			case 5:
			case 6:
			output("`2The old beggar eagerly snatches the gem from your hands and rises to his feet.");
			output("`2 Moments later, he's hooping and hollering around the alley in fit of pure joy.`n`n");
			output("`2\"Aye, Cedrick will pay me well for this bit of glass!\" he says jubiantly, and hobbles rather quickly away down the alley.`n`n");
			output("`2\"Glass?\" you ask to yourself. Shaking your head in confusion, you take your leave of the alleyway.`n`n");
			output("`2You know though that upon telling %s, %s be bound to find you more charming.`n`n", $lover, $sex);
			$session['user']['specialinc'] = "";
			break;
		}
	}else if ($op == "giveall"){
		output("`2Feeling `ireally`i generous, you hand the old man a bulging sack containing `iall`i of your gold!`n`n");
		if (is_module_active('alignment'))align(get_module_setting("gavebeggarallgold"));
		$session['user']['gold']=0;
		$session['user']['charm']+=3;
		set_module_pref("seenbeggar","1");
		switch(e_rand(1,3)){
			case 1:
			output("`2The old beggar's eyes go wide with suprise as he eyes the large amount of gold in his hands.`n`n");
			output("`2\"Why, I...\" he stutters, tears forming in his eyes. \"I don't know what to say!");
			output(" `2In all my life, no one's ever been so kind! Why I...I...\"`n`n");
			output("`2He suddenly drops the gold to the ground and clutches at his chest in pain. With one last gasp he falls over sideways, dead from an apparant heart attack.`n`n");
			output("`2Feeling really nervous, you decide it would be best to depart before someone sees you.`n`n");
			addnews("`3%s `2accidently `@killed a beggar `2with kindness today!",$session['user']['name']);
			$session['user']['specialinc'] = "";			
			break;
			case 2:
			output("`2The old beggar glances down at the sack with an irritated look on his face. A moment later, he looks up to you.`n`n");
			output("`2\"And just how in the blazes am I 'sposed to carry all o' this?\" he chides you.");
			output(" `2\"What are y' tryin' to do, make my arthritis even worse?\"`n`n");
			output("`2He pushes himself up to his feet with his cane, gives you one last bitter look, and hobbles away.");
			output(" `2Sighing, you reach down to pick up your gold only to discover it's missing!`n`n");
			output("`3At least %s will appreciate the effort, you think.`n`n", $lover);
			addnews("`3%s `2gave `@all of %s gold `2to a beggar today!",$session['user']['name'],$sex3);
			$session['user']['charm']+=3;
			$session['user']['specialinc'] = "";
			break;
			case 3:
			output("`2The old beggar's eyes twinkle as he eyes the fortune in his lap.`n`n");
			output("`2\"Aye,\" he says with a broad grin. \"I'll give this all t' that nice minister man who what promises t' get us poor folk a seat on some wondrous ship t' th' moon!\"`n`n");
			output("`2Before you can protest, the old fellow is halfway down the alley, with all your gold in hand.`n`n");
			output("`3On the bright side, at least you feel quite a bit more charming for your actions.`n`n");
			$session['user']['charm']+=2;
			addnews("`3%s `2gave `@all of %s gold `2to a beggar today!",$session['user']['name'], $sex3);
			$session['user']['specialinc'] = "";			
			break;
		}		
	}else if ($op=="decline"){	
		if (is_module_active('alignment'))align(get_module_setting("gavebeggarnothing")*-1);	
		output("`2You shake the old man's hand loose and politely say you have nothing to spare.");
		output("`2 He mumbles something under his breath at you and goes about trying to spot someone a little wealthier than you.`n`n");
		$session['user']['specialinc'] = "";
	}	
	break;		
	}
}
function beggar_run()
{
	// Nothing to see here, move along!
}	
