<?php

function mount_feeder_getmoduleinfo() {
	$info = array(
		"name"=>"Mount Feeder",
		"author"=>"`LShinobiIceSlayer",
		"version"=>"1.0",
		"category"=>"Forest Specials",
		"download"=>"",
		"settings"=>array(
			"Mount Feeder - Preferences, title",
			"A creepy old man that offers players so feed for mounts,note",
			"limit"=>"How many feed items can a player buy each gameday?,range,1,5,1|1",
			"chance"=>"How like is this event in the forest?,range,1,100,1|25",
			"experienceloss"=>"Percentage: How many experience is lost/won after a fight,floatrange,1,100,1|10",
			),
                "prefs"=>array(
                        "bought"=>"How many feeds as the player bought today?,int|0",
                        "encountered"=>"Has the player encountered the feeder in the forest today?,int|0",
                        ),
                "requires"=>array(
                        "inventory"=>"1.0|Inventory system must be installed",
                        ),
	);
	return $info;
}

function feeder_chance(){
	global $session;

	if ($session['user']['hashorse'] != 0){
		if (get_module_pref("encountered", "mount_feeder") > 0){
			return 0;
		} else {
			return get_module_setting("chance", "mount_feeder");
		}
	} else {
		return 0;
	}
}

function mount_feeder_install() {
        if (!is_module_active('inventory')) {
                output("This module requires the Inventory module to be installed.");
                return false;
        }
        module_addhook("newday");
        module_addhook("stables-desc");
        module_addhook("stables-nav");
        module_addeventhook("forest", "require_once('modules/mount_feeder.php'); return feeder_chance();");
// note to self - this may add too many items. mind uninstalling...
        if (!is_module_active('mount_feeder')) {
                $sql="INSERT INTO item (class, name, description, gold, gems, weight, droppable, level, dragonkills, buffid, charges, link, hide, customvalue, execvalue, exectext, noeffecttext, activationhook, findchance, loosechance, dkloosechance, sellable, buyable, uniqueforserver, uniqueforplayer, equippable, equipwhere) VALUES
                ('Loot', '`TMount Feed', 'A strange, meat like blob. Apparently mounts find this appealing', 1, 0, 1, 0, 1, 0, 0, 0, '', 0, '', 'require_once(\"modules/mount_feeder/item_code.php\");', '`7The you throw the lump of strange feed to you mount.', '', '86', 0, 0, 0, 0, 0, 0, 0, 0, '');";
                db_query($sql);
        }

        return true;
}

function mount_feeder_uninstall() {
        return true;
}

function mount_feeder_add_feed($amount = 1) {
        if (is_module_active('inventory')) {
                require_once("modules/inventory/lib/itemhandler.php");
                add_item_by_name("`TMount Feed", $amount);
        } else {
                output("`4Warning:`0 Inventory module not active; feed not delivered.`n");
        }
}

function mount_feeder_dohook($hookname,$args) {
	global $session;
	switch($hookname){
	case "newday":
		set_module_pref("bought", 0);
		set_module_pref("encountered", 0);
		break;
	case "stables-desc":
		if ($session['user']['hashorse'] != 0){
			output("`n`nYou notice over in a quiet corner, a bent over old man, gesturing for you to come over.");
		}
		break;
	case "stables-nav":
		if ($session['user']['hashorse'] != 0){
			addnav("Other");
			addnav("Visit Old Man", "runmodule.php?module=mount_feeder");
		}
		break;
	}
	return $args;
}

function mount_feeder_runevent($type,$link) {
	global $session, $playermount;
	$from = "forest.php?";
	$session['user']['specialinc'] = "module:mount_feeder";
	$op = httpget('op');
	$cost = round($session['user']['level']*$playermount['mountfeedcost']*1.1); //Slightly cheaper here.
	
	switch ($op) {
	case "":
		output("`7Ahead along the forest path, you spy a little old man carrying a large woven basket on his back.");
		output(" He stopped as you neared with a smile. `T\"Hey you! Want some extra feed for your mount? I have some extra fresh stuff right here.\"`7");
		output("`n`nThe old man smiles like a young boy, rocking back and forth the balls of his feet. `T\"There is a small fee of %s gold however.\"`7",$cost);
		output("`n`nWhat do you want to do?");		
		set_module_pref("encountered", 1);
		if ($session['user']['gold'] >= $cost) addnav(array("Buy Feed (%s)",$cost),$link."op=buy");
		else addnav(array("Buy Feed (%s)",$cost),"");
		addnav("Rob",$link."op=rob");
		addnav("Ignore",$link."op=ignore");
		break;
	case "buy":
		output("`7You hold out requested amount of gold, and the old man quickly snatches it up, hiding it somewhere on his person so fast you lose track of it.");
		output(" He swings the basket off his back, and pulls out a lump of the meat like substance, still dripping. He hands the thing to you with a smile.");
		output(" `T\"There you go, some nice, fresh feed for your mount, I'm sure they'll love it.\"`7");
                mount_feeder_add_feed();
                $session['user']['gold']-=$cost;
                $session['user']['specialinc'] = "";
                break;
	case "rob":
		output("`7You see the poor, defenseless looking old man, and decide you don't need to pay for enough. Slowly you approach him, drawing your %s`7.`n`n",$session['user']['weapon']);
		$chance = e_rand(1,10);
		if ($chance == 1){
			//Run
			output("`7The old man looks up at you, then screams, making a run for it. You bolt after them, only to be overcome by a huge shadow.");
			output(" A giant winged beast swoops down, and picks up the old man, flying away with him. The stand, staring in wonder at the strange event.`n`n");
			$cchance = e_rand(1,10);
                        if ($cchance == 1){
                                mount_feeder_add_feed();
                                output("Looking down, you notice the old man appeared to drop something, a nice fresh lump of feed.");
                        }
			if ($cchance == 10){
				//attacked!
				$hploss = round($session['user']['hitpoints']/e_rand(2,4));
				$session['user']['hitpoints']-=$hploss;
				output("The winged beast comes around, and dives on you, slash at you with it's close. You lose `4%s `7hitpoints.",$hploss);
			}
			$session['user']['specialinc'] = "";
		} elseif($chance > 1 && $chance < 5){
			//Surrender
			output("The old man flails in fear, the throws a lump of the feed at you. `T\"Take it! Just let me go!\" `7The old man runs off, leaving you with a free feed.");
                        mount_feeder_add_feed();
                        $session['user']['specialinc'] = "";
                } else {
			//Attack!
			output("`7The old man eyes you with interest. `T\"You plan take my stock by force? Well then, lets see if I can tenderize you child.\"`n");
			addnav("Attack",$link."op=attack");
		}		
		break;
	case "ignore":
		output("`7You decided you want nothing to do with this strange old man, and keep on your way through the forest.`n`n");
		$session['user']['specialinc'] = "";
		break;
	case "attack": 		
		output("`7The old man slams his hands on the ground, summoning a giant beast, all muscle, and sharp pointy parts. The old man cackles as the beast makes his way towards you.`n`n");
		require_once("lib/battle-skills.php");
		$badguy = array(
		"creaturename"=>"Giant Beast!",
		"creaturelevel"=>$session['user']['level']+2,
		"creatureweapon"=>"Massive Claws and Fangs",
		"creatureattack"=>$session['user']['attack']+$session['user']['dragonkills']+5,
		"creaturedefense"=>$session['user']['defense']+$session['user']['dragonkills']+10,
		"creaturehealth"=>($session['user']['level']*10+50+round(e_rand($session['user']['level'],($session['user']['maxhitpoints']-$session['user']['level']*10)))),
		"diddamage"=>0,);		

		$battle=true;
		$session['user']['badguy'] = createstring($badguy);
		$op = "combat";
		httpset('op', $op);
		case "combat": case "fight":
		include("battle.php");
		if ($victory){ 
			output("`n`n`7...%s`7 dies by your hand. The old man laughs, and opens his basket. Suddenly the creatures corpse gets sucked into the basket by a vortext.",$badguy['creaturename']);
			output(" `T\"Well, I guess this will have to make do then.\" `7The old man laments.`n`n");
			addnews("%s`^ survived an encounter with the Mount Feeder`^.",$session['user']['name']);
			$chance = e_rand(1,10);
                        if ($chance < 6){
                                output("Distracted by the scene, the old man slips away before you can notice.");
                        } elseif($chance > 5 && $chance < 9){
                                output("Distracted by the scene, the old man slips away before you can notice. However you noticed a lump of feed left behind in his haste.");
                                mount_feeder_add_feed();
                        } else {
                                output("Distracted by the scene, the old man slips away before you can notice. However you noticed TWO lumps of feed left behind in his haste.");
                                mount_feeder_add_feed(2);
                        }
			$badguy=array();
			$session['user']['specialinc'] = "";
			$session['user']['badguy']="";
		}elseif ($defeat){ 
			$exploss = $session['user']['experience']*get_module_setting("experienceloss")/100;
			output("`n`n`7You are dead... struck down by %s `7. The old man cackles, and suddenly sucks your corpse into his basket with a vortex.`n",$badguy['creaturename']);
			output("`t\"Yes, you will do nicely indeed. Just perfect.\" `7The old man cleans up the mess, letting your body slowly turn into his famous feed.");
			if ($exploss>0) output(" You lose `^%s percent`7	of your experience and all of your gold.",get_module_setting("experienceloss"));
			$session['user']['experience']-=$exploss;
			$session['user']['gold']=0;
			debuglog("lost $exploss experience and all gold to the Mount Feeder.");
			addnews("%s`^ was killed by the Mount Feeder`^.",$session['user']['name']);
			addnav("Return");
			addnav("Return to the Shades","shades.php");
			$session['user']['specialinc'] = "";
			$badguy=array();
			$session['user']['badguy']="";
		}else{
			require_once("lib/fightnav.php");
			$allow = true;
			fightnav($allow,false);
			if ($session['user']['superuser'] & SU_DEVELOPER) addnav("Escape to Village","village.php");
		}
	}
}

function mount_feeder_run(){
	global $session, $playermount;
	$op = httpget('op');
	if (!isset($playermount['mountfeedcost'])) {
		$playermount['mountfeedcost'] = 10000;
		$playermount['mountname'] = 'Master Ducky the Great Bug Mount of Code';
	}
	$cost = round($session['user']['level']*$playermount['mountfeedcost']*1.2); 
	page_header("A Little Old Man");
	switch($op) {
		case "":
			output("`7You go around the corner, and find a little, bent over old man. He carries a large woven basket on his back, and a childlike grin upon is face.");
			output(" The old man drags you down to his level, and whispers in your ear. `T\"Hey, I got some special stuff here, and little extra feed you can give to %s `Ton the go.\"",$playermount['mountname']);
			output(" The old man gives you a gummy grin. `T\"It's cheap too, only %s gold. You know it's a good deal.\"`n`n",$cost);
			addnav("Options");
			if ($session['user']['gold'] >= $cost){
				if (get_module_pref("bought") < get_module_setting("limit")) addnav(array("Buy Feed (%s)",$cost), "runmodule.php?module=mount_feeder&op=buy");
			} else {
				addnav(array("Buy Feed (%s)",$cost), "");
				output(" `7The old man looks disappointed. `T\"Seem you can't afford my feed off you go youngin'\"`7`n");
			}
			if (get_module_pref("bought") >= get_module_setting("limit")){
				output("`7The old man chases you away, saying you've had your share of feed for the day.");
			}
			addnav("Ask about Feed", "runmodule.php?module=mount_feeder&op=ask");
			break;
		case "buy":
			output("`7The old man beam brightly, swinging the basket off his back. `T\"Good choice young one.\"`7");
			output(" The old man lifted the lid on his basket slightly, and quickly reached in, and snatched something out, not giving you a chance to peek inside.");
			output(" `T\"Well here you go, my special feed.\" `7The man holds out a strange fleshy looking lump to you, the smell quite overpowering.");
			output(" As you take the strange feed, the old man clears his throat, and holds out a hand. `T\"My Payment?\"`7 You fish the required amound from your pockets and hand over.");
			output(" `T\"Good, well your %s should enjoy that! Just give it to them, and they'll be back to tip top shape in no time... usually.\" `7The old mans says before ushering you away.",$playermount['mountname']);
                        mount_feeder_add_feed();
                        $session['user']['gold']-=$cost;
                        set_module_pref("bought", 1);
                        break;
		case "ask":
			output("`7You decide to ask the old man what it is that makes his feed so special. The old man laughs, setting the basket down, you swear you see it move.");
			output(" `T\"Well, it's the special ingredients I use you see. Plus I have my own unique preparation methods, eleven secret herbsand spices, that sort of thing.\"`7");
			output(" You try asking more questions, but get little more from the old man, it becomes clear he isn't going to reveal anything extra to you.");
			addnav("Options");
			if (get_module_pref("bought") < get_module_setting("limit")) {
				if ($session['user']['gold'] >= $cost) addnav(array("Buy Feed (%s)",$cost), "runmodule.php?module=mount_feeder&op=buy");
			}
			break;
	}
	addnav("Return");
	require_once("lib/villagenav.php");
	villagenav();
	page_footer();
}

?>
