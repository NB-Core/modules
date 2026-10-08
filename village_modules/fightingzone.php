<?php

function fightingzone_getmoduleinfo(){
	$info = array(
		"name"=>"Fighting Zone Chatroom",
		"author"=>"`2Oliver Brendel",
		"version"=>"1.2",
		"category"=>"Village",
		"download"=>"",
		"settings"=>array(
			"Fighting Zone Settings,title",
			"rulesurl,Address of the zone rules (empty: no rules link)|",
		),
	);
	return $info;
}

function fightingzone_install(){
	module_addhook_priority("village",50);
	return true;
}
function fightingzone_uninstall(){
	return true;
}

function fightingzone_dohook($hookname,$args){
	global $session;
	switch($hookname){
	case "village":
		addnav($args['fightnav']);
		addnav("Fighting Zone","runmodule.php?module=fightingzone");
		break;
	}
	return $args;
}

function fightingzone_run(){
	global $session;
	$op = httpget("op");
	require_once("lib/commentary.php");
	require_once("lib/villagenav.php");
	addcommentary();	
	page_header("Fighting Zone");
	output("`b`i`c`\$Fighting `!Zone`c`i`b`n");
	if ($op=="") 
		$zone=translate_inline("Main");
		else
		$zone=substr($op,strpos($op,"-")+1);
	require_once("modules/addimages/addimages_func.php");
	output("`b`i`c`!Zone %s`c`i`b`n`n",$zone);
	output("`2Here you are allowed to fight, use jutsus and whatever you like, as long as you can do it with words only.");
	output("`nAll villages are linked here with special portals. It does not matter where you are, you can join in.");
	output("`nDo not use rude language... you may taunt your enemy, but stick to normal language and don't use trash talk or nasty words. Thank you.`n`n");
	villagenav();
	$rulesurl = trim((string) get_module_setting('rulesurl'));
	if ($rulesurl !== '') {
		addnav("`bRules`b");
		addnav("Rules for the Zone",$rulesurl,false,true,"");
	}
	modulehook("fightingzones",array());
	addnav("Arenas");
		addnav("`4Main Zone`0","runmodule.php?module=fightingzone");
	// for ($i=1;$i<11;$i++) {
		// addnav(array("Fighting Ground %s",$i),"runmodule.php?module=fightingzone&op=ground-$i");
	// }
	addnav("Fighting Grounds");
	addnav("#1 `tDesert","runmodule.php?module=fightingzone&op=ground-1");
	addnav("#2 `1Wetlands","runmodule.php?module=fightingzone&op=ground-2");
	addnav("#3 `#Lightning plains","runmodule.php?module=fightingzone&op=ground-3");
	addnav("#4 `\$Fire Valley","runmodule.php?module=fightingzone&op=ground-4");
	addnav("#5 `2Green Lands","runmodule.php?module=fightingzone&op=ground-5");
	addnav("#6 `gPlains","runmodule.php?module=fightingzone&op=ground-6");
	addnav("#7 `!Iceland","runmodule.php?module=fightingzone&op=ground-7");
	//addnav("#8 `tObsidianFlameCore VS `~N`4a`qru`4to`~U`@zu`2mak`@i","runmodule.php?module=fightingzone&op=ground-8");
	addnav("#8 `)Font of Darkness","runmodule.php?module=fightingzone&op=ground-8");
	addnav("#9 `xEmpty Town","runmodule.php?module=fightingzone&op=ground-9");
	addnav("#10 `~Sleepy Hollow","runmodule.php?module=fightingzone&op=ground-10");
	addnav("#11 `)Death Valley","runmodule.php?module=fightingzone&op=ground-11");
	if ($session['user']['dragonkills']>=25) {
		addnav("Sannin Level");
		addnav("#1 `tDesert","runmodule.php?module=fightingzone&op=ground-20");
		addnav("#2 `1Wetlands","runmodule.php?module=fightingzone&op=ground-21");
		addnav("#3 `#Lightning plains","runmodule.php?module=fightingzone&op=ground-22");
		addnav("#4 `)Dark Cave","runmodule.php?module=fightingzone&op=ground-23");
	}
	if ($session['user']['dragonkills']>=50) {
		addnav("Hokage Level");
		addnav("#1 `gPlains","runmodule.php?module=fightingzone&op=ground-30");
		addnav("#2 `!Iceland","runmodule.php?module=fightingzone&op=ground-31");
		addnav("#3 `)Death Valley","runmodule.php?module=fightingzone&op=ground-32");
		addnav("#4 `xEmpty Town","runmodule.php?module=fightingzone&op=ground-33");
		addnav("#5 `\$Valley of the End","runmodule.php?module=fightingzone&op=ground-34");
	}
	if ($op=="")  {
		//output("`2This is the main area...you see several areas beside it.`n`n");
		output("`2Main Zone- `^You walk in with your Shinobi tools and weapons ready and looking for a fight.  You enter the main zone and you see a large arena-type zone occupied by a white paneled floor with several small trees. Around the paneled floor you see it is surrounded by large amounts of sand and a lake over to the side.");
		commentdisplay("`n`n`@Shout out your actions.`n","fightingzone","Shout out",20,"shouts");
	} elseif(strstr($op,"ground")) {
		switch ($zone) {
			case 1:
				addimage("fightingzone/desert.gif");
				output("`2Zone 1(Desert) - `^You walk in with your Shinobi tools and weapons ready, looking for a fight.  You look around and notice that this zone is completely barren and covered entirely in sand with large gusts of `4wind `^frequently passing through.");
				break;
			case 2:
				addimage("fightingzone/wet.gif");
				output("`2Zone 2(Wetlands) - `^You walk in with your Shinobi tools and weapons ready, looking for a fight.  You walk into the zone and are immediately berated by gusts of `!rain `^and `4wind; `^you notice that the nearby lakes and ponds of the zone have flooded over causing puddles all over the zone.");
				break;
			case 3:
				addimage("fightingzone/lightning.gif");
				output("`2Zone 3(Lightning Plains) - `^You walk in with your Shinobi tools and weapons ready, looking for a fight.  You walk in and look up at the `)cloudy `^sky. `nYou can see flashes of `t lightning `^high up in the clouds and you can hear the rumbling of thunder rather close by.");
				break;
			case 4:
				addimage("fightingzone/fire.gif");
				output("`2Zone 4(Fire Valley) - `^You walk in with your Shinobi tools and weapons ready, looking for a fight.  In the distance you can see that there are several `4v`\$olcanoes `^a long distance away. `nSeveral of them are spewing out large amounts of ash and lava littering the zone with hot fire, and many tall trees are ablaze in this zone.");
				break;
			case 5:
				addimage("fightingzone/green.gif");
				output("`2Zone 5(Green Lands) - `^You walk in with your Shinobi tools and weapons ready, looking for a fight.  You look around and notice you stand amidst a vast `@forest `^of large tall trees many of which block out the sun creating large areas of `~shade.`^");
				break;
			case 6:
				addimage("fightingzone/plains.gif");
				output("`2Zone 6(Plains) - `^You walk in with your Shinobi tools and weapons ready, looking for a fight.`n You notice you're in an almost `)empty `^field, filled with tall `2grass `^where large gusts of wind blow through the zone. `n`nIt seems shifting through the grass and blowing past you.");
				break;
			case 7:
				addimage("fightingzone/ice.gif");
				output("`2Zone 7(Icelands) - `^You walk in with your Shinobi tools and weapons ready, looking for a fight. You enter the zone and immediately a `Jchill `^is sent up your spine from the sheer `1cold `^of the room.  You notice that it is snowing heavily and a few inches of snow covers the zone's ground.");
				break;
			case 8:
			case 11:
				addimage("fightingzone/death.gif");
				output("`2Zone 8(Death Valley) - `^You walk in with your Shinobi tools and weapons ready, looking for a fight.  Steep canyons and gigantic boulders adorn this zone, at the bottom of the canyons are vast amounts also in the zone is a `~dark `^looking cave that once within its depths is completely dark, many have `)lost `^their way and never returned with stalagmite rocks covering the cave floor.");
				if ($zone==11) output("`n`n`xOver the years, the battlefield has been scarred by the relentless battles of `tObisdianFlameCore`x and `5`~N`4a`qru`4to`~U`@zu`2mak`@i`x");
				break;
			case 9:
				addimage("fightingzone/town.gif");
				output("`2Zone 9(Empty Town...) - `^You walk in with your Shinobi tools and weapons ready, looking for a fight.  The zone is `)enshrouded `^in `~darkness. `^A full moon hangs in the sky giving the zone some slight amount of light.  Around are several building awnings and water towers, it seems to resemble the streets of a deserted city.");
				break;
			case 10:
				addimage("fightingzone/hollow.gif");
				output("`2Zone 10(Sleepy Hollow) - `^You walk in with your Shinobi tools and weapons ready, looking for a fight.  This zone seems to be all in a dark `)grey `^hue.`n Many `~dead `^trees are making small forests all throughout the zone with small winding paths in between,  you notice that some of the trees have weapons sticking out of them and, instead of sap, `4blood `^comes out of the trees.");
				break;
			case 20:
				addimage("fightingzone/desert2.gif");
				output("`gSannin Level Zone... - `^Here is where the real powerful fights happen and not everyone can enter.`n`n`^`2Zone 1(Desert) - `^You walk in with your Shinobi tools and weapons ready, looking for a fight.  You look around and notice that this zone is completely barren and covered entirely in sand with large gusts of `4wind `^frequently passing through.");
				break;
			case 21: 
				addimage("fightingzone/wet2.gif");
				output("`gSannin Level Zone... - `^Here is where the real powerful fights happen and not everyone can enter.`n`n`^`2Zone 2(Wetlands) - `^You walk in with your Shinobi tools and weapons ready, looking for a fight.  You walk into the zone and are immediately berated by gusts of `!rain `^and `4wind; `^you notice that the nearby lakes and ponds of the zone have flooded over causing puddles all over the zone.");
				break;
			case 22:
				addimage("fightingzone/lightning2.gif");
				output("`gSannin Level Zone... - `^Here is where the real powerful fights happen and not everyone can enter.`n`n`^`2Zone 3(Lightning Plains) - `^You walk in with your Shinobi tools and weapons ready, looking for a fight.  You walk in and look up at the `)cloudy `^sky. `nYou can see flashes of `t lightning `^high up in the clouds and you can hear the rumbling of thunder rather close by.");
				break;
			case 23:
				addimage("fightingzone/cave.gif");
				output("`gSannin Level Zone... - `^Here is where the real powerful fights happen and not everyone can enter.`n`n`^`2Zone 4(Dark Cave) - `^You walk in with your Shinobi tools and weapons ready, looking for a fight. You look around and notice that this zone is completely `~dark `^and covered with rock formations of all shapes and sizes.");
				break;						
			case 30:
				addimage("fightingzone/plains2.gif");
				output("`yHokage Level Zone... - `^Here is where the real powerful fights happen and not everyone can enter.`n`n`2Zone 1(Plains) - `^You walk in with your Shinobi tools and weapons ready, looking for a fight.`n notice you're in an almost `)empty `^field, filled with tall `2grass `^where large gusts of wind blow through the zone. `n`nIt seems shifting through the grass and blowing past you.");
				break;
			case 31:
				addimage("fightingzone/ice2.gif");
				output("`yHokage Level Zone... - `^Here is where the real powerful fights happen and not everyone can enter.`n`n`2Zone 2(Icelands) - `^You walk in with your Shinobi tools and weapons ready, looking for a fight. You enter the zone and immediately a `Jchill `^is sent up your spine from the sheer `1cold `^of the room.  You notice that it is snowing heavily and a few inches of snow covers the zones ground.");
				break;
			case 32:
				addimage("fightingzone/death2.gif");
				output("`yHokage Level Zone... - `^Here is where the real powerful fights happen and not everyone can enter.`n`n`2Zone 3(Death Valley) - `^You walk in with your Shinobi tools and weapons ready, looking for a fight.  Steep canyons and gigantic boulders adorn this zone, at the bottom of the canyons are vast amounts also in the zone is a `~dark `^looking cave that once within its depths is completely dark, many have `)lost `^their way and never returned with stalagmite rocks covering the cave floor.");
				break;
			case 33:
				addimage("fightingzone/town2.gif");
				output("`yHokage Level Zone... - `^Here is where the real powerful fights happen and not everyone can enter.`n`n`2Zone 4(Empty Town...) - `^You walk in with your Shinobi tools and weapons ready, looking for a fight.  The zone is `)enshrouded `^in `~darkness. `^A full moon hangs in the sky giving the zone some slight amount of light.  Around are several building awnings and water towers, it seems to resemble the streets of a deserted city.");				
				break;
			case 34:
				addimage("fightingzone/end.gif");
				output("`yHokage Level Zone... - `^Here is where the real powerful fights happen and not everyone can enter.`n`n`2Zone 5(Valley of the End...) - `^You walk in with your Shinobi tools and weapons ready, looking for a fight. You could not help but notice the two large statues in this zone which are separated by a huge waterfall, with `2Senju Hashirama `^on one side of the waterfall and `4Uchiha Madara `^on the other. The thundering roar of the falls cancels out all other noises in the area.");				
				break;				
		}
		commentdisplay("`n`n`@Shout out your actions.`n","fightingzone-$zone","Shout out",20,"shouts");
			
	}
	page_footer();
}
?>
