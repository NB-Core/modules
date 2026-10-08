<?php
function wintercastle_getmoduleinfo(){
	$info = array(
		"name" =>"Winter Castle, copy of Haunted House",
		"version" =>"0.1",
		"author" =>"Based on Work by KainStrider",
		"category" =>"Winter",
		"download"=>"",
		"settings"=>array(
			"Winter Castle Settings,title",
			"start"=>"Activation start date (mm-dd)|12-1",
			"end"=>"Activation end date (mm-dd)|12-31",
		),
	);
	return $info;
}

function wintercastle_install(){
	module_addhook("Village");
	return true;
}

function wintercastle_uninstall(){
	return true;
}

function wc_datecheck() {
		$mytime = get_module_setting("start");
		$start = strtotime(date("Y")."-".$mytime);
		$mytime = get_module_setting("end");
		$end = strtotime(date("Y")."-".$mytime);	
		
		$now = strtotime("now");
		
		if ($start<=$now && $now<=$end) {
			return true;
		} else {
			return false;
		}

}

//dohook

function wintercastle_dohook($hook,$args){
	global $session;
	//check for activation date
	if (wc_datecheck()===false) {
		return $args;
	}
	switch($hook){
		case "village":
			tlschema ($args['schemas']['othernav']);
			addnav("Village Gates");
			tlschema();
			/*
			addnav(); examples
			addnav("My Module Link #1","runmodule.php?module=pages&op=page1");
			addnav("My Module Link #2","runmodule.php?module=pages&op=page2");
			*/
			output("`e`nThere is a large ice parlor with snowpeople near the Village Gates.`n");
			addnav("`lW`Linter `)C`jastle","runmodule.php?module=wintercastle&op=hauntede");
		break;
	}
	return $args;
}

//run function

function wintercastle_run(){
	global $session;
	$op = httpget('op');
	addnav("Exit the Winter Castle");
	addnav("Return to the Gates","runmodule.php?module=wintercastle&op=hauntede");
	addnav("Actions");
	
	if ($op == "hauntede"){
		page_header("The Snowy Gate");
		output("`eYou stumble along the snowmen and -women, only discernible by wearing a `Ppinkish`e nose, to stand suddenly before a massive iron gate. `nThe iron bars, as cold as ice to your touch, are towering befor you, shrouding all that stands behind it. `nYou step closer, counting the frozen flakes on the iron which seem to have been spread only recently due to the veil of the winter. You carry on and the gates part by frosty hand... slowly, icily, beckoning you forward.");
		output("`e`n`nWinds sweep by causing the trees to whispered to the air and their surroundings. The moon shines bright white, in a cloudless sky... the only source of light that can be seen for miles. `nThe solemnity of this place calms you, while you look around, your presence bringing warmth to this place.`n`nThe air is cold and numb and with every breath you draw a misty, chilly exhale follows.`n`nAs the castle draws nearer, everything around you became quieter and more distant. The winter trees surrounding this place seem to watch you, the cold iron gates are far, far back in the distance...");
		output("`e`n`nCan you gather the necessary courage to step forward through the gate and go on a frosty adventure?");
		addnav("Gather Your Courage");
		addnav("Enter the Gate","runmodule.php?module=wintercastle&op=wintercastlemain");
		addnav("Run Away");
		villagenav();
	}
	
	if ($op == "wintercastlemain"){
		page_header("The `lW`Linter `)C`jastle");
		output("`$`b`c`lW`Linter `)C`jastle`b`c`n`n`KAs you step through the doors of the castle, a cool shudder trickles down your spine.`n`nGlancing around nervously, you see portraits of snownobles around, seemingly highly decorated amonst their kin.`n`nCold, hesitant light streams in through a cracked window, making the scene really surreal as you see an owl sitting on a twig from one of the present dynasty.`n`nYou slowly proceed into the castle...");
		output("`K`n`nYou notice a slippery, frost-covered staircase spiraling upwards onto the `6second floor`K of the castle. `n`nThere seems to be some noises coming from the floor above, not to mention the steady drip, drip, drip of cold water leaking through the ceiling in places.");
		output("`K`n`nThere is another staircase, hidden in a tucked away corner leading down into an inky black `6cellar`K. Do you have the courage to descend into the bowels of the castle?");
		output("`K`n`nYou spot an ancient wooden door frame with an iron door barely hanging off it's hinges. There is mud tracked in, with what appears to be frostflkes caked into it. Maybe it leads to a `6garden`K?`n`n");
		require_once("lib/commentary.php");
		addcommentary();
		viewcommentary("wintercastle", "Frosty Fun,", 30, "says");
		addnav("Explore the castle");
		addnav("Go Upstairs","runmodule.php?module=wintercastle&op=hauntedfloor2");
		addnav("Go to the Cellar","runmodule.php?module=wintercastle&op=hhbasement");
		addnav("Spooky Gardens","runmodule.php?module=wintercastle&op=wintercastlegarden");
	}
	
	if ($op == "wintercastlegarden"){
		page_header("The Winter Castle Gardens");
		output("`b`$`cThe `lF`Lrosty `2Garden`c`b`n`n`KMerry folks around you drink some small punch from a source unknown to you.`n`nSmall elves float around you, tending to the trees and plants - decorating them with a more vivid and luxurious decor fitting the season.`n`n");
		output("`KYou can feel the squish flower pedals as you make your way through the gardens. It seems to be the end of the garden as you venture along, only to be stopped by a large line of trees.");
		output("`n`n`KOff in the distance, there is a `6patch of frozen flowers`K that seems to be doing well... But then you realize it must have a caretaker...`n`n");
		require_once("lib/commentary.php");
		addcommentary();
		viewcommentary("wc_garden", "Winter Garden Talk,", 30, "says");
		//addnav("Little Cemetary");
		//addnav("Read Gravestones","runmodule.php?module=gravestoneshh&op=gravestonemodule");
		addnav("Go Elsewhere");
		//addnav("Halloween Party","runmodule.php?module=halloweenmaskedballwintercastle"); -finish later-
		//addnav("Pumpkin Patch","runmodule.php?module=pumpkinhouse");
		//addnav("The Corn Maze","runmodule.php?module=xxx"); //********** To be finished later CORN MAZE to be finished later ***********//
		addnav("Return to the Castle","runmodule.php?module=wintercastle&op=wintercastlemain");
	}
	
	//hauntedfloor2 contains trickortreathh and Applebob
	
	if ($op == "hauntedfloor2"){
		page_header("The Winter Castle Second Floor");
		output("`KYou cringe at each creak on the old warped stairs, but it doesn't sway your determination to make it to the second floor. `n`nHalfway up, a shadow flickers at the corner of your vision. `nYou freeze, and as you stand there, you catch a woody scent lingering in the air. Tobacco smoke? Fire?`n`nA warm smell of fruit cake penetrates the air as you carry on.`n`n");
		output("Turning back forward, you creep to the top. The shimmering firelight of your torch flickers over a set of doors positioned at separate points in a long lit hall way.. You reach out and test them...one is barred, a pool of wetness seeping from under the door...the second locked with a sign 'knock to come in'... Do you wish to enter?");
		output("`K`n`nAt the very end of the shady hall way there is `6locked door`K with a little light coming from the cracks around the door frame. `nThis door's lock looks formidable. Do you want to knock on it?");
		addnav("Explore the castle");
//		addnav("Open the Unlocked Door","runmodule.php?module=applebobhh");
		addnav("Knock on the Locked Door","runmodule.php?module=wc_trickortreat&op=knockdoor");
		addnav("Go Downstairs","runmodule.php?module=wintercastle&op=wintercastlemain");
	}
	
	//trickortreat!
	
	if ($op == "hhbasement"){
		page_header("The Cellar of the Winter Castle");
		output("`$`c`bThe Winter Cellar`c`b`K`n`nYou slowly step to the cellar door, fearlessly moving as you circle the door knob..turn.. and push it open. `nYou peer down into the inky blackness below, wondering why nobody thought of bringing a light here!");
		output("`KYou summon your strength and tiptoe your way down the stairs. Each step intensified by moaning and creaking, as if the steps could collapse at any moment.");
		output("`K`n`nThere is a room off to the left with a glowing blue light and a deep thrumming sound coming from it. On the wall over the door there is a picture of a `6rock, a peice of paper, and a pair of scissors`K. You should probably investigate it.");
		require_once("lib/commentary.php");
		addcommentary();
		viewcommentary("wc_cellar", "Cellar Talk,", 30, "says");		
		addnav("Explore the Cellar");
		addnav("The Left Room","runmodule.php?module=wintercastle&op=hbleftroom");
		addnav("Return");
		addnav("Go Upstairs","runmodule.php?module=wintercastle&op=wintercastlemain");
		modulehook("hhbasement-navs");
	}
	
	if ($op == "hbleftroom"){
	page_header("Casper's Games");
	output("`KA small but friendly looking ghost floats in the corner of the room, seemingly friendly.");
	addnav("Approach the Ghost");
	addnav("Speak To Casper","runmodule.php?module=wc_casper&op=playgame");
	addnav("Return to the Basement");
	addnav("Leave","runmodule.php?module=wintercastle&op=hhbasement");
	}
	
page_footer();
}
?>
