<?php
function hauntedhouse_getmoduleinfo(){
	$info = array(
		"name" =>"Haunted House (timelocked)",
		"version" =>"0.1",
		"author" =>"KainStrider, small mods by Oliver Brendel",
		"category" =>"Holidays|Halloween",
		"settings" => 
			array(
					"Haunted House Module Settings,title",
					"hauntedhouseloc"=>"Where does the Haunted House appear?,location|".getsetting("villagename", LOCATION_FIELDS),
					"start"=>"Activation start date (mm-dd)|10-31",
					"end"=>"Activation end date (mm-dd)|11-02",	
				),
		"requires"=>array(
			"datemanager"=>"1.0|By Oliver Brendel",
		),
	);
	return $info;
}

function hauntedhouse_install(){
	module_addhook("village");
	return true;
}

function hauntedhouse_uninstall(){
	return true;
}

//dohook

function hauntedhouse_dohook($hook,$args){
	global $session;
	switch($hook){
		case "village":

			//check for datemanager
			require_once("modules/datemanager.php");
			$start = get_module_setting("start");
			$end = get_module_setting("end");
			$check=datemanager_datecheck($start,$end,0);

			//check for activation date
			if ($check!=1) return $args;

			// Check location 
			if ($session['user']['location'] != get_module_setting("hauntedhouseloc")) break;
			tlschema ($args['schemas']['othernav']);
			addnav("Village Gates");
			tlschema();
			output("`5`nThe is a very spooky looking and run down house near the Village Gates.`n");
			addnav("`2Ha`@un`2te`@d `2Ho`@us`2e","runmodule.php?module=hauntedhouse&op=hauntede");
		break;
	}
	return $args;
}

//run function

function hauntedhouse_run(){
	global $session;
	$op = httpget('op');
	
	if ($op == "hauntede"){
		page_header("The Haunted Gate");
		output("`5Whether due to a mishap in the forest, or foolish thoughts of being labeled a Hero intent, you suddenly find yourself before a massive iron gate. The iron bars, as cold as ice to your touch, stand over 20 feet tall, shrouding all that stands behind it. You step closer, intending to put all your weight behind pushing them open when the ground beneath your feet trembles, piercing moans and groans fill the air around you, and the gates part...slowly, mockingly, beckening you forward.");
		output("`5`n`nWinds sweep by causing the trees to whispered to the air and their surroundings. The moon shines bright white, in a cloudless sky... the only source of light that can be seen for miles. Owls and bats occasionally fluttered by overhead, their silhouettes passing over the grass. The air is cold and numb and with every breath you draw a misty, chilly exhale follows. As the house draws nearer everything around you became quieter and more distant. The trees murmuring can't be heard anymore and the cold iron gates are far, far back in the distance...");
		output("`5`n`nCan you gather the necessary courage to step forward through the gate and adventure in the haunted house?");
		addnav("Gather Your Courage");
		addnav("Enter the Gate","runmodule.php?module=hauntedhouse&op=hauntedhousemain");
		addnav("Run Away");
		villagenav();
	}
	
	if ($op == "hauntedhousemain"){
		page_header("The Haunted House");
		output("`$`b`cThe Haunted House`b`c`n`n`^As you step through the doors of the old mansion, a cool shudder trickles down your spine. Glancing around nervously, you see somber portraits staring at you from behind layers of dust, seemingly penetrating your very being. Cold, hesitant light streams in through a cracked window, casting eerie shadows on the walls. As you walk forward, you can't help but feel that someone ...or something...is following you. Whirling around, you see nothing but the empty hallway and the faces in the portraits staring at you. 'Turn back' they seem to say, but you swallow a nervous whimper and continue into the dark bowels of the house...");
		output("`^`n`nYou notice a dusty staircase spiraling upwards onto the `6second floor`^ of the house. There seems to be some noises coming from the floor above, not to mention the steady drip, drip, drip of water leaking through the ceiling in places.");
		output("`^`n`nThere is another staircase, hidden in a tucked away corner leading down into an inky black `6cellar`^. Do you have the courage to descend into the bowels of the house?");
		output("`^`n`nYou spot an aged door frame with a wicker door barely hanging off it's hinges. There is mud tracked in, with what appears to be blood caked into it. Maybe it leads to a `6garden`^?`n`n");
		require_once("lib/commentary.php");
		addcommentary();
		viewcommentary("TheHauntedHouse", "Several Spooky things happen", 30, "screams");
		addnav("Explore the House");
		addnav("Go Upstairs","runmodule.php?module=hauntedhouse&op=hauntedfloor2");
		addnav("Go to the Cellar","runmodule.php?module=hauntedhouse&op=hhbasement");
		addnav("Spooky Gardens","runmodule.php?module=hauntedhouse&op=hauntedhousegarden");
		addnav("Exit the Haunted House");
		addnav("Return to the Gates","runmodule.php?module=hauntedhouse&op=hauntede");
	}
	
	if ($op == "hauntedhousegarden"){
		page_header("The Haunted House Gardens");
		output("`b`$`cThe Dead Garden`c`b`n`n`^Steeped in shadow, the darkness echos and folds inside itself as all sunlight is absorbed completely by the trees. Large mammoths of foliage...dead branches, long and spindly, wave in the air, veining like capillaries... the bark flaking off in spots, diseased with a forging moss that stewed for years and ate its way into the tree's innards, killing from the inside out. Brown crispy leaves litter the grounds and dance macabre on light feet, smelling of fungus and autumnal-moisture.`n`n");
		output("`^You can feel the squish of rotten vegetables as you make your way through the gardens. There are many directions you can go. Nearby is an enclosed `6Cemetary `^that seems to contain generations of the family that once possessed the house. Want to investigate?");
		output("`n`n`^Off in the distance, there is a `6patch of pumpkins`^ that seems to be doing well... But then you realize it must have a caretaker... Want head off in it's direction?`n`n");
		require_once("lib/commentary.php");
		addcommentary();
		viewcommentary("hauntedgarden", "Several People Scream,", 30, "screams");
		addnav("Little Cemetary");
		addnav("Read Gravestones","runmodule.php?module=gravestoneshh&op=gravestonemodule");
		addnav("Go Elsewhere");
		//addnav("Halloween Party","runmodule.php?module=halloweenmaskedballhauntedhouse"); -finish later-
		addnav("Pumpkin Patch","runmodule.php?module=pumpkinhouse");
		//addnav("The Corn Maze","runmodule.php?module=xxx"); //********** To be finished later CORN MAZE to be finished later ***********//
		addnav("Return to the House","runmodule.php?module=hauntedhouse&op=hauntedhousemain");
	}
	
	//hauntedfloor2 contains trickortreathh and Applebob
	
	if ($op == "hauntedfloor2"){
		page_header("The Haunted House Second Floor");
		output("`^You cringe at each creak on the old warped stairs, but it doesn't sway your determination to make it to the second floor. Halfway up, a shadow flickers at the corner of your vision. You freeze, and as you stand there, you catch a woody scent lingering in the air. Tobacco smoke? Fire? A shiver curls through the hairs on the back of your neck then cascaded down down your backbone. It's all you can do not hurl yourself back down the stairs and out the front door.`n`n");
		output("Turning back forward, you creep to the top. The shimmering firelight of your torch flickers over a set of doors positioned at separate points in a long, dark hall way.. You reach out and test them...one is unlocked, a pool of wetness seeping from under the door...the second locked.. It's wood almost pulsing under your fingertips...which will you choose?");
		output("`^`n`nThere is an `6unlocked door`^ to the right, a little down the hallway. A little pool of water seeps from the crack under the door. Want to investigate the moisture?");
		output("`^`n`nAt the very end of the shady hall way there is `6locked door`^ with a little light coming from the cracks around the door frame. This door's lock looks formidable. Do you want to knock on it?");
		addnav("Explore the House");
//		addnav("Open the Unlocked Door","runmodule.php?module=applebobhh");
		addnav("Knock on the Locked Door","runmodule.php?module=trickortreathh&op=knockdoor");
		addnav("Go Downstairs","runmodule.php?module=hauntedhouse&op=hauntedhousemain");
	}
	
	//trickortreat!
	
	if ($op == "hhbasement"){
		page_header("The Cellar of the Haunted House");
		output("`$`c`bThe Haunted Cellar`c`b`^`n`nYou slowly step to the cellar door, hand trembling as you circle the door knob..turn.. and push it open. You peer down into the inky blackness below, wondering when a twisted headed person will crawl out and have you for dinner!");
		output("`^You summon your strength and tiptoe your way down the stairs. Each step intensified by moaning and creaking, as if the steps could collapse at any moment.");
		output("`^ A musty, dank order creeps into your nose. The house is dead silent except for intermittent creaks and moans. In a burst of panic, you fumble for a light switch. You flip it up and down frantically, but the room remains immersed in darkness.");
		output("`^`n`nFear settles in and deep down you know you're not alone in the dark. Something brushes your back. You turn, but there is nothing. Nothing you can see, that is. Outside you can hear the autumn wind howling, and it almost sounds like laughter to your panicked mind. A low chuckle breaks your thought process, directly in front of you. You scream, but hear nothing in the overwhelming blackness. You're being drawn in, drowned in slumbering evil, there is no escape...");
		output("`^`n`nThere is a room off to the left with a glowing blue light and a deep thrumming sound coming from it. On the wall over the door there is a picture of a `6rock, a peice of paper, and a pair of scissors`^. You should probably investigate it.");
		addnav("Explore the Cellar");
		addnav("The Left Room","runmodule.php?module=hauntedhouse&op=hbleftroom");
		addnav("Return");
		addnav("Go Upstairs","runmodule.php?module=hauntedhouse&op=hauntedhousemain");
		modulehook("hhbasement-navs");
	}
	
	if ($op == "hbleftroom"){
	page_header("Casper's Games");
	output("`^A small but friendly looking ghost floats in the corner of the room, seemingly afraid of you.");
	addnav("Approach the Ghost");
	addnav("Speak To Casper","runmodule.php?module=casperhh&op=playgame");
	addnav("Return to the Basement");
	addnav("Leave","runmodule.php?module=hauntedhouse&op=hhbasement");
	}
	
page_footer();
}
?>
