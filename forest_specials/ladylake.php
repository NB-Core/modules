<?php
/****************************************************ss*
  ladylake.php - The Lady of the Lake
  Created by Excalibur (www.ogsi.it) and Strider (Legendgard)

  Version: 1.02 (LoneStrider & Rowne) 12/17/2004
  - Fixed a case issue -Rowne
  - Added a few more details -Strider


  --------=Requires:  LotGD 0.9.8-----------------
  Just drop into your /modules/ folder and install

  Bonus: If you have an "Elf" race there are more
  options.

  ---------Description: ---------------------------
  Discover the sacred pool where the Lady of the Lake
  resides and possibly meet an elvish thief paying homage.

  ---------To Do: ----------------------------------
  Script is complete for the moment
  --------------------------------------------------

  / Version History:
  v1.01 	- Added new "Coin" section 	-Strider
  - Added Biting Faeries
  - Expanded the Story

  - Cleaned up messy output 	- Excalibur
  - Fixed a few errors	 	- Excalibur

  v1.00 	- Converted to LotGD 0.9.8   -Excalibur

  v0.2 (LotGD 0.9.7) -Modified and re-written by Strider
  - Complete re-write of the story with a touch of poetic license.
  - Cleaned up some of the code. - April 2004
  v.Alpha Created by Excalibur (www.ogsi.it) (for LotGD 0.9.7)

  ss********************************************ss************
  -Originally by: Excalibur
  -Contributors: Excalibur, Strider, Rowne
  ss********************************************ss************/
// December 2004  - Dragonprime Script Release (ss)

function ladylake_getmoduleinfo(){
	$info = array(
			"name"=>"LadyLake",
			"version"=>"1.02",
			"author"=>"Excalibur & Strider<br>of OGSI / Legendgard",
			"category"=>"Forest Specials",
			"download"=>"http://dragonprime.net/users/strider/ladylake.zip",

		     );
	return $info;
}
function ladylake_install(){
	module_addeventhook("forest", "return 100;");
	return true;
}
function ladylake_uninstall(){
	return true;
}
function ladylake_dohook($hookname,$args){
	return $args;
}
function ladylake_runevent($type)
{
	global $session;
	// This is a forest event and should only be run as such.
	$from = "forest.php?";
	$session['user']['specialinc'] = "module:ladylake";
	$op = httpget('op');
	switch($op){
		case "":
			output("`n `3You stagger through the woods until you hear the sound of crickets and the lapping of waves. ");
			output("There is a fog that hangs thick in the air and you cautiously measure your steps through the bracken. ");
			output("The land slopes down in and you feel the soil soften under your feet. Brushing aside a large clump ");
			output("of leaves, you gaze upon a beautiful lake. Ancient banyon trees drape their massive roots into the ");
			output("clear water where beautiful water lillies grow. The mist rises up from the gentle water as fireflies ");
			output("glitter in the boughs of surrounding trees. You have no doubt that this is a holy place. `n`n");
			output("`3 As you observe the beauty of this place, a spryte flutters above your head and says \"`6Mighty warrior, ");
			output("this pool is sacred to the fey and you must take care. Those who reflect on its surface may know their ");
			output("own charm, but might also tempt the `2Lady of the Lake`6 to cast her own attention upon you.`3\"`n`n");
			output("`&What will you do?");
			addnav("`@Reflect","forest.php?op=reflect");
			addnav("`@Toss in a coin","forest.php?op=coin");
			addnav("`\$Return to Forest","forest.php?op=lascia");
			$session['user']['specialinc'] = "module:ladylake";
			break;
		case "reflect":
			output("`3Your vanity draws you towards the surface of the lake to catch a glimpse of your reflection. ");
			output(" You gaze upon the surface of the pool, uncertain of what your appearance may tempt. ");
			$rand = e_rand(1,15);
			switch ($rand) {
				case 1:case 2: case 3: case 4: case 5:
					output("`3The `2Lady of the Lake`3 appears behind your reflection. Her voice is hauntingly beautiful ");
					output("as she tells you that you have `4%s`3 charm points.`n`n",$session['user']['charm']);
					output("`3Within moments, she vanishes into the depths of the water, leaving you to contemplate your own visage.`n`n");
					$session['user']['specialinc']="";
					break;
				case 6: case 7:
					output("`3The `2Lady of the Lake`3 appears behind your reflection. Her voice is hauntingly ");
					output("beautiful as she tells you that you have `4%s`3 charm points.`n`n",$session['user']['charm']);
					output("`3She fixes her gaze intently upon you and remarks, \"`6You are the most handsome warrior ");
					output("I've ever seen for many years! May your beauty never fade, fair one.`3\"`n");
					output("`3You hear a playful chime, some bright melody upon the wind and realize it is her own adoring ");
					output("laughter that fills your heart with light. Within moments, she vanishes into the depths of the water, ");
					output("leaving you to contemplate your own visage. You feel the grace of this place bless you.`n");
					output("`&You gain 2 charm points!!");
					debuglog("gain 2 charm points at the pool");
					$session['user']['charm']+=2;
					$session['user']['specialinc']="";
					break;
				case 8: case 9: case 10:
					output("`3The `2Lady of the Lake`3 appears behind your reflection. Her pale features capture your heart ");
					output("as she tells you have `4%s`3 charm points.`n`n",$session['user']['charm']);
					output("`3Then, she fixes her gaze intently upon your eyes and says, \"`6You are quite a handsome warrior! ");
					output("You should take care of yourself in these dark forests.`3\"`n");
					output("`3Within moments, she vanishes into the depths of the water, leaving you to contemplate your own visage. ");
					output("You feel the grace of this place bless you.`n");
					output("`&You gain 1 charm point!!");
					debuglog("gain 1 charm point at the pool");
					$session['user']['charm']++;
					$session['user']['specialinc']="";
					break;
				case 11: case 12: case 13:
					output("`n`n`3You see nothing but your own self gazing in the water. Expecting some Faerie Goddess ");
					output("to jump out of the lake, only to find nothing makes you feel rather silly. Puckishly, you begin ");
					output("to make faces at the water. Tis just a moment of fun to break up the hours of hunting evil ");
					output("creatures in the forest. You squint your eyes and stick out your tongue, watching your ");
					output("reflection mimic your own playful antics. `n`n`iThen, a hush falls over the forest and the ");
					output("`2Lady of the Lake`3 appears.`i Her eyes flare and her voice quakes the water's surface, ");
					output("dispelling your reflection. She says that you have `4%s`3 charm points with a note of distaste.`n`n",$session['user']['charm']);
					output("`3Then, she fixes her gaze intently upon you and remarks,`n\"`6I've seen worse faces in my life, ");
					output("but those were all goblins. Stare no longer upon my waters, you horrid thing.`3\".`n");
					output("`3She vanishes into the depths of the water, her anger sinking your heart and making you feel rather ugly!`n");
					if ($session['user']['charm']>0) {
						$session['user']['charm']-=1;
						debuglog("lose 1 charm point at the pool");
						output("`&You lose 1 charm point!!");
					}else {
						output("`&You are so ugly that the spell of the `2Lady of the Lake`& has no effect upon you!!");
					}
					$session['user']['specialinc']="";
					break;
				case 14: case 15:
					output("`n`n`3The `2Lady of the Lake`3 stares up from the water and a great wind knocks you over. ");
					output("A shrill voice tells you that you have `4%s`3 charm points.`n`n",$session['user']['charm']);
					output("`3The wind rages, whipping the trees surrounding the lake into a frenzy for falling leaves. ");
					output("You stumble backwards as waves of water begin to swell and the howling wind bites your cheeks. With depreciation she says:\"`6You hideous thing! How dare you gaze into these sacred waters! ");
					output("Begone!`3\".`n`3The winds calm down and the water once again becomes a tranquil pool. ");
					output("Your heart is crushed and you now feel more ugly than ever!`n");
					if ($session['user']['charm']>2) {
						$session['user']['charm']-=3;
						debuglog("lose 3 charm points at the pool");
						output("`&You lose 3 charm points!!");
					} else {
						switch ($session['user']['charm']) {
							case 0:
								output("`&You are already the ugliest person of the village that the spell of the `2Lady of the Lake`& doesn't have any effect on you!!");
								break;
							case 1: case 2:
								output("`&You are so ugly already that the spell of the `2Lady of the Lake`& can subtract only `^`b%s`b`& charm point!!",$session['user']['charm']);
								$session['user']['charm']=0;
								break;
						}
					}
					$session['user']['specialinc']="";
					break;
			}
			break;
		case "lascia":
			output("`3 You stare at lake's surface, and after few moments you decide that you don't have time to waste ");
			output("gazing into a pool of water.`n`3 Your hands are eager and your weapons are ready to face the dangers ");
			output("of the forest.`n ");
			if ($session['user']['turns']>0) {
				output("`n`6 You turn back to the forest, knowing that you've lost `^1 turn`6 from wandering about the lake shore.");
				$session['user']['specialinc']="";
				$session['user']['turns']-=1;
			}
			break;
		case "coin":
			if ($session['user']['gold']<1) {
				output("`3 You search your pockets for a single gold coin to toss into the pool of water. ");
				output("Unfortunately, all you find is pocket lint and a half-eaten piece of orcish jerky. ");
				output("Taking a bite of the horrid jerky, you turn away. At least you won't starve. ");
				output("For now, you need to earn some gold in the forest.`n");
				output("`6 Sadly, you turn away, knowing that you've lost `^1 turn`6 ambling about the lake shore.");
				$session['user']['specialinc']="";
				if ($session['user']['turns']>0)
					$session['user']['turns']-=1;
			}else{
				output("`n`3 You reach into your pocket and pull out a single gold coin. ");
				output("Tossing it into the pool, you whisper a silent wish for luck and good fortune in this realm. ");
				output("Your coin breaks the surface of the pool and you watch as the ripples form.`n`n");
				if ($session['user']['turns']>0)
					$session['user']['turns']-=1;
				$session['user']['gold']-=1;
				$rand = e_rand(1,4);
				switch($rand){
					case 1:
						output("`3The ripples grow and form small waves on the shore of the pool. ");
						output("You see something glittering on the bank and bend down for closer inspection. ");
						output("Fortune smiles indeed, you've found a `%Gem`3!`n");
						output("`6 You turn away, knowing that you've lost `^1 turn`6 for tossing the ");
						output("`^Gold piece`6 into the lake and gained `%1 gem`6.");
						$session['user']['gems']+=1;
						$session['user']['specialinc']="";
						break;
					case 2:
						output("`3Your heart sighs at the beauty of this place as the ripples ");
						output("lap softly upon the sand of the pool. A shaft of light eminates from the pool ");
						output("and the `2Lady of the Lake`3 rises from the water. Her voice is hauntingly ");
						output("beautiful as she tells you that you have `4%s`3 charm points.`n`n",$session['user']['charm']);
						output("`3She fixes her gaze intently upon you and whispers, \"`6You have pleased us this day, ");
						output("may the elements' grace shine upon you.`3\".`n");
						output("`3You hear sweet music in the air, some lucid reminder of dreams ");
						output("and you realize it is her own adoring laughter that fills your heart with light. ");
						output("Within moments, she vanishes into the depths of the water, leaving you to contemplate your own visage. ");
						output("You feel the grace of this place bless you.`n");
						apply_buff('faeriesong',
								array(
									"name"=>"`%Faerie Graces",
									"rounds"=>4,
									"wearoff"=>"The faerie grace fades...",
									"atkmod"=>2,
									"roundmsg"=>"You think you hear a faerie's laughter.",
									"schema"=>"module-ladylake",
								     ));
						$session['user']['specialinc']="";
						break;
					case 3:
						if (!is_module_active('thieves')) {
							output("Nothing happens except for a slight breeze coming through the meadows...`n`n");
							break;
						}
						output("`3As you stare at the ripples, hoping to have pleased the `2Lady of the Lake`3, ");
						output("you see something strange. Stepping into the moonlight is the infamous elvish thief, ");
						output("%s`3. You quietly hide behind a tree and watch as he kneels to the waters ",get_module_setting('lonestrider','thieves'));
						output("and whispers a silent prayer in the `#forgotten`3 tongue. ");
						output("Ceremoniously, he touches his fingers to the water, then stands.`n");
						output("A moment of silence passes and he turns around and casually says, ");
						if ($session['user']['race'] == get_module_setting('preferredrace','thieves')){
							output("`n`n`4\"I know you're watching me, my dear friend. Don't worry, I have no intention ");
							output("of robbing a fellow adventurer. This place is sacred, the spirit of the ");
							output("`#Lady`4 guides us even when the darkness threatens to engulf the forest itself. ");
							output("She is one of the true guardians of this forest and deserves our homage.\"");
							output("`3You listen carefully then watch as %s`3 stealthily takes his leave of you. ",get_module_setting('lonestrider','thieves'));
							output("You feel the grace of this place bless your %s blood.`n",get_module_setting('preferredrace','thieves'));
							output("`&You gain 1 charm point!!");
							debuglog("gain 1 charm point at the pool");
							$session['user']['charm']++;
							$session['user']['specialinc']="";
						}else{
							output("`n`n`4\"I know you're watching me. Don't worry, I have no intention of spilling blood here. ");
							output("This place is sacred, the spirit of the `\$Lady`4 guides us even when the darkness threatens to ");
							output("engulf the forest itself. She is one of the true guardians of this forest and deserves our homage.\"`n");
							output("`3You listen carefully then watch as %s`3 brushes past and stealthily takes his leave of you. ",get_module_setting('lonestrider','thieves'));
							output("Once the rogue is gone, you realize one of your gem pouches are missing! ");
							output("%s `3must have snatched it when he brushed by you!`n`n",get_module_setting('lonestrider','thieves'));
							if ($session['user']['gems'] > 5){
								$gemloss = e_rand(1,4);
								output("%s`& stole `^%s gems`&!!",get_module_setting('lonestrider','thieves'),$gemloss);
								debuglog("lost %s gems at the pool",$gemloss);
								$session['user']['gems']-=$gemloss;
								$session['user']['specialinc']="";
							}else{
								output("`&Lucky for you, %s`& stole an empty pouch. ",get_module_setting('lonestrider','thieves'));
								output("That thief will probably be pretty disappointed when he realizes it.");
								$session['user']['specialinc']="";
							}
						}
						break;
					case 4:
						output("`n`n`3You see nothing but your own self gazing in the water. ");
						output("Maybe some good luck will come of it. Puckishly, you begin to make faces at the water. ");
						output("Tis just a moment of fun to break up the hours of hunting evil ");
						output("creatures in the forest. You squint your eyes and stick out your tongue, ");
						output("watching your reflection mimick your own playful antics. `n`n");
						output("`iThen, a hush falls over the forest and the `2Lady of the Lake`3 appears.`i ");
						output("Her eyes flare and her voice quakes the water's surface, dispelling your reflection. ");
						output("She says that you have `4%s`3 charm points with a note of distaste.`n`n",$session['user']['charm']);
						output("`3Then, she fixes her gaze intently upon you and remarks,`n");
						output("\"`6I've seen worst faces in my life, but those were all goblins. ");
						output("Stare no longer upon my waters, you horrid thing.`3\".`n");
						output("`3She vanishes into the depths of the water and a swarm of biting faeries ");
						output("rush out of the trees to chase you away!`n");
						$dkb = (int) log($session['user']['dragonkills']^2);
						apply_buff('faeries', array(
									"startmsg"=>"`n`^You are surrounded by a swarm of angry faeries!`n`n",
									"name"=>"`%Biting Faerie Swarm",
									"rounds"=>3,
									"wearoff"=>"The faeries flutter away.",
									"minioncount"=>$session['user']['level'],
									"mingoodguydamage"=>0,
									"maxgoodguydamage"=>1+$dkb,
									"effectmsg"=>"`\$A faerie `4bites you for `\${damage}`4.",
									"effectnodmgmsg"=>"`\$A faerie `4tries to bite you but `^MISSES`4.",
									"effectfailmsg"=>"`\$A faerie `4tries to bite you but `^MISSES`4.",
									"schema"=>"modules-ladylake",
									));
						$session['user']['specialinc']="";
				}
				break;
			}
	}
}
function ladylake_run(){
}
?>
