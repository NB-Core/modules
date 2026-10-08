<?php

function sichshop_getmoduleinfo(){
$info = array(
    "name"=>"Sichae's Apple Shop",
    "version"=>"2.0",
    "author"=>"Chris Vorndran",
    "download"=>"http://dragonprime.net/users/Sichae/sichshop.zip",
	"vertxtloc"=>"http://dragonprime.net/users/Sichae/",
	"description"=>"Allows a user to purchase an apple, that will boon the player randomly.",
    "category"=>"Village",
    "settings"=>array(
		 "Sichae's Apple Shop Settings,title",
		 "village"=>"Name of Village where Sichae settled down,text|Degolburg",
		 "mult"=>"Multiplier of Level to produce cost,int|20",
		 "eatsallowed"=>"Times players are allowed to eat per day,int|3",
		 "expgain"=>"Experience Multiplier,floatrange,0,1,.05|.1",
		 "Sichae's Apple Shop Location,title",
		 "shoploc"=>"Where does the shop appear,location|".getsetting("villagename", LOCATION_FIELDS),
	),
	"prefs"=>array(
		"Sichae's Apple Shop Preferences,title",
		"eatentoday"=>"Has player eaten today,int|0",
		)
	);
	return $info;
}
function sichshop_install(){
	module_addhook("changesetting");
	module_addhook("newday");
	module_addhook("village");
	return true;
}
function sichshop_uninstall(){
	return true;
}
function sichshop_dohook($hookname,$args){
	global $session;
	switch ($hookname){
		case "changesetting":
			if ($args['setting'] == "villagename"){
				if ($args['old'] == get_module_setting("shoploc")){
					set_module_setting("shoploc",$args['new']);
				}
			}
			break;
		case "newday":
			set_module_pref("eatentoday",0);
			break;
		case "village":
			if ($session['user']['location'] == get_module_setting("shoploc")){
				tlschema($args['schemas']['marketnav']);
				addnav($args['marketnav']);
				tlschema();
				addnav("Sichae's Apple Shop","runmodule.php?module=sichshop&op=enter");
			}
			break;
		}
	return $args;
}
function sichshop_run(){
	global $session;
	$op = httpget('op');
	$apple = httpget('apple');
	$village = get_module_setting("village");
	$et = get_module_pref("eatentoday");
	$ea = get_module_setting("eatsallowed");
	$c = ($session['user']['level']*get_module_setting("mult"));
	page_header("Sichae's Apple Shop");
	addnav("Navigation");
	villagenav();
	addnav("Actions");

	switch ($op){
		case "enter":
			output("`2You duck into a small alley, lit only by a small torch at the end.");
			output(" You walk over to this torch and read a small plaque.\"`6Sichae's Apple Shop`2\"`n`n");
			output("`2You open the door, and walk down a small set of stairs.");
			output(" You look all around and see a little counter.");
			output(" You walk up to the counter and ring a small bell.");
			output(" A beautiful woman walks out and stops right behind the counter.");
			output(" The mysterious woman looks at you, through stained-glass glasses.");
			output(" She smiles brightly and asks \"`6Hello there! Might I interest you in something today?`2\"`n");
			addnav("Peruse the Selection","runmodule.php?module=sichshop&op=apple");
			break;
		case "apple":
			if ($apple == "" && $et < $ea){
				output("`2 You look around and only see apples.");
				output(" Your mouth waters... \"`6Ah, so you WOULD like an apple...`2\" she says ominously, \"`6The question is, what KIND of apple would you like?...`2\"`n`n");
				output("You gaze upon the counter as five baskets appear.");
				output(" One carrying `4Red `2apples, one with `@Green.");
				output(" `2One with `7Black.");
				output(" `2One with `qBrown `2and the other with `1Blue`2.");
				output(" `2In a small glass case, you see a `&White `2one. `n`n");
				output("\"`6Which would you like my dearie, they each cost `^%s `6gold...?`2\"`n`n",$c);
				output("`2\"`6Or would you rather to hear a little bit about me?`2\"");
				addnav("Menu");
				addnav("Green Apple","runmodule.php?module=sichshop&op=apple&apple=green");
				addnav("Red Apple","runmodule.php?module=sichshop&op=apple&apple=red");
				addnav("Blue Apple","runmodule.php?module=sichshop&op=apple&apple=blue");
				addnav("Brown Apple","runmodule.php?module=sichshop&op=apple&apple=brown");
				addnav("White Apple","runmodule.php?module=sichshop&op=apple&apple=white");
				addnav("Black Apple","runmodule.php?module=sichshop&op=apple&apple=black");
			}elseif($et >= $ea){
				output("`2\"`6You are too full for more apples today!`2\"");
				output(" You rub your belly, thinking maybe you should not have eaten so much.");
				output(" You walk off, hoping you can lose the weight...");
			}else{
				if ($session['user']['gold'] >= $c){
					$session['user']['gold']-=$c;
					$et++;
					set_module_pref("eatentoday",$et);
					switch ($apple){
						case "green":
							output("`2You gaze upon the Green Apples and reach for one.");
							output("Sichae jams a pencil through your hand.");
							output("\"`6So, you would like a Green Apple?`2\" You nod to the Apple Wench.`n`n");
							output("`2You pull the pencil out of your hand, Sichae walks over and heals it instantly.");
							output("\"`6Just trying to scare you a little bit...`2\" Then she hands you a Green Apple.`n");
							output("`2You take a big bite out of it.");
							output("Your skin turns green for a bit.");
							sichshop_apples();
							break;
						case "red":
							output("`2You laugh at the sight of Red Apples and reach for one.");
							output("Sichae slams her hand on top of yours. `n`n");
							output("\"`4I have seen red apples before, there is nothing special to them.`2\"`n`n");
							output("\"`6So, do you want one or not?`2\" You nod to the Apple Wench.`n`n");
							output("`2You nurse your hand in ice, as a Red Apple is tossed to you.");
							output("You take a big bite out of it and your skin turns red for a bit.");
							sichshop_apples();
							break;
						case "blue":
							output("`2You giggle at the sight of Blue Apples and reach for one.");
							output(" Sichae draws her knife and presses it to your throat.");
							output("\"`6So, are you the little rat that has been stealing my apples?`2\"");
							output(" You jump back a bit, then shake your head.`n");
							output("`2Sichae looks at you and smiles as she puts her knife back in her pocket,`n`n");
							output("`2You rub your neck, trying to rid the pain as a Blue Apple is tossed to you.");
							output(" You take a big bite out of it. Your skin turns blue for a bit.");
							sichshop_apples();
							break;
						case "brown":
							output("`2You begin to feel qualmish at the sight of Brown Apples and take a step back.");
							output(" Sichae appears behind you and whispers into your ear.");
							output("\"`6So, you choose this lonely path...Yet, you do not accept your fate...You still don't want this fine apple...`2\" You shake your head.`n`n");
							output("`2You feel very calm at the sound of her voice as a Brown Apple is tossed to you.");
							output(" You take a big bite out of it.");
							output(" Your skin turns brown for a bit.");
							sichshop_apples();
							break;
						case "white":
							output("`2You stare at a magnificent White Apple.");
							output(" You feel in awe of it's power. `n`n");
							output("`2Sichae appears next to you and speaks \"`6My, my, my, that is a work of art.");
							output(" It took me seven decades to perfect.");
							output(" The art of making a new breed of apple is painstaking.");
							output(" But, it is rewarding in the end.");
							output(" Would you like to eat this fine thing?`2\"`n");
							output("`2You feel utterly relaxed as she speaks to you.");
							sichshop_apples();
							break;
						case "black":
							output("`2You gasp as the sight of Black Apples and reach for one.");
							output(" Sichae pulls out a plunger and jams it onto your head. `n");
							output("\"`6So, you choose the apple that is the color of your heart...`2\" You snicker a bit.`n");
							output("`2Sichae pulls out her sword and cuts off the plunger, as well as a bit of hair...");
							output("\"`6You think it is funny? I can see your ugly heart...a monstrosity...`2\"`n");
							output("`2You begin to sweat, you can feel shivers run up and down your spine.");
							output(" A Black Apple is thrown into your mouth.");
							output(" You take a big bite out of it.");
							output(" Your skin turns black for a bit.");
							sichshop_apples();
							break;
					}
				}else{
					output("`2\"`6What do you think I am running, a soup kitchen?!`2\"");
					output("She points towards the door, beckoning you to leave.");
				}
			}
			addnav("Other");
			if ($et < $ea && $apple != "") addnav("Return to Menu","runmodule.php?module=sichshop&op=apple");
			addnav("Listen to Sichae","runmodule.php?module=sichshop&op=bio");
			break;
		case "bio":
			output("`2Sichae walks you into a small room, sits down and sets a chair for you.");
			output("`6Well, here we go...I was born into a small family living in the north.");
			output(" This family, was the Divine Bloodline from Lady Shiva, the Goddess of Ice.");
			output(" Each of her descendants were given her amazing abilities of Ice.");
			output(" Well, the family continued to prosper, the Kingdom of Ice they called it.");
			output(" They started many a war. This was all back, I don't know, maybe 35000 years.");
			output(" Well as you can tell, we were given powers by Blood.");
			output(" But, as the family kept multiplying, each new generation was given less and less power...\"`n`n");
			output("`2Sichae wipes her brow and continues \"`6By the time that I was born, I should've been given almost no power.");
			output(" Yet the amazing thing, it was almost like Shiva was reborn inside of me.");
			output(" I had full mastery over Ice. I was meant to accede the Throne and reclaim the country.");
			output(" Only problem, was that my father abused my powers.");
			output(" He forced me to start and finish wars for him.");
			output(" So, having no one to turn to, I fled the country.");
			output(" I headed south to %s. My first night there, I was attacked in my sleep, by a Miss Mystic.",$village);
			output(" She changed me into a little Vampire.");
			output(" I was glad to finally belong.");
			output(" Later on, I was accepted into the grand Night Mask clan.");
			output(" But, after a little run in with the law, I was thrown out.");
			output(" I joined several different clans after that, even becoming an Officer in a Pirate Crew.");
			output(" But, I settled down and moved back in with the Saints.\"`n`n");
			output("`2Sichae grasps your hand and continues with the story \"`6Well, after all of this, I began searching to reclaim my old title.");
			output(" I searched forever, to find a space to set my new rigging up.");
			output(" I finally found some comfort with a man called Murph.");
			output(" He allowed me to use a little of his space.");
			output(" Since then, I have started up my own little community.");
			output(" Although, I did have a run-in with a Robot and a Family of foxes.");
			output(" But, 'tis another story. `^Thanks for Listening!\"`n");
			break;
		}
	page_footer();
}
function sichshop_apples(){
	global $session;
	$apple = httpget('apple');
		switch (e_rand(1,6)){
		case 1:
			$deathoverlord=getsetting('deathoverlord','`$Ramius');
			$session['user']['maxhitpoints']++;
			output("`n`n`2This apple actually tastes good, despite it's rotten appearance.");
			output(" You feel a sudden strike of fear, as %s`2 appears in your mind.",$deathoverlord);
			output(" You double over vomiting, a faerie comes forth and fends off %s`2.",$deathoverlord);
			output(" She taps you on the head, and you feel a bit more healthy...");
			output("`n`n`^You gain `@1 `^Maximum Hitpoint!");
			debuglog("gained 1 maxHP from a $apple apple");
			break;
		case 2:
			$gold = e_rand(1,500);
			$session['user']['gold']+=$gold;
			output("`n`n`2You fall into a deep sleep, and bang your head against a tree stump on the way down. `n`n");
			output("`2You awake with a note attached to you, it reads: \"`6Ok, to dismiss the thought of a lawsuit, I put `^%s `6gold into your satchel...`2\"",$gold);
			output("You get up and look around.");
			output(" You see no trace of Sichae...");
			debuglog("gained $gold gold from a $apple apple");
			break;
		case 3:
			$ch = e_rand(1,4);
			output("`n`n`2You look upon your skin as it turns a different color.");
			output(" You feel your body begin to re-graft itself.");
			output(" You can feel your face changing...");
			output("`^You gain `%%s `^Charm!",$ch);
			debuglog("gained $ch charm from a $apple apple");
			$session['user']['charm']+=$ch;
			break;
		case 4:
			$gems = e_rand(1,3);
			$session['user']['gems']+=$gems;
			output("`n`n`2Your Eyes begin to sparkle...literally.");
			output(" You can feel your eyes begin to harden.");
			output(" You look all around you and see a small light.");
			output(" `^You reach for the light and you get `5%s `^%s.",$gems,translate_inline($gems>1?"Gems":"Gem"));
			debuglog("gained $gems gem from a $apple apple");
			break;
		case 5:
			$expgain = round($session['user']['experience']*get_module_setting("expgain"));
			output("`n`n`2Your mind begins to fog up.");
			output(" You start having flash backs to a simple time of warriors.`n`n");
			output("`^Witnessing these flashback, you gain `#%s `^Experience!",$expgain);
			$session['user']['experience']+=$expgain;
			debuglog("gained $expgain experience from $apple apple");
			break;
		case 6:
			$turns = e_rand(1,3);
			$session['user']['turns']+=$turns;
			output("`n`n`2You feel in tune with nature.");
			output(" You feel as if you can face more forest creatures.");
			output("`^You gain `@%s `^%s!",$turns,translate_inline($turns==1?"Turn":"Turns"));
			debuglog("gained $turns turns from a $apple apple");
			break;
	}
}
?>
