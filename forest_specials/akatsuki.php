<?php
/*
Meet the akatsuki in the woods...

*/
function akatsuki_getmoduleinfo()
{
	$info = array(
	"name"=>"The Akatsuki - Swift and Sharp",
	"author"=>"`2Oliver Brendel",
	"version"=>"1.0",
	"category"=>"Travel Specials",
	"download"=>"http://dragonprime.net/dls/akatsuki.zip",
	"settings"=>array(
	"The akatsuki - Preferences, title",
	"Meet them after travelling when you have had too much of a debt while being at Akatsuki,note",
	"name"=>"Name (coloured) of the organization they are from,text|`4A`\$N`2B`4U",
	"overwhelm"=>"1 in x chance they are really really strong,floatrange,2,25,1|6",
	"chiefname"=>"Name of the overwhelming leader,text|`QK`^a`tk`Ea`)s`@h`%i",
	"experienceloss"=>"Percentage: How many experience is lost after a fight,floatrange,1,100,1|10",
	),
	"prefs"=>array(
		"The akatsuki,title",
		"slayercheater"=>"Has user had large debts on last DK?,bool|0",
		),
	);
	return $info;
}
function akatsuki_install()
{
	module_addeventhook("travel", "require_once(\"modules/akatsuki/akatsuki_chance.php\");return akatsuki_getchance();");
	module_addhook("dk-preserve");
	return true;
}
function akatsuki_uninstall()
{
	return true;
}
function akatsuki_dohook($hookname,$args)
{
	global $session;
	switch($hookname) {
		case "dk-preserve":
		if (is_module_active("slayerguild")) {
			set_module_pref("slayercheater",0);
			if (get_module_pref("apply","slayerguild")) {
				if ($session['user']['goldinbank']<-499) {
					set_module_pref("slayercheater",1);
				}
			}
		}
		break;
	}
	return $args;
}
function akatsuki_runevent($type,$link)
{
	global $session;
	$city = urldecode(httpget("city"));
	$danger = httpget("d");
	$from = $link;
	$session['user']['specialinc'] = "module:akatsuki";
	$op = httpget('op');
	$akatsuki=get_module_setting("name");
	$chief=get_module_setting("chiefname");
	if ($city=='Iwagakure' && is_module_active('evil_punishers')) {
		$akatsuki=get_module_setting("name","evil_punishers");
		$chief=get_module_setting("chiefname","evil_punishers");
	}elseif ($city!='Iwagakure' && is_module_active('punishers')) {
		$akatsuki=get_module_setting("name","punishers");
		$chief=get_module_setting("chiefname","punishers");
	}
	switch ($op)
	{

	case "":
		output("`3You try approach %s to enter the town, as you hear: \"`\$STOP`&, don't move!`3\"",$city);
		output("`n`nSeveral members of the local ANBU seem to have noticed your approach upon the city.");
		if ($city=='Iwagakure') { 		
			output("`n\"`\$My my... aren't you %s`\$ who is working for Akatsuki? You left quite some debt at the bank. You are denied to enter the city, unfortunately. Unfortunately too that `@Orochimaru`3 is currently tolerating your kind.`3\"",$session['user']['name']);
			output_notl("`n`n");
			
		} else {
		output("`n\"`\$Hold it! You are %s`\$ who is working for Akatsuki. You left quite some debt at the bank. You are denied to enter the city. We do not want a confronation.`3\"",$session['user']['name']);
		}
		output("`nWhat do you do?");
		addnav("Ignore them and enter",$link."op=ignore");
		addnav("Fight them",$link."op=combatready");
		addnav("Back off",$link."op=leave");
		break;
	case "ignore":
		output("`3After a few minutes you are surrounded by `^%s`3 members in combat ready stance... you have to fight your way through.",$akatsuki);
		output_notl("`n`n");
		$gender=(!$session['user']['sex']?translate_inline("buddy"):translate_inline("lassie"));
		$over = 0;
		if (e_rand(1,get_module_setting('overwhelm'))==1) {
			output("`3Oh no! You seem to have attracted the most skilled of their kind!`n");
			output("They are lead by the famous %s`3 who is staring at you angrily!`n`n",$chief);
			$over=1;
		}
		addnav("Get combat-ready",$link."op=combatready&over=$over");
		break;
	case "leave":
		output("`3\"`\$And don't come back!`3\" is a faint sound in your back as you back off away.");
		output("`n");
		$session['user']['specialinc'] = "";
		addnav(array("Return to %s",$session['user']['location']),"village.php");
		break;
	case "hilfeichbineinadminholtmichhierraus":
		output("Due to your powers as a god you teleport yourself out of it.");
		$session['user']['specialinc'] = "";
		strip_buff('punisher_kunais');
		break;
	case "combatready":
		require_once("lib/battle-skills.php");
		$extraatt=round(e_rand(5,5+round($session['user']['level']+$session['user']['dragonkills']/2,0)),0);
		$extradef=$extraatt+2;
		$extrahp=$extraatt*20;
		$badguy = array(
		"creaturename"=>translate_inline($akatsuki." members"), //not so good for translation purposes if they switch the name often, but well
		"creaturelevel"=>$session['user']['level']+e_rand(1,3),
		"creatureweapon"=>translate_inline("Many deadly weapons"),
		"creatureattack"=>$session['user']['level']+$session['user']['dragonkills']+$extraatt,
		"creaturedefense"=>$session['user']['level']+$session['user']['dragonkills']+$extradef,
		"creaturehealth"=>$session['user']['level']*10+50+$extrahp,
		"diddamage"=>0,);
		if (httpget('over')) { //attack+defence depends on the dks... the more, the bigger the thread, the harder they fight... and win with it usually
// those 3 were never used (badguy is defined above)
//			$extrahp=round(e_rand($session['user']['level']+50,($session['user']['maxhitpoints']-$session['user']['level']*10)));
//			$extraatt=e_rand(10,$session['user']['dragonkills']+25);
//			$extradef=e_rand(10,$session['user']['dragonkills']+25);
			$badguy['creaturename']=$chief.translate_inline("`2 and ").$badguy['creaturename'];
			if ($city!='Iwagakure') $badguy['creatureweapon']=translate_inline("One-eyed Mangekyou Sharingan and ").$badguy['creatureweapon'];
				else $badguy['creatureweapon']=translate_inline("Two vicious whips and ").$badguy['creatureweapon'];

		}
		output("`n`2You cannot flee as you are completely surrounded!`n`n");
	   	$battle=true;
		$session['user']['badguy'] = createstring($badguy);
		$op = "combat";
		httpset('op', $op);
	case "combat": case "fight":
		if (e_rand(1,3)) {
			apply_buff('punisher_kunais',
				array(
				"name"=>"`qKunais!",
				"rounds"=>2,
				"mingoodguydamage"=>1,
				"maxgoodguydamage"=>5,
				"minioncount"=>1,
				"effectmsg"=>"`)A Kunai hits you for {damage} damage!",
				"schema"=>"module-akatsuki",
		));
		}
		include("battle.php");
		if ($victory){ //no exp at all
			output("`n`n`@...`!%s`^ members lie dead around you. You have managed to survive...somehow.",$akatsuki);
			output("`n%s`^ is nowhere to be seen and you decide to enter %s a bit more stealthly the next time.`n",$chief,$city);
			if (strstr($badguy['creaturename'],$chief)) {
				addnews("%s`^ survived an encounter with %s`^ while entering %s.",$session['user']['name'],$badguy['creaturename'],$city);
			} else {
				addnews("%s`^ survived an encounter with the local %s`^ while entering %s.",$session['user']['name'],$akatsuki,$city);
			}
			$session['user']['specialinc'] = "";
			$badguy=array();
			strip_buff('punisher_kunais');
			$session['user']['badguy']="";
	    }elseif ($defeat){ //but a loss of course if you die
			$exploss = $session['user']['experience']*get_module_setting("experienceloss")/100;
			output("`n`n`@You are dead... stroke down by %s`@ members`@.`n",$akatsuki);
			if ($exploss>0) output(" You lose `^%s percent`@  of your experience and all of your gold.",get_module_setting("experienceloss"));
			$session['user']['experience']-=$exploss;
			$session['user']['gold']=0;
			debuglog("lost $exploss experience and all gold due to the akatsuki $akatsuki.");
			strip_buff('punisher_kunais');
			if (strstr($badguy['creaturename'],$chief)) {
				addnews("%s`^ was killed by `^%s`^ while trying to enter %s`^.",$session['user']['name'],$badguy['creaturename'],$city);
			} else {
				addnews("%s`^ was killed by `^%s`^ members while trying to enter %s`^.",$session['user']['name'],$akatsuki,$city);
			}
			addnav("Return");
			addnav("Return to the Shades","shades.php");
			$session['user']['specialinc'] = "";
			$badguy=array();
			$session['user']['badguy']="";
	    }else{
			require_once("lib/fightnav.php");
			fightnav(true,false,$from."op=combat");
			if ($session['user']['superuser'] & SU_DEVELOPER) addnav("Escape to Village",$link."op=hilfeichbineinadminholtmichhierraus");
		}
		break;
	}

}

function akatsuki_run(){
}

function akatsuki_get() {
	$evilalign = get_module_setting('evilalign','alignment');
	$goodalign = get_module_setting('goodalign','alignment');
	$useralign = get_module_pref('alignment','alignment');
	//0 equals evil, 1 equals neutral, 2 equals good alignment
	if ($useralign <= $evilalign) return 0;
	if ($useralign >= $goodalign) return 2;
	return 1;
}
?>
