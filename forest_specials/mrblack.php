	<?php
/*

This module is dedicated to Mr. Black, who was put to sleep exactly 3 weeks after Lady Erwin... I miss him as much as her...

Oliver

*/

function mrblack_getmoduleinfo()
{
	$info = array(
		"name"=>"Mr. Black - The Black Rat",
		"author"=>"`2Oliver Brendel",
		"version"=>"1.0",
		"category"=>"Forest Specials",
		"download"=>"http://lotgd-downloads.com",
	"settings"=>array(
		"Lady Erwin - Preferences, title",
		"This module supports the alignment module,note",
		"suspendbuffs"=>"Does Mr. Black suspend all buffs (those not allowed in training),bool|1",
		"maxgems"=>"Maximum amount of gems to win,floatrange,1,10,1|3",
		"experienceloss"=>"Percentage: How many experience is lost when player is killed by Mr. Black,floatrange,1,100,1|10",
		"bufflast"=>"How long do possible buffs last in rounds,floatrange,1,100|15",
		"buffsurvives"=>"Do buffs survive new days,bool|0",
		/*"maxgold"=>"Maximum amount of gold to win/lose,int|300",
		"maxhp"=>"Maximum amount of hp to gain (temporary/permanent according to your server settings),floatrange,1,10,1|3",
		"carrydk"=>"Do max hitpoints gained carry across DKs?,bool|1",
		"maxff"=>"Maxmimum amount of forest fights to gain/lose,floatrange,1,10,1|3",
		"suspendbuffs"=>"Does the protector suspend all buffs (those not allowed in training),bool|true",
		"bufflast"=>"How long do possible buffs last in rounds,floatrange,1,100|15",
		"buffsurvives"=>"Do buffs survive new days,bool|false",
		"protectorname"=>"Name of protector of the Lady,text|Oliver",
		"protectorweapon"=>"Name of the weapons of her protector,text|pure affection for the Lady",
		"protectorattack"=>"Attackmultiplicator based on PC-attack,floatrange,0,2,0.05|1.1",
		"protectordefense"=>"Defensemultiplicator based on PC-defense,floatrange,0,2,0.05|1.1",
		"protectorhp"=>"Hitpointsmultiplicator based on PC-hitpoints,floatrange,0,2,0.05|1.1",
		"experienceloss"=>"Percentage: How many experience is lost when player is killed by the protector,floatrange,1,100,1|10"
	*/),
	/*"prefs"=>array(
			"Lady Erwin Preferences,title",
			"kicked"=>"Tried to kick her once,viewonly|0",
		)*/
		"requires"=>array(
			"ladyerwin"=>"1.06|`2Oliver Brendel",
		),
	);
	return $info;
}
function mrblack_install()
{
	module_addeventhook("forest", "return 100;");
	return true;
}
function mrblack_uninstall()
{
	return true;
}
function mrblack_dohook($hookname,$args)
{
	return $args;
}
function mrblack_runevent($type,$link)
{
	global $session;
	$from = "forest.php?";
	$session['user']['specialinc'] = "module:mrblack";
	$op = httpget('op');
	$bufflast = get_module_setting("bufflast");
	$maxhp = get_module_setting("maxhp");
	$buffsurvives = get_module_setting("buffsurvives");
	$suspendbuffs = get_module_setting("suspendbuffs");
	switch ($op)
	{
	case "":
		output("As you walk along a small track, you suddenly realize everything grew quiet around you.");
		if($session['user']['race']== 'Elf')
		{
		output("`@Your elvish senses tell you that something special and important closes in.");
		}
		output("`n`nAs you walk farther into the forest, you hear something move in a bush near you. You stop a moment and wonder what might come out,... and ready your weapon... just in case...");
		output("`n`nYou are quite surprised as a little black rat comes out of the bush. It begins to sniff towards your direction.");
		$kicked=get_module_pref("kicked","ladyerwin");
		if ($kicked) {
			output("`n`n `^The rat seems to grin... diabolically... evilly... in a mood to kill...`n`n");
			output("You suddenly remember you once tried to kick a certain white rat... ");
			output("`n`nLegends tell about a companion of her... called `)Mr. Black`^...");
			addnav("Fight for your life!",$link."op=sorryikickedher");
			$session['user']['specialmisc']='kicked';
			set_module_pref("kicked",0,"ladyerwin");
		} else {
			output("`n`nWhat will you do?");
			addnav("Pet the rat",$link."op=pet");
			addnav("Feed the rat",$link."op=feed");
			addnav("Kick the rat",$link."op=kick");
			addnav("Walk away",$link."op=walk");
		}
		break;
	case "pet": case "feed":
		output("`@You start to approach the black rat... but you hold back as you feel a very strong presence.");
		output(" You start to realize this is not a rat that needs to be pet or fed.");
		output("`n`nLegends tell about a black rat... the companion of `\$Lady Erwin`@... called `)Mr. Black`^...`n`n");
		$randomchance=e_rand(1,10);
		if (is_module_active('alignment')) {
			$evilalign = get_module_setting('evilalign','alignment');
			$goodalign = get_module_setting('goodalign','alignment');
			$useralign = get_module_pref('alignment','alignment');
			if ($useralign <= $evilalign) $randomchance+=1;
			if ($useralign >= $goodalign) $randomchance-=1;
		}
		if ($randomchance<1) $randomchance=1;
		if ($randomchance>10) $randomchance=10;
		switch ($randomchance)
			{
			case 1:
				output("`) Mr. Black`@ looks like he is pleased.");
				if (is_module_active('alignment'))
				{
				require_once("./modules/alignment/func.php");
				align("5");
				}
				output(" He really seems to like you! You feel better than ever!");
				apply_buff('mrblackmastermind',
				array(
					"name"=>"`%Fierceness of Mr. Black",
					"rounds"=>$bufflast,
					"wearoff"=>"You feel vulnerable again.",
					"invulnerable"=>1,
					"survivenewday"=>$buffsurvives,
					"roundmsg"=>"`^You are protected by `)Mr. Black`^!",
					"schema"=>"module-mr.black"
				));
				break;
			case 2:
			output("`)Mr. Black`@ is obviously pleased... and something strange happens.`n`n");
			$gemgain=e_rand(1,get_module_setting("maxgems"));
			$gemtext=($gemgain==1? translate_inline("gem"):translate_inline("gems"));
			output("Somehow, he rolls `^%s`@ %s towards you! You must be in luck because he seems to like you very much!",$gemgain,$gemtext);
			$session['user']['gems']+=$gemgain;
			break;
			case 3:
				output("`)Mr. Black`@ seems to be pleased, and you leave somehow with a feeling much worse could have happened...");
				apply_buff('mrblacklittle',
				array(
					"name"=>"`%Relief of `)Mr. Black",
					"rounds"=>$bufflast,
					"survivenewday"=>$buffsurvives,
					"wearoff"=>"You feel normal again.",
					"atkmod"=>1.2,
					"minioncount"=>1,
					"regen"=>$session['user']['level'],
					"roundmsg"=>"You feel great and healthy!",
					"schema"=>"module-mrblack"
				));
				if ($session['user']['hitpoints'] < $session['user']['maxhitpoints'])
				$session['user']['hitpoints']=$session['user']['maxhitpoints'];
			break;
			case 4: case 5: case 6:
				output("`) Mr. Black`@ doesn't seem to care much about you and vanishes in the woods.`n");
				output("You continue your journey.");
			break;
			case 7:case 8:
			output("`) Mr. Black`@ is not very pleased and his aura seems to oppress you.");
			apply_buff('mrblackoppress',
			array(
				"name"=>"`%Little curse of `)Mr. Black",
				"rounds"=>$bufflast,
				"wearoff"=>"You feel normal again.",
				"atkmod"=>0.8,
				"minioncount"=>1,
				"survivenewday"=>$buffsurvives,
				"roundmsg"=>"`^You feel watched by `)Mr. Black`^!",
				"schema"=>"module-mrblack"
			));
			break;
			case 9:case 10:
			output("`@You try to approach him, but he seems to sense evil intentions from you (true or not) and looks at you angrily!");
			output("`n`nYou don't know why, but you start to run. You run and run and run... finally, you stop to catch your breath.  You lose a forest fight.`n`n");
			if ($session['user']['turns']>0) $session['user']['turns']--;
			if (e_rand(1,2)==1 && $session['user']['gems']>0)
			{
			output("You have `$ lost`@ a gem during your escape!");
			$session['user']['gems']--;
			}
			apply_buff('mrblackoppress',
			array(
				"name"=>"`%Little curse of `)Mr. Black",
				"rounds"=>$bufflast,
				"wearoff"=>"You feel normal again.",
				"atkmod"=>0.8,
				"minioncount"=>1,
				"survivenewday"=>$buffsurvives,
				"roundmsg"=>"`^You feel watched by `)Mr. Black`^!",
				"schema"=>"module-mrblack"
			));
			break;
		}
		$session['user']['specialinc'] = "";
	break;

	case "walk":
		output("You don't mind the rat and start to walk away... you feel this rat radiated danger more than anything else...");
		$what=e_rand(1,2);
		if ($what==2) {
			output("You trip... cursing what happend you turn around and find ");
			$find=e_rand(1,6);
			switch($find) {
				case 1:
					output(" a skeleton... even the bones seems to be cracked by little small teeth...strange...");
					break;
				case 2:
					output(" an apple! Delicious!");
					if ($session['user']['hitpoints']<$session['user']['maxhitpoints']) {
						output("`n`nYour hitpoints are filled up!");
						$session['user']['hitpoints']=$session['user']['maxhitpoints'];
					}
					break;
				case 3:
					$gold=e_rand(1,$session['user']['level']*30);
					output(" %s gold!",$gold);
					$session['user']['gold']+=$gold;
					break;
				case 4:case 5: case 6:
					output(" the great black nothingness of nothing. Really!");
					break;
			}
		}
		$session['user']['specialinc']="";
		break;
	case "kick":
	case "sorryikickedher": //players who try to harm her have to fight against her protector ;) and they receive no mercy
		//now let's start a fight... done with the help of Sichae
		if ($op=="kick") output("`^Meeep.... bad idea, the black rat prepares for combat!");
		$battle=true;
		$badguy = array(
		"creaturename"=>translate_inline("Mr. Black"),
		"creaturelevel"=>$session['user']['level']+5,
		"creatureweapon"=>translate_inline("Teeth from Hell"),
		"creatureattack"=>$session['user']['attack']*1.33,
		"creaturedefense"=>$session['user']['defense']*1.95,
		"creaturehealth"=>round($session['user']['maxhitpoints']*(1.5+e_rand(1,80)/100)),
		"diddamage"=>0,);
		$session['user']['badguy'] = createstring($badguy);
		require_once("lib/battle-skills.php");
		if ($suspendbuffs) suspend_buffs('allowintrain',"Time ceases to exist... You suddenly feel vulnerable... the aura of the Mr. Black disables all your extraordinary talents and no one is able to help you now!");
		$op = "combat";
		//redirect("runmodule.php?module=mrblack&op=combat");
		//break; //didn't know how to call it right, but let just the fight now occurr
	case "combat": case "fight":
		include("battle.php");
		if ($victory){ //no exp at all for such a foul act
		output("`@Mr. Black is critically wounded and somehow your vision blurs. You have managed to survive...somehow.");
		addnews("%s`^ has somehow survived the deadly bites of `2Mr. Black`^, companion of `\$Lady Erwin`^.",$session['user']['name']);
		if ($suspendbuffs) unsuspend_buffs('allowintrain',"You feel that time and the energies are now flowing normally again.");
		$session['user']['specialinc'] = "";
		$session['user']['specialmisc'] = "";
		$badguy=array();
		$session['user']['badguy']="";
    }elseif ($defeat){ //but a loss of course if you die
		$exploss = $session['user']['experience']*get_module_setting("experienceloss")/100;
		output("`@Mr. Black bites you down mercilessly because you have tried to harm the Lady once.`n");
		if ($exploss>0) output(" You lose `^%s percent`@  of your experience and all of your gold.",get_module_setting("experienceloss"));
		$session['user']['experience']-=$exploss;
		$session['user']['gold']=0;
		debuglog("lost $exploss experience and all gold to Lady Erwin.");
		if ($session['user']['specialmisc']=='kicked')
			addnews("%s`^ was killed and eaten as a revenge for a cowardly attack on `\$Lady Erwin`^ by `2Mr. Black`^, companion of `$ Lady Erwin`^.",$session['user']['name']);
			else
			addnews("%s`^ was killed and eaten for challenging `2Mr. Black`^, companion of `$ Lady Erwin`^.",$session['user']['name']);
		addnav("Return");
		addnav("Return to the Shades","shades.php");
		$session['user']['specialinc'] = "";
		$session['user']['specialmisc'] = "";
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

function mrblack_run(){
}

?>
