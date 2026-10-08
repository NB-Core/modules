<?php


function villain_getmoduleinfo(){
	$info = array(
		"name"=>"The Villain's Cave",
		"description"=>"The hero gets caught by the evil overlord and might have the chance to escape.",
		"version"=>"1.0",
		"author"=>"`7Christian Rutsch,`ninspired by `4Talisman`7,`nimproved by `2Elessa`0",
		"category"=>"Forest Specials",
		"download"=>"http://www.dragonprime.net/users/XChrisX/villain.zip",
		"settings"=>array(
			"forest"=>"Base chance in forest,range,5,100,5|100",
			"travel"=>"Base chance during travel,range,5,100,5|100",
			"excluded"=>"Account IDs that never get this event (comma separated),text|",
		),
	);
	return $info;
}

function villain_install(){
	module_addeventhook("forest", "return (in_array((int)\$session['user']['acctid'], array_map('intval', preg_grep('/^\d+$/', array_map('trim', explode(',', (string)get_module_setting('excluded', 'villain'))))), true)?0:get_module_setting('forest', 'villain'));");
	module_addeventhook("travel", "return (in_array((int)\$session['user']['acctid'], array_map('intval', preg_grep('/^\d+$/', array_map('trim', explode(',', (string)get_module_setting('excluded', 'villain'))))), true)?0:get_module_setting('travel', 'villain'));");
	return true;
}

function villain_uninstall(){
	return true;
}

function villain_dohook($hookname, $args) {
	return $args;
}

function villain_runevent($type, $from) {
	global $session;

	$session['user']['specialinc'] = "module:villain";
	$type = httpget('type');
	$op = httpget('op');
	$sql="SELECT sex,name,acctid FROM ".db_prefix('accounts')." WHERE superuser>0 AND (superuser!=".SU_IS_TRANSLATOR.") AND ((superuser & ".SU_NEVER_EXPIRE.")!=".SU_NEVER_EXPIRE.") AND acctid!=".$session['user']['acctid']." ORDER BY rand(".e_rand().") LIMIT 1;";
	$result=db_query($sql);
	$row=db_fetch_assoc($result);
	if (!isset($row['name']))
		$villain = "The Eternal Dread Ninja";
	else
		$villain = $row['name'];
	if (!isset($row['sex']))
		$gender = 0;
	else
		$gender = $row['sex'];
	$heshe=translate_inline(($gender?"she":"he"));
	switch ($op) {
		case "":
			if ($type == 'suicide' || $type == 'thrill') {
				output("`4You walk through the forest, cutting away the underbrush with your %s`4.", $session['user']['weapon']);
				output("You are in the right mood to kill any of those vicious creatures lurking %s", translate_inline($type=="forest"?"in the depth of the forest, ready to slay any uncautious adventurer.":"near the road, ready to attack any traveller nearby."));
				output("Your are quickly moving on your way so you don't recognize the shadow appearing from behind a tree and slowly approaching you from your blindside.");
				output("Suddenly you have this strange feeling, that the world is spinning around you and the last thing you see, is someone bending over you and strapping your fists to your back.");
			} else {
				output("`6Cautiously you step through the forest, carefully observing every movement and sound.");
				output("With every sudden, unexpected noise you jump and only after close investigation you feel relieved enough to continue your search for one of those fabulous creatures living in this forest.");
				if($session['user']['dragonkills'] < 5) {
					output("You are going to be a great hero, that is sure.");
					output("And you are going to kill a lot of those creatures to become such a hero.");
					output("But you won't let yourself get surprised in any way, no.");
				} else {
					output("You have slain the dragon many times, but still you think, 'Better safe than sorry'.");
					output("And so you turn every leaf and every stone in the hope that you won't get surprised by any of those malevolent critters living here.");
				}
				output("But with you being so focussed on caring for your safety it is no problem for this shadow to appear behind you and send you to the vale of dreams.");
			}
			addnav("Continue", $from."op=step1");
			break;
		case "step1":
			output("`5You open your eyes, the pain errupting in your head is almost overwhelming.");
			output("You try to lift your hands to your head, when you realize that you are tied to a table and feel yourself unable to move.`n`n");
			output("Looking around you find that you are within a cave. ");
			output("Torchlight is dancing on the walls and strange noises can be heard coming from the dark corners.");
			output("You tilt your head to the side to take a glimpse of what is going on in here, when you see a small creature appearing.");
			output("It is definitely too ugly for a troll, too small for a dwarf and too strange for an elf, but what it is you cannot tell.`n");
			output("As this `ithing`i notices that you are awake, it turns to you and begins to speak.`n`n");
			output("\"`7I am %s`7 The Great.",$villain);
			output("I will be the ruler of this world. Soon!`6\"");
			output("With a really annoying giggling %s continues, \"`7But for this to come to fruition, it takes only a little sacrifice.",$heshe);
			output("And that where my plans involve... YOU!`6\"`n`n");
			output("You cannot believe what you are hearing. This squabbly little thing is going to succeed in taking over the entire world. ");
			output("And you have no chance to stop him.`n");
			output("But %s continues, \"`7But before I can continue the `&Codex of the Villain, Annex B Chapter 6`7 tells me, I have to introduce you to my evil plan.",$heshe);
			output("So, let's get this nonsense behind us.`6\"`n`n");
			output("And so it starts his liturgy about his almighty evilness and after a while... `^");
			switch (e_rand(1,4)) {
				case 1: // loose bonds
					output("you recognize, that the bonds which kept you to the table have become loose.");
					output("Fortunately %s`6 is so absorbed in telling you, what %s will do after taking over the world that he does not notice your escape.",$villain,$heshe);
					output("`n`n`&During your stay you lost one forest fight.");
					$session['user']['turns']--;
					debuglog("lost a turn at the villain's cave.");
					$session['user']['specialinc']='';
					break;
				case 2: // challenge him
					output("%s finishes talking about its plans.",$heshe);
					output("You have looked for every single opportunity to escape but found nothing.");
					output("Then, you see your last chance.");
					output("Raising your voice you yell at %s`7, \"`3Hey, evil overlord, ruler of the world to-be! ",$villain);
					output("If you are so smart and almighty, why not battle me in a hand to hand contest?`6\"`n`n");
					output("You would have never thought, %s`6 would ever consider your suggestion but %s seems rather willing to let you contest him.",$villain,$heshe);
					output("So magically your bonds are loosed and you prepare to fight the thing.");
					output("Then you see some sort of device, %s`6 seems to use for its plans.",$villain);
					output("Some levers and buttons are used to operate this device. ");
					output("And one is labelled in big red letters: `\$DO NOT PRESS`6.");
					output("This must be the self-destruct button! ");
					output("You start circling around %s`6 and slowly advance to his machine, ready to set this to an end.",$villain);
					output("Aiming for a heavy blow, %s`6 loses its balance and this is the perfect moment for you to push the button.",$villain);
					output("\"`7Noooooooooooooo....`6\", you hear %s`6 screaming and almost instantly after you release the button a deep grumbling sound can be heard.",$villain);
					output("You head for the exit of this cave and with a last jump make it out of it closely followed by a cloud of dust.");
					output("Where once was the entrance to %s`6's cave, is now only debris and dirt.",$villain);
					output("`&It will surely take some time for you to forget this experience.`n`n");
					$bonus = max(500, round($session['user']['experience'] * e_rand(1,5)/100, 0));
					output("`n`n`&You gain %s experience.", $bonus);
					$session['user']['experience'] += $bonus;
					debuglog("gained $bonus exp at the villain's cave.");
					require_once("lib/addnews.php");
					addnews("`^%s`5 stopped the plans of %s`5.", $session['user']['name'],$villain);
					$session['user']['specialinc']='';
					break;
				case 3: // citizens free you
					output("you hear voices shouting from the entrance of the cave.");
					output("A struggling fight seems to be going on and it is coming closer.");
					output("Shinobi wearing black leather and with animal skins around their shoulders are fleeing.");
					output("Close behind them you spot a group of raging villagers, armed with pitchforks and torches hunting down %s`6's troups.",$villain);
					output("You rip at your bonds, but they keep you securely in place.`n`n");
					output("Helplessly you have to watch the villagers destroying the device %s`7 wanted to use to take over the world.",$villain);
					output("After they have arrested %s`7 and its minions, the leader frees you and leads you to the forest.",$villain);
					output("You feel a little ashamed, that you had to be freed by a group of villagers instead of fighting this army alone.`n`n");
					$loss = e_rand(1,3);
					if ($loss > $session['user']['charm']) $loss = $session['user']['charm'];
					if ($loss) {
						output("You lose some charm.");
						$session['user']['charm'] -= $loss;
					} else if ($session['user']['gold']) {
						output("You hand all your gold to the villagers so they don't tell anyone they had to rescue you.");
						debuglog("lost all their gold when they were rescued by the villagers.");
						$session['user']['gold'] = 0;
					} else {
						require_once("lib/addnews.php");
						addnews("`^%s`5 had to be rescued by a group of villagers armed with forks and torches.", $session['user']['name']);
					}
					$session['user']['specialinc']='';
					break;
				case 4: // let him succeed
					output("it stops.");
					output("You are looking at him quizzically for you don't know, what will happen next, but %s`6 does not care for you.",$villain);
					output("Using some levers and buttons on its strange machine in the back you hear a hissing noise right above you.");
					output("Out of the dark a strange, crystalline object descends.");
					output("Flashes of light are dancing on its surface.");
					output("Unable to speak or move anymore you watch in terror what is happening right now, right above you.");
					output("The flashes stop dancing and start gathering at the tip of the object.");
					output("The last thing you can remember is the voice of %s`6, maniacly laughing and shouting, \"`7Finally!!!`6\"`n`n",$villain);
					output("`4You are dead.`n");
					output("`4All gold on hand has been lost!`n");
					output("`410% of experience has been lost!`b`n");
					output("You may begin fighting again tomorrow.");
					$session['user']['specialinc']='';
					$session['user']['experience'] = round($session['user']['experience'] * 0.9, 0);
					$session['user']['alive'] = false;
					$session['user']['gold'] = 0;
					$session['user']['hitpoints'] = 0;
					addnav("Daily news", "news.php");
					modulehook("villain_youdied",array());
					break;
			} // end e_rand()
			break;
	} // end $op
}

function villain_run(){
}
?>
