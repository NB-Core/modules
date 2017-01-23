<?php
/*
Meet the punishers in the woods...

*/
function evil_punishers_getmoduleinfo()
{
	$info = array(
	"name"=>"The Evil Punishers - Swifter and Sharper",
	"author"=>"`2Oliver Brendel",
	"version"=>"1.0",
	"category"=>"Forest Specials",
	"download"=>"http://lotgd-downloads.com",
	"settings"=>array(
	"The Punishers - Preferences, title",
	"Meet them in the woods and maybe gut punished if you are evil,note",
	"name"=>"Name (coloured) of the organization they are from,text|`4A`\$N`2B`4U",
	"level"=>"Level at which the player might escape (2-15),floatrange,2,15,1|10",
	"overwhelm"=>"1 in x chance they are really really strong,floatrange,2,25,1|6",
	"chiefname"=>"Name of the overwhelming leader,text|`QM`^i`ts`Et`)r`2e`@ss `QK`^a`ty`El`)a",
	"experienceloss"=>"Percentage: How many experience is lost after a fight,floatrange,1,100,1|10",
	),
	"requires"=>array(
		"alignment"=>"1.72|WebPixie<br> `#Lonny Luberts<br>`^and Chris Vorndran",
		),
	);
	return $info;
}
function evil_punishers_install()
{
	module_addeventhook("forest", "return 50;");
	if (is_module_active("evil_punishers")) debug("`c`bModule Punishers updated`b`c`n`n");
	return true;
}
function evil_punishers_uninstall()
{
	return true;
}
function evil_punishers_dohook($hookname,$args)
{
	return $args;
}
function evil_punishers_runevent($type,$link)
{
	global $session;
	$from = "forest.php?";
	$session['user']['specialinc'] = "module:evil_punishers";
	$op = httpget('op');
	$punishers=get_module_setting("name");
	$chief=get_module_setting("chiefname");
	require_once("./modules/alignment/func.php");
	switch ($op)
	{
	case "":
		if ($session['user']['location']!="Kirigakure") { //for my game, you can enter any place where you want to have this suppressed
			if (is_module_active('punishers')) {
				$session['user']['specialinc'] = "module:punishers";
				require_once("modules/punishers.php");
				punishers_runevent($type,$link);
				return;
			}
			output("`3You hear some noises passing by at rapid speed. After some time they are gone.");
			output("`nWondering what that might be, you continue on your journey.");
			output_notl("`n`n");
			$session['user']['specialinc'] = "";
			break;
		}
		output("`3You are on your way through the forest as you hear: \"`\$STOP`&, don't move!`3\"");
		output_notl("`n`n");
		output("You sense this voice has somewhat power behind it to back these words up by power.");
		output("`nWhat do you do?");
		addnav("Stand and wait",$link."op=stand");
		addnav("Run away",$link."op=run");
		break;
	case "stand":
		output("`3After a few minutes you are surrounded by `^%s`3 members.",$punishers);
		$ali=evil_punishers_get();
		output_notl("`n`n");
		if ($ali==0) {
			$gender=(!$session['user']['sex']?translate_inline("guy"):translate_inline("girl"));
			if (e_rand(1,get_module_setting('overwhelm'))==1) {
				output("`3Oh no! You seem to have attracted the most skilled of their kind!`n");
				output("They are led by the famous %s`3 who is staring at you angrily with both whips drawn!`n`n",$chief);
				$over=1;
			}
			output("\"`&So, what do we have here? An all-so-good %s trying to sneak through `bour`b woods?",$gender);
			output(" Prepare to die you `iscumm`i!`3\"...");
			addnav("Get combat-ready",$link."op=combatready&over=$over");
		} elseif ($ali==1) {
			$gender=(!$session['user']['sex']?translate_inline("buddy"):translate_inline("lassie"));
			output("\"`&We %s`& don't have business with you. You better not mess with our village, %s!`3\"",$punishers,$gender);
			output_notl("`n`n");
			output("Just as they appeared, they take their leave: fast and almost noiseless.");
			addnav("Continue your journey",$link."op=leave");
		} else {
			output("\"`&You seem to be somebody as evil as we are! Your good work has spread around, all the slaughter and killing. It's good to see someone like you around here.");
			output(" Good guys always lurk around here in the deep woods.`3\"");
			if ($session['user']['hitpoints']<$session['user']['maxhitpoints']) {
				output_notl("`n`n");
				output(" \"`&Oh, you're wounded. Our sadistic medical ninja is going to treat you.`3\"");
				output_notl("`n`n");
				output_notl("Your health has been `2fully restored`3!");
				$session['user']['hitpoints']=$session['user']['maxhitpoints'];
			}
			addnav("Continue your journey",$link."op=leave");
		}
		break;
	case "run":
		output("`3\"`&Hey you, stay where you are!`3\" is now a faint sound in your back as you run away.");
		output("`n");
		$chance=e_rand(1,$session['user']['level']); //clever I think. low-level guys *cant* run away
		$fleechance=get_module_setting("level");
		switch($chance) {
			case $fleechance:
				output("`3You run as fast as you can... trusting to your skills as warrior.");
				output_notl("`n`n");
				output("Soon you are once more alone in the woods.");
				if (e_rand(0,1) && $session['user']['turns']>0) {
					output("You `\$lose`3 enough time for one forest fight!");
					$session['user']['turns']--;
				}
				$session['user']['specialinc'] = "";
				break;
			default:
			output("`3You are no match for the speed of your pursuers.");
			output("`n`n");
			output("After a few minutes you are surrounded by `^%s`3 members.",$punishers);
			$ali=evil_punishers_get();
			output_notl("`n`n");
			if ($ali==0) {
				$gender=(!$session['user']['sex']?translate_inline("guy"):translate_inline("girl"));
				if (e_rand(1,get_module_setting('overwhelm'))==1) {
					output("`3Oh no! You seem to have attracted the most skilled of their kind!`n");
					output("They are led by the famous %s`3 who is staring at you angrily with both whips drawn!`n`n",$chief);
					$over=1;
				}
				output("\"`&So, what do we have here? An all-so-good %s trying to sneak through `bour`b woods?",$gender);
				output(" Prepare to die you `iscumm`i!`3\"...");
				addnav("Get combat-ready",$link."op=combatready&over=$over");
			} elseif ($ali==1) {
				$gender=(!$session['user']['sex']?translate_inline("buddy"):translate_inline("lassie"));
				output("\"`&We %s`& don't have business with you. You better not mess with our village, %s!`3\"",$punishers,$gender);
				output_notl("`n`n");
				output("Just as they appeared, they take their leave: fast and almost noiseless.");
				addnav("Continue your journey",$link."op=leave");
			} else {
				output("\"`&You seem to be somebody as evil as we are! Your good work has spread around, all the slaughter and killing. It's good to see someone like you around here.");
				output(" Good guys always lurk around here in the deep woods.`3\"");
				if ($session['user']['hitpoints']<$session['user']['maxhitpoints']) {
					output_notl("`n`n");
					output(" \"`&Oh, you're wounded. Our sadistic medical ninja is going to treat you.`3\"");
					output_notl("`n`n");
					output_notl("Your health has been `2fully restored`3!");
					$session['user']['hitpoints']=$session['user']['maxhitpoints'];
				}
				addnav("Continue your journey",$link."op=leave");
			}
		}
		break;
	case "leave":
		output("`3You continue on your journey and forget about the `^%s`3 members very soon.`n`n",$punishers);
		$session['user']['specialinc'] = "";
		break;
	case "hilfeichbineinadminholtmichhierraus":
		output("Due to your powers as a god you teleport yourself out of it.");
		$session['user']['specialinc'] = "";
		strip_buff('evil_punisher_kunais');
		break;
	case "combatready":
		require_once("lib/battle-skills.php");
		$extraatt=e_rand(1,$session['user']['level']);
		$extradef=$extraatt;
		$extrahp=$extraatt*20;
		$badguy = array(
		"creaturename"=>translate_inline($punishers." members"), //not so good for translation purposes if they switch the name often, but well
		"creaturelevel"=>$session['user']['level']+e_rand(1,3),
		"creatureweapon"=>translate_inline("many deadly weapons"),
		"creatureattack"=>$session['user']['level']+$session['user']['dragonkills']+$extraatt,
		"creaturedefense"=>$session['user']['level']+$session['user']['dragonkills']+$extradef,
		"creaturehealth"=>$session['user']['level']*10+50+$extrahp,
		"diddamage"=>0,);
		if (httpget('over')) { //attack+defence depends on the dks... the more, the bigger the thread, the harder they fight... and win with it usually
			$extrahp=round(e_rand($session['user']['level']+50,($session['user']['maxhitpoints']-$session['user']['level']*10)));
			$extraatt=e_rand(10,$session['user']['dragonkills']+5);
			$extradef=e_rand(10,$session['user']['dragonkills']+5);
			$badguy['creaturename']=$chief.translate_inline("`2 and ").$badguy['creaturename'];
			$badguy['creatureweapon']=translate_inline("Two vicious whips and ").$badguy['creatureweapon'];

		}
	   	$battle=true;
		$session['user']['badguy'] = createstring($badguy);
		$op = "combat";
		httpset('op', $op);
	case "combat": case "fight":
		if (e_rand(1,3)) {
			apply_buff('evil_punisher_kunais',
				array(
				"name"=>"`qKunais!",
				"rounds"=>2,
				"mingoodguydamage"=>1,
				"maxgoodguydamage"=>5,
				"minioncount"=>1,
				"effectmsg"=>"`)A poisoned Kunai hits you for {damage} damage!",
				"schema"=>"module-evil_punishers",
		));
		}
		include("battle.php");
		if ($victory){ //no exp at all
			output("`n`n`@...`!%s`^ members lie dead around you. You have managed to survive...somehow.",$punishers);
			output("`n%s`^ is nowhere to be seen... must have gotten somewhere to treat the wounds... hopefully your face was not recognized...`n",$chief);
			if (strstr($badguy['creaturename'],$chief)) {
				addnews("%s`^ survived an encounter with %s`^ in the woods of %s.",$session['user']['name'],$badguy['creaturename'],$session['user']['location']);
			} else {
				addnews("%s`^ survived an encounter with the local %s`^ in the woods of %s.",$session['user']['name'],$punishers,$session['user']['location']);
			}
			$session['user']['specialinc'] = "";
			$badguy=array();
			strip_buff('evil_punisher_kunais');
			$session['user']['badguy']="";
	    }elseif ($defeat){ //but a loss of course if you die
			$exploss = $session['user']['experience']*get_module_setting("experienceloss")/100;
			output("`n`n`@You are dead... struck down by %s`@ members`@.`n",$punishers);
			if ($exploss>0) output(" You lose `^%s percent`@  of your experience and all of your gold.",get_module_setting("experienceloss"));
			$session['user']['experience']-=$exploss;
			$session['user']['gold']=0;
			debuglog("lost $exploss experience and all gold due to the punishers $punishers.");
			strip_buff('evil_punisher_kunais');
			if (strstr($badguy['creaturename'],$chief)) {
				addnews("%s`^ was killed by `^%s`^ in the woods of %s`^.",$session['user']['name'],$badguy['creaturename'],$session['user']['location']);
			} else {
				addnews("%s`^ was killed by `^%s`^ members in the woods of %s`^.",$session['user']['name'],$punishers,$session['user']['location']);
			}
			addnav("Return");
			addnav("Return to the Shades","shades.php");
			$session['user']['specialinc'] = "";
			$badguy=array();
			$session['user']['badguy']="";
	    }else{
			require_once("lib/fightnav.php");
			$allow = true;
			fightnav($allow,false);
			if ($session['user']['superuser'] & SU_DEVELOPER) addnav("Escape to Village",$link."op=hilfeichbineinadminholtmichhierraus");
		}
		break;
	}

}

function evil_punishers_run(){
}

function evil_punishers_get() {
	$evilalign = get_module_setting('evilalign','alignment');
	$goodalign = get_module_setting('goodalign','alignment');
	$useralign = get_module_pref('alignment','alignment');
	//2 equals evil, 0 equals neutral, 0 equals good alignment
	if ($useralign <= $evilalign) return 2;
	if ($useralign >= $goodalign) return 0;
	return 1;
}
?>
