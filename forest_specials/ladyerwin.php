<?php
/*

This module is dedicated to my beloved rat, Lady Erwin. She was put to sleep on 1st of August 2005. She had three large tumors, wasn't able to walk normally and had a fever too. I am still sad about this, and so I tried to write something for her. After that poem, I thought about this module.


Oliver

V1.01 added the feed operation
V1.02 added the kick operation
V1.03 added some buff settings
V1.04 fixed some grammar and punctuation mistakes, big thanks to Elessa :-)
V1.05 fixed the permanent/temporary hp matter
v1.06 added one pref for the kick and mr. black
*/

function ladyerwin_getmoduleinfo() {
	$info = array(
		"name"=>"Lady Erwin - The White Rat",
		"author"=>"`2Oliver Brendel",
		"version"=>"1.06",
		"category"=>"Forest Specials",
		"download"=>"http://lotgd-downloads.com",
	"settings"=>array(
		"Lady Erwin - Preferences, title",
		"This module supports the alignment module,note",
		"maxgems"=>"Maximum amount of gems to win,floatrange,1,10,1|3",
		"maxgold"=>"Maximum amount of gold to win/lose,int|300",
		"maxhp"=>"Maximum amount of hp to gain (temporary/permanent according to your server settings),floatrange,1,10,1|3",
		"carrydk"=>"Do max hitpoints gained carry across DKs?,bool|1",
		"maxff"=>"Maxmimum amount of forest fights to gain/lose,floatrange,1,10,1|3",
		"suspendbuffs"=>"Does the protector suspend all buffs (those not allowed in training),bool|1",
		"bufflast"=>"How long do possible buffs last in rounds,floatrange,1,100|15",
		"buffsurvives"=>"Do buffs survive new days,bool|0",
		"protectorname"=>"Name of protector of the Lady,text|Oliver",
		"protectorweapon"=>"Name of the weapons of her protector,text|pure affection for the Lady",
		"protectorattack"=>"Attackmultiplicator based on PC-attack,floatrange,0,2,0.05|1.1",
		"protectordefense"=>"Defensemultiplicator based on PC-defense,floatrange,0,2,0.05|1.1",
		"protectorhp"=>"Hitpointsmultiplicator based on PC-hitpoints,floatrange,0,2,0.05|1.1",
		"experienceloss"=>"Percentage: How many experience is lost when player is killed by the protector,floatrange,1,100,1|10",
	),
	"prefs"=>array(
			"Lady Erwin Preferences,title",
			"kicked"=>"Tried to kick her once,viewonly|0",
		),
	);
	return $info;
}
function ladyerwin_install() {
	module_addeventhook("forest", "return 100;");
	return true;
}

function ladyerwin_uninstall() {
	return true;
}

function ladyerwin_dohook($hookname,$args) {
	return $args;
}

function ladyerwin_runevent($type,$link) {
	global $session;
	$from = "forest.php?";
	$session['user']['specialinc'] = "module:ladyerwin";
	$op = httpget('op');
	$bufflast = get_module_setting("bufflast");
	$maxhp = get_module_setting("maxhp");
	$buffsurvives = get_module_setting("buffsurvives");
	$suspendbuffs = get_module_setting("suspendbuffs");
	switch ($op) {
		case "":
			output("As you walk along a small track, you suddenly realize everything grew quiet around you.");
			if($session['user']['race']== 'Elf') {
				output("`@Your elvish senses tell you that something special and important closes in.");
			}
			output("`n`nAs you walk farther into the forest, you hear something move in a bush near you. You stop a moment and wonder what might come out,... and ready your weapon... just in case...");
			output("`n`nYou are quite surprised as a little white rat comes out of the bush. It begins to sniff towards your direction.");
			output("`n`nWhat will you do?");
			addnav("Pet the rat",$link."op=pet");
			addnav("Feed the rat",$link."op=feed");
			addnav("Kick the rat",$link."op=kick");
			addnav("Walk away",$link."op=walk");
			break;
		case "pet":
			$randomchance=e_rand(1,10);
			$session['user']['specialinc'] = "";
			if (is_module_active('alignment')) {
				$evilalign = get_module_setting('evilalign','alignment');
				$goodalign = get_module_setting('goodalign','alignment');
				$useralign = get_module_pref('alignment','alignment');
				if ($useralign <= $evilalign) $randomchance+=1;
				if ($useralign >= $goodalign) $randomchance-=1;
			}
			output("`@You carefully approach the little white rat. It doesn't seem to be very shy, and you can pet her easily. You suddenly realize that this rat might be `$ Lady Erwin`@, spoken of in many legends.`n`n");
			if ($randomchance<1) $randomchance=1;
			if ($randomchance>10) $randomchance=10;
			switch ($randomchance) {
				case 1:
					output("`$ Lady Erwin`@ is very pleased with your kindness.");
					if (is_module_active('alignment')) {
						require_once("./modules/alignment/func.php");
						align("5");
					}
					output(" She really seems to like you! You feel better than ever!");
					apply_buff('ladyerwinmastermind',
					array(
						"name"=>"`%Bless of Lady Erwin",
						"rounds"=>$bufflast,
						"wearoff"=>"Your shield ceases to function.",
						"invulnerable"=>1,
						"survivenewday"=>$buffsurvives,
						"roundmsg"=>"You are protected by an invisible shield!",
						"schema"=>"module-ladyerwin"
					));
					$hpt = "permanent";
					if (!get_module_setting("carrydk") || (is_module_active("globalhp") && !get_module_setting("carrydk", "globalhp"))) $hpt = "temporary";
					$hpt = translate_inline($hpt);
					$extra = get_module_setting("hptoaward");
					$randhp=e_rand(1,$maxhp);
					$hptext=($randhp==1? translate_inline("point"):translate_inline("points"));
					output("`n`nYou additionally feel stronger! `$ Lady Erwin`@ has given you %s `b%s`b hit%s!",$randhp,$hpt,$hptext);
					if ($session['user']['hitpoints'] < $session['user']['maxhitpoints'])
						$session['user']['hitpoints']=$session['user']['maxhitpoints'];
						$session['user']['maxhitpoints']+=$randhp;
						$session['user']['hitpoints']+=$randhp;
					break;
				case 2:
					output("`$ Lady Erwin`@ is obviously pleased... and something strange happens.`n`n");
					$gemgain=e_rand(1,get_module_setting("maxgems"));
					$gemtext=($gemgain==1? translate_inline("gem"):translate_inline("gems"));
					$session['user']['gems']+=$gemgain;
					output("Somehow, she rolls `^%s`@ %s towards you! You must be in luck because she seems to like you very much!",$gemgain,$gemtext);
					break;
				case 3: case 4: case 5:
					output("`$ Lady Erwin`@ seems to be pleased, you start to pet her for a while and return to the forest positively glowing.");
					apply_buff('ladyerwinlittle',
					array(
						"name"=>"`%Little bless of Lady Erwin",
						"rounds"=>$bufflast,
						"survivenewday"=>$buffsurvives,
						"wearoff"=>"You feel normal again.",
						"atkmod"=>1.2,
						"minioncount"=>1,
						"regen"=>$session['user']['level'],
						"roundmsg"=>"You feel great and healthy!",
						"schema"=>"module-ladyerwin"
					));
					if ($session['user']['hitpoints'] < $session['user']['maxhitpoints'])
						$session['user']['hitpoints']=$session['user']['maxhitpoints'];
					break;
				case 6: case 7:
					output("`$ Lady Erwin`@ doesn't seem to care much about you and vanishes in the woods.`n");
					output("You continue your journey.");
					break;
				case 8:
					output("`$ Lady Erwin`@ is not very pleased and runs away. You feel suddenly kind of sad about it.");
					apply_buff('ladyerwinsad',
					array(
						"name"=>"`%Little curse of Lady Erwin",
						"rounds"=>$bufflast,
						"wearoff"=>"You feel normal again.",
						"atkmod"=>0.8,
						"minioncount"=>1,
						"survivenewday"=>$buffsurvives,
						"roundmsg"=>"`)You feel sad about `$ Lady Erwin `)!",
						"schema"=>"module-ladyerwin"
						));
					 break;
				 case 9:case 10:
					output("`@You try to approach her, but she seems to sense evil intentions from you (true or not) and looks at you angrily!");
					output("`n`nYou don't know why, but you start to run. You run and run and run... finally, you stop to catch your breath. You lose a forest fight.`n`n");
					if ($session['user']['turns']>0) $session['user']['turns']--;
					if (e_rand(1,2)==1 && $session['user']['gems']>0) {
						output("You have `$ lost`@ a gem during your escape!");
						$session['user']['gems']--;
					}
					apply_buff('ladyerwinsad',
					 array(
						"name"=>"`%Little curse of Lady Erwin",
						"rounds"=>$bufflast,
						"wearoff"=>"You feel normal again.",
						"atkmod"=>0.8,
						"minioncount"=>1,
						"survivenewday"=>$buffsurvives,
						"roundmsg"=>"`)You feel sad about `$ Lady Erwin `)!",
						"schema"=>"module-ladyerwin"
						));
					break;
			}
			$session['user']['specialinc'] = "";
			break;
		case "feed":
			$randomchance=e_rand(1,10);
			$session['user']['specialinc'] = "";
			if (is_module_active('alignment')) {
				$evilalign = get_module_setting('evilalign','alignment');
				$goodalign = get_module_setting('goodalign','alignment');
				$useralign = get_module_pref('alignment','alignment');
				if ($useralign <= $evilalign) $randomchance+=1;
				if ($useralign >= $goodalign) $randomchance-=1;
			}
			output("`@You carefully approach the little white rat. It doesn't seem to be very shy, and you offer her some food in your hand. As she approaches, you suddenly realize that this rat might be `$ Lady Erwin`@, spoken of in many legends.`n`n");
			if ($randomchance<1) $randomchance=1;
			if ($randomchance>10) $randomchance=10;
			switch ($randomchance) {
				case 1:
					output("`$ Lady Erwin`@ is accepting your offer and eats happily. You are in a good mood now!");
					$gainedff=e_rand(1,get_module_setting(maxff));
					$fftext=($gainedff==1? translate_inline("fight"):translate_inline("fights"));
					output("`n`nYou gain `^%s`@ forest %s!",$gainedff,$fftext);
					$session['user']['turns']+=$gainedff;
				break;
				case 2: case 3: case 4:
					output("`$ Lady Erwin`@ looks suspicious... but eats a bit.`n");
					output("She is pleased and some warm feeling is glowing inside you.");
					$gold=e_rand(1,get_module_setting("maxgold"));
					$goldtext=($gold==1? translate_inline("piece"):translate_inline("pieces"));
					output("`n`nShe somehow manages to pull out of a bush a little bag...`nas you open it, you find `^%s gold%s`@!",$gold,$goldtext);
					$session['user']['gold']+=$gold;
				break;
				case 5:case 6:case 7:
					output("`$ Lady Erwin`@ just sniffs a bit... but then she seems to think that you might cause harm to her and runs away.");
					output("`n`nYou shrug and continue on your journey.");
				break;
				case 8:
					output("`$ Lady Erwin`@ seems to be quite angry about your offer... she seems to sense some evil plan from you (maybe... it's true?).");
					apply_buff('ladyerwinsad2',
					 array(
						"name"=>"`%Curse of Lady Erwin",
						"rounds"=>$bufflast,
						"wearoff"=>"You feel normal again.",
						"atkmod"=>0.5,
						"minioncount"=>1,
						"survivenewday"=>$buffsurvive,
						"roundmsg"=>"`)You feel very sad about `$ Lady Erwin `)!",
						"schema"=>"module-ladyerwin"
						));
				break;
				case 9: case 10:
					output ("`$ Lady Erwin`@ doesn't look pleased at all ... you've just tried to give her some old food you wouldn't eat anymore, don't you?");
					output("`n`nShe runs away, but not before letting you know what she thinks of you.`n`n");
					output("Suddenly, you feel very depressed.");
					$loseff=e_rand(1,get_module_setting("maxff"));
					$fftext=($loseff==1? translate_inline("fight"):translate_inline("fights"));
					if ($session['user']['turns']>=$loseff) output("`n`nYou `$ lose `@%s forest %s!",$loseff,$fftext);
					if ($session['user']['turns']>=$loseff) $session['user']['turns']-=$loseff;
				break;
			}
			$session['user']['specialinc'] = "";
			break;
		case "walk":
			output("`@You decide that it is safer to run away.");
			$stumble=e_rand(1,2);
			if ($stumble==1) {
				output("`n`nOh my, you're really clumsy. You ran with full speed but you tripped... and suffered injury from the fall.`n");
				$hploss=e_rand(1,$session['user']['maxhitpoints']);
				$hptext=($hploss==1? translate_inline("point"):translate_inline("points"));
				output("You `$ lose`@ %s hit%s!",$hploss,$hptext);
				$session['user']['hitpoints']-=$hploss;
				if ($session['user']['hitpoints']<=0) {
					$session['user']['hitpoints']=1;
					output("`n`nYou are at the brink of death... but %s`@ laughed quite a bit about that little stunt.",getsetting('deathoverlord','`$Ramius'));
					output(" He decided to let you live... for now... because you're quite funny in a way.");
				}
			}
			$session['user']['specialinc'] = "";
			break;
		case "kick": //players who try to harm her have to fight against her protector ;) and they receive no mercy
			set_module_pref("kicked",1);
			if (is_module_active('alignment')) {
				require_once("./modules/alignment/func.php");
				align("-5");
			}
			output("`@You walk towards her... and when in range you try to kick her.");
			output(" It remains a try... because right when your boot is about to strike her, a roar of anger is heard out of a nearby bush.");
			$defender=get_module_setting("protectorname");
			$defenderweapons=get_module_setting("protectorweapon");
			output("`n`nHer defender %s`@ appears and takes you on. You wonder about the weapon, %s...",$defender,$defenderweapons);
			if ($session['user']['playerfights']>1) {
				output("`n`n(You also lose a PVP fighting the defender...)`n");
				$session['user']['playerfights']--;
			}
			//now let's start a fight... done with the help of Sichae
			$battle=true;
			$badguy = array(
					"creaturename"=>$defender,
					"creaturelevel"=>$session['user']['level']+5,
					"creatureweapon"=>$defenderweapons,
					"creatureattack"=>$session['user']['attack']*get_module_setting("protectorattack"),
					"creaturedefense"=>$session['user']['defense']*get_module_setting("protectordefense"),
					"creaturehealth"=>round($session['user']['maxhitpoints']*get_module_setting("protectorhp")),
					"diddamage"=>0,);
			$session['user']['badguy'] = createstring($badguy);
			require_once("lib/battle-skills.php");
			if ($suspendbuffs) suspend_buffs('allowintrain',"Time ceases to exist... You suddenly feel vulnerable... the aura of the protector disables all your extraordinary talents and no one is able to help you now!");
			$op = "combat";
			//redirect("runmodule.php?module=ladyerwin&op=combat");
			//break; //didn't know how to call it right, but let just the fight now occurr
		case "combat": case "fight":
			include("battle.php");
			if ($victory){ //no exp at all for such a foul act
				output("`@The protector dies by your hand. You have managed to survive...somehow.");
				addnews("%s`^ has somehow survived the deadly strokes of %s`^, protector of `$ Lady Erwin`^.",$session['user']['name'],get_module_setting("protectorname"));
				if ($suspendbuffs) unsuspend_buffs('allowintrain',"You feel that time and the energies are now flowing normally again.");
				$session['user']['specialinc'] = "";
				$badguy=array();
				$session['user']['badguy']="";
			 }elseif ($defeat){ //but a loss of course if you die
				$exploss = $session['user']['experience']*get_module_setting("experienceloss")/100;
				output("`@The protector strikes you down mercilessly because you tried to harm the Lady.`n");
				if ($exploss>0) output(" You lose `^%s percent`@of your experience and all of your gold.",get_module_setting("experienceloss"));
				$session['user']['experience']-=$exploss;
				$session['user']['gold']=0;
				debuglog("lost $exploss experience and all gold to Lady Erwin.");
				addnews("%s`^ was killed after a cowardly attack on `$ Lady Erwin`^ by %s`^, protector of `$ Lady Erwin`^.",$session['user']['name'],get_module_setting("protectorname"));
				addnav("Return");
				addnav("Return to the Shades","shades.php");
				$session['user']['specialinc'] = "";
				$badguy=array();
				$session['user']['badguy']="";
				if ($suspendbuffs) unsuspend_buffs('allowintrain',"");
			 }else{
				require_once("lib/fightnav.php");
				$allow = true;
				fightnav($allow,false);
				if ($session['user']['superuser'] & SU_DEVELOPER) addnav("Escape to Village","village.php");
			 }
		 }

}

function ladyerwin_run(){
}

?>
