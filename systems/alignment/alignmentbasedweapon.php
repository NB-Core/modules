<?php
function alignmentbasedweapon_getmoduleinfo(){
	$enum = "00,lawful-good,01,lawful-neutral,02,lawful-evil,10,neutral-good,11,neutral-neutral,12,neutral-evil,20,chaotic-good,21,chaotic-neutral,22,chaotic-evil";
	$info = array(
		"name"			=>	"Weapons based on alignment",
		"author"		=>	"Christian Rutsch for shinobilegends.com",
		"category"		=>	"Forest Specials",
		"download"		=>	"",
		"description"	=>	"Based on the current alignment the character may eventually come into possession of a powerful wepaon.",
		"prefs"			=>	array(
			"Alignment Based Weapons - Preferences,title",
				"hasweapon"			=>	"This user has an alignment-based weapon,bool|0",
				"typeofweapon"		=>	"Type of weapon?,enum,$enum|11",
				"meditatedtoday"	=>	"Has already meditated?,bool|0",
				"gainedaweaponthisdk" => "Has gained a weapon this DK?,bool|0",
				"hitpointsafer"		=>	"Hitpoints before fight,viewonly|1",
		),
		"settings"		=>	array(
			"Alignment Based Weapons - Weapon Names,title",
				"lawful-good"		=>	"Name for the lawful-good weapon,string,50|Mighty Super Power Force",
				"lawful-neutral"	=>	"Name for the lawful-neutral weapon,string,50|Mighty Super Power Force",
				"lawful-evil"		=>	"Name for the lawful-evil weapon,string,50|Mighty Super Power Force",
				"neutral-good"		=>	"Name for the neutral-good weapon,string,50|Mighty Super Power Force",
				"neutral-neutral"	=>	"Name for the neutral-neutral weapon,string,50|Mighty Super Power Force",
				"neutral-evil"		=>	"Name for the neutral-evil weapon,string,50|Mighty Super Power Force",
				"chaotic-good"		=>	"Name for the chaotic-good weapon,string,50|Mighty Super Power Force",
				"chaotic-neutral"	=>	"Name for the chaotic-neutral weapon,string,50|Mighty Super Power Force",
				"chaotic-evil"		=>	"Name for the chaotic-evil weapon,string,50|Mighty Super Power Force",
			"Alignment Based Weapons - Chances,title",
				"chanceforest"		=>	"Chance to appear in the forest,range,0,100,1|100",
				"chancetravel"		=>	"Chance to appear during travels,range,0,100,1|50",
				"minlevel"			=>  "Minimum required level,range,1,15,1|5",
			"Alignment Based Weapons - Other Names,title",
				"oldmonk"			=>	"Name of the old monk,string,25|`#Old Sensei",
			"Alignment Based Weapons - Fight Options,title",
				"healthmultiplier"	=>	"Health multiplier,floatrange,0.8,2.5,0.1|1.4",
				"attackmultiplier"	=>	"Attack multiplier,floatrange,0.5,5,0.1|1.7",
				"defensemultiplier"	=>	"Defense multiplier,floatrange,0.5,5,0.1|1.7",
				"leveladd"			=>	"Level Modificator,range,-2,5,1|2",
				"exploss"			=>	"Loss of experience in percent for lost fight.,int|10",
		),
	);
	return $info;
}

function alignmentbasedweapon_test($type){
	if (get_module_pref("hasweapon", "alignmentbasedweapon")!=true){
		if (get_module_pref("gainedaweaponthisdk", "alignmentbasedweapon") == true) {
			return 0;
		} else {
			return get_module_setting("chance$type", "alignmentbasedweapon");
		}
	} else {
		return 0;
	}
}


function alignmentbasedweapon_install(){
	module_addeventhook("forest", "require_once('modules/alignmentbasedweapon.php'); return alignmentbasedweapon_test('forest');");
	module_addeventhook("travel", "require_once('modules/alignmentbasedweapon.php'); return alignmentbasedweapon_test('travel');");
	module_addhook("gardens");
	module_addhook("dragonkill");
	module_addhook("newday");
	module_addhook("modify-weapon");
	return true;
}

function alignmentbasedweapon_uninstall(){
	return true;
}

function alignmentbasedweapon_dohook($hookname, $args){
	switch ($hookname) {
		case "modify-weapon":
			if (get_module_pref("hasweapon")==true) {
				$args['skip'] = true;
				$args['unavailable']=true;
			}
			break;
		case "dragonkill":
			set_module_pref("hasweapon", false);
			set_module_pref("gainedaweaponthisdk", false);
			break;
		case "newday":
			set_module_pref("meditatedtoday", false);
			break;
		case "gardens":
			$type = get_module_pref("typeofweapon");
			$usertype = alignmentbasedweapon_usertype();
			debug("Type: $type - User: $usertype");
			addnav("Meditation Shrine");
			addnav("Go to the Shrine", "runmodule.php?module=alignmentbasedweapon");
			break;
	}
	return $args;
}

function alignmentbasedweapon_runevent($type, $from) {
	global $session;

	$op = httpget('op');
	$session['user']['specialinc'] = "module:alignmentbasedweapon";
	switch($op){
		case "":
			$align = e_rand(0,2);
			$demeanor = e_rand(0,2);
			$type = $demeanor . $align;
			debug("Align: $align - Demeanor: $demeanor");
			debug("User: ".alignmentbasedweapon_usertype());

			$goodfog = translate_inline("white");
			$neutralfog = translate_inline("grey");
			$evilfog = translate_inline("black");

			$goodflash = translate_inline("icy");
			$neutralflash = translate_inline("watery");
			$evilflash = translate_inline("lightning");

			output("`c`bA Clearing`b`c`n");
			output("`2Deep in the glades you come across a clearing.");
			output("Centered in a ray of sparkling sunlight you see a stone.");
			output("It's made of the purest marble you have ever seen - especially this far from the villages.");
			output("Stuck in the middle of the stone you recognize a mighty sword, engulfed in %s fog.", $align==0?$goodfog:($align==1?$neutralfog:$evilfog));
			output("You blink as your eyes realize the %s flashes which draw a fragile web through the fog, just to vanish and reappear every now and then.",$demeanor==0?$goodflash:($demeanor==1?$neutralflash:$evilflash));
			output("`n`n`&What will you do?");
			addnav("What will you do?");
			addnav("I'll draw the sword.", $from."op=draw");
			addnav("I'd rather leave.", $from."op=leave");
			$session['user']['specialmisc'] = $type;
			break;
		case "leave":
			$session['user']['specialinc'] = '';
			$session['user']['specialmisc'] = '';
			output("`2Shaking your head at this most wondrous encounter you head back to where you wanted to go before.");
			output("Soon you have forgotten about the clearing.");
			break;
		case "draw":
			output("You step up the few stairs to the stone.");
			output("Both your hands take a firm grip at the hilt of the sword.");
			output("Thinking of the might with which this sword must have been driven into the stone, you activate the most hidden reservoirs of power you may have.");
			output("The fog and the flashes gently disappear while you try to pull out the sword.");
			$usertype = alignmentbasedweapon_usertype();
			$type = $session['user']['specialmisc'];
			debug("Type: $type - User: $usertype");
			if ($type != $usertype || $session['user']['level'] < get_module_setting("minlevel")) { // fail
				output("But nothing happens.");
				output("The sword doesn't move a single inch.");
				output("It seems as if this sword wasn't made for you.");
				addnav("Leave...");
				addnav("... the clearing.", $from."op=leave");
				break;
			} else {
				output("Sparks and cracklings can be seen and heard while you slowly move the sword out of the stone.");
				output("The hazy fog, which was disappearing just a moment ago, now forms itself into the ghostly figure of an old monk.`n`n");
				output("\"`3My name is %s`3, young traveller.`2\" you hear his shattering voice from within your head.", get_module_setting("oldmonk"));
				output("\"`3Thou seekest to gain advantage from this weapon.");
				output("But before we can allow this, thou ought to prove worthy!`2\"");
				output("The ghost draws a katana and launches an attack on you...`n`n");
				$badguy = array(
					"creaturename"		=>	get_module_setting("oldmonk"),
					"creatureweapon"	=>	translate_inline("`#Ghostly Katana`0"),
					"creaturehealth"	=>	round($session['user']['maxhitpoints'] * e_rand(10,get_module_setting("healthmultiplier")*10)/10,0),
					"creatureattack"	=>	ceil($session['user']['attack'] * get_module_setting("attackmultiplier")),
					"creaturedefense"	=>	ceil($session['user']['defense'] * get_module_setting("defensemultiplier")),
					"creaturelevel"		=>	min(1, $session['user']['level'] + get_module_setting("leveladd")),
					"type"				=>	"alignmentbasedweapon",
					"diddamage"			=>	false,
					"noadjust"			=>	true,
					"hidehitpoints"	    =>  true,
				);
				$session['user']['badguy'] = createstring($badguy);
				require_once("lib/battle-skills.php");
				suspend_buffs("allowinpvp", "Your buffs don't work against this figure.");
				if (file_exists("lib/extended-battle.php")) {
					require_once("lib/extended-battle.php");
					suspend_companions("allowinpvp", "Your companions barely recognize this figure at all. Seems like it can be seen only by yourself.");
				}
				$battle = true;
				$op = "fight";
				httpset("op", "fight", true);
				set_module_pref("hitpointsafer", $session['user']['hitpoints']);
			}
			// fallthrough
		case "fight":
			require("battle.php");
			if ($victory){
				$type = $session['user']['specialmisc'];
				$align = $type[0];
				$demeanor = $type[1];
				switch ($align) {
					default:
					case "0":
						$align = "lawful";
						break;
					case "1":
						$align = "neutral";
						break;
					case "2":
						$align = "chaotic";
						break;
				}
				switch ($demeanor) {
					default:
					case "0":
						$demeanor = "good";
						break;
					case "1":
						$demeanor = "neutral";
						break;
					case "2":
						$demeanor = "evil";
						break;
				}
				$weapon=get_module_setting("$align-$demeanor");
				output("`2Being close to dealing your final blow you hesitate just long enough to let the ghostly figure disappear in a shimmering flash of light.");
				output("All that remains is the echoing sound of voice in your mind.");
				output("\"`#Thou hast proven worthy, young traveller...`2\" it slowly fades away.");
				output("All you are left with now, is the sword from the stone.`n`n");
				output("`&You have gained %s`&.`n", $weapon);
				if (is_module_active('customeq')) {
					$name=get_module_pref('weaponname','customeq');
					if ($name!='') {
						output("`i`4Note: Your custom weapon `\$%s`4 won't be gone forever!`i`n`n",$name);
					}
				}
				addnews("%s`\$ was seen coming out of the forest of %s`\$ with %s`\$!",$session['user']['name'],$session['user']['location'],$weapon);
				set_module_pref("hasweapon", true);
				set_module_pref("gainedaweaponthisdk", true);
				set_module_pref("typeofweapon", $type);
				unsuspend_buffs("allowinpvp", "Your buffs work again.");
				if (file_exists("lib/extended-battle.php")) {
					require_once("lib/extended-battle.php");
					unsuspend_companions("allowinpvp", "Your companions are ready to move on.");
				}
				$session['user']['weapon'] = $weapon;
				$session['user']['attack'] -= $session['user']['weapondmg'];
				$session['user']['weapondmg'] = 16;
				$session['user']['attack'] += 16;
				$session['user']['weaponvalue'] = 0; // It's worth nothing for someone like MightyE.
				$session['user']['specialinc']='';
				debuglog("has won against a ghostly figure and gained `^$weapon`0. (alignmentbasedweapon.php)");
				if(e_rand(0,1)) {
					$session['user']['hitpoints'] = get_module_pref("hitpointsafer");
				} else if ($session['user']['hitpoints'] < 1) {
					tlschema("forest");
					output("With your dying breath you spy a small stand of mushrooms off to the side.");
					output("You recognize them as some of the ones that the healer had drying in the hut and taking a chance, cram a handful into your mouth.");
					output("Even raw they have some restorative properties.`n");
					$session['user']['hitpoints'] = 1;
					tlschema();
				}
			} elseif($defeat){
				output("`2Just as you are about to collapse, the ghostly figure vanishes from your view.");
				output("All that remains is the echoing sound of voice in your mind.");
				output("\"`#Thou hast not been proven worthy...`2\" it slowly fades away.`n`n");
				output("Then, your vision blanks out and you feel yourself lifted to the realms of %s`2.`n`n", getsetting("deathoverlord","`\$Ramius"));
				output("`4You are dead.`n");
				output("`4You lost %s percent of your experience.`n", get_module_setting("exploss"));
				output("`4You lost all your gold.`n");
				output("`4You may continue fighting tomorrow.");
				$exploss = get_module_setting("exploss");
				$loss = round($session['user']['experience'] * $exploss / 100,0);
				debuglog("lost $loss exp when losing against a ghostly figure. (alignmentbasedweapon.php)");
				$session['user']['experience'] -= $loss;
				$session['user']['gold'] = 0;
				$session['user']['hitpoints'] = 0;
				$session['user']['alive'] = false;
				$session['user']['specialinc']='';
				addnews("%s`\$ was destroyed by %s`\$.",$session['user']['name'],get_module_setting('oldmonk'));
				addnav("Daily News", "news.php");
			} else {
				require_once("lib/fightnav.php");
				fightnav(true, false);
			}
			break;
	}
}

function alignmentbasedweapon_run() {
	global $session;
	$op = httpget('op');
	page_header("A Small Shrine");
	switch($op) {
		case "":
			output("`vA range of beautiful flowers bathes the shrine into soft and lively colors.");
			output("Lined by plum trees a small path of white pebbles leads to the shrine sanctum.");
			output("Several students can be seen contemplating over holy lore and ancients wisdoms.`n`n");
			addnav("Options");
			if(get_module_pref("hasweapon") && !get_module_pref("meditatedtoday") && $session['user']['turns'] > 0){
				addnav("Meditate on your weapon", "runmodule.php?module=alignmentbasedweapon&op=meditate");
				output("You may join the students, contemplating over the fate and glory your weapon might lead you to.");
			} else {
				output("You feel the need to leave this place as you do not wish to disturb the meditations of the other students.");
//				if (get_module_pref("hasweapon") && get_module_pref("meditatedtoday")){
//					addnav("Meditate on your weapon", "");
//				}
			}
			break;
		case "meditate":
			output("`vUpon entering the shrine, you devoutly ascend a set of stairs moving from daylight to the dimly-lit, cave-like inner sanctuary.`n`n");
			output("Located near the rear side of the gardens, this place engulfs you in peace and silence needed for the deeper meditations.`n`n");
			output("You start to contemplate on your last night's dreams when suddenly your weapon, lying in front of you on your knees, starts to hum a sound strange to your ears.");
			$weapontype = get_module_pref("typeofweapon");
			$usertype = alignmentbasedweapon_usertype();
			$session['user']['turns']--;
			debuglog("lost a turn for meditating in the garden. (alignmentbasedweapon.php)", false, false, "turns", -1);
			if ($weapontype != $usertype) { // shatter the weapon
				output("`\$You barely can stand the asynchronous and disharmonic noises, amplifying more and more.");
				output(" With every ongoing second its sounds become more painful, but you cannot stop it.");
				output(" In panic you look around, but no one besides you seems to hear these noises.");
				output("`n`nAnd then...");
				output("`n`nSilence...");
				output("`n`nNothingness...");
				output("`n`nBeautiful blackness...");
				output("Only interrupted by a shattering sound.");
				output("Like glass being hit by a stone.");
				output("You slowly open your eyes, just to find your `^%s`\$, lying there - broken into myriads of tiny pieces.", $session['user']['weapon']);
				$session['user']['attack'] -= $session['user']['weapondmg'];
				$session['user']['weapondmg'] = 0;
				$session['user']['weapon'] = translate_inline("Fists");
				$session['user']['hitpoints'] = 1;
				set_module_pref("hasweapon", false);
				set_module_pref("typeofweapon", "");
				debuglog("lost their weapon due to incompatible alignment / demeanor (should have been $weapontype but was $usertype). (alignmentbasedweapon.php)");
			} else { // gimme buffy
				output("`vUnheard harmonies and spheric melodies join each other in your mind and form a pattern of cosmical understanding new to you.");
				output("You hear the soft voice of %s`v, speaking to you.", get_module_setting("oldmonk"));
				// okay... we'll try something like that. The demeanor causes modifications of the atkmod/defmod
				// the alignment causes damage vs. minioncount.
				$buff = array(
					"rounds"=>e_rand(20,25),
					"schema"=>"module-alignmentbasedweapon"
				);
				switch($weapontype[0]){ // demeanor
					case "0": // lawful
						$buff['atkmod'] = 1;
						$buff['defmod'] = 1.3;
						break;
					case "1": // neutral
						$buff['atkmod'] = 1.15;
						$buff['defmod'] = 1.15;
						break;
					case "2": // chaotic
						$buff['atkmod'] = 1.3;
						$buff['defmod'] = e_rand(80,100)/100;
						break;
				}
				switch($weapontype[1]){ // alignment
					// minioncount has no real effect, if the average damage is the same.
					// It's just a cosmetic difference.
					case "0": // good, average damage: 1 * ((10+50)/2) = 30 - 1 * ((10+60)/2) = 35
						$buff['minioncount'] = 1;
						$buff['minbadguydamage'] = 10;
						$buff['maxbadguydamage'] = max(50,e_rand(40,60));
						$buff['effectmsg'] = "`&The spirit of your weapons urges to strike at `^{badguy}`& and deals `\${damage} damage`&.";
						break;
					case "1": // neutral, 25-35 rounds
						$buff['lifetap'] = 1;
						$buff['regen'] = "ceil(<level>/2);";
						$buff['effectmsg'] = "`7Your weapon slices through the air and you feel a little better as it hits `^{badguy}`7.";
						$buff['rounds'] += e_rand(5,10);
						break;
					case "2": // evil, average damage: 2 * ((0+15)/2) = 15 - 4 * ((0+20)/2) = 40
						$buff['minioncount'] = e_rand(2,4);
						$buff['minbadguydamage'] = 0;
						$buff['maxbadguydamage'] = e_rand(15,20);
						$buff['effectmsg'] = "`&The demons that have taken possession of your weapon burst out and attack `^{badguy}`&, dealing `\${damage} damage`&.";
						break;
				}
				switch ($weapontype) {
					case "00": // Lawful good
						$buff['name'] = "`&White Ice Strike";
						break;
					case "01": // Lawful neutral
						$buff['name'] = "`7Grey Ice Strike";
						break;
					case "02": // Lawful evil
						$buff['name'] = "`~Black Ice Strike";
						break;
					case "10": // neutral good
						$buff['name'] = "`&White Mist Aura";
						break;
					case "11": // true neutral
						$buff['name'] = "`7Grey Mist Aura";
						break;
					case "12": // neutral evil
						$buff['name'] = "`~Black Mist Aura";
						break;
					case "20": // chaotic good
						$buff['name'] = "`&White Lightning Hammerstrike";
						break;
					case "21": // chaotic neutral
						$buff['name'] = "`7Grey Lightning Flash";
						break;
					case "22": // chaotic evil
						$buff['name'] = "`~Black Lightning Bolt";
						break;
				}
				output("\"`#Brave traveller, thou has earned thee unrivalled power to aide you in thy journey...");
				output("Be blessed with the power of the %s`#...`v\", you hear him speaking before his voice fades away.", $buff['name']);
				debug($buff);
				apply_buff('alignmentbasedweapon', $buff);
			}
			set_module_pref("meditatedtoday", true);
			break;
	}
	addnav("Return...");
	addnav("Return to the gardens", "gardens.php");
	require_once("lib/villagenav.php");
	villagenav();
	page_footer();
}

function alignmentbasedweapon_usertype() {
	require_once("modules/alignment/func.php");
	if (is_evil()) {
		$user_align = "2";
	} elseif (is_good()) {
		$user_align = "0";
	} else {
		$user_align = "1";
	}

	if(is_chaotic()) {
		$user_demeanor = "2";
	} elseif(is_lawful()){
		$user_demeanor = "0";
	} else {
		$user_demeanor = "1";
	}
	return $user_demeanor . $user_align;
}
?>
