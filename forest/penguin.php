<?php

function penguin_getmoduleinfo(){
	$info = array(
		"name"=>"Penguin Overlord",
		"author"=>"Chris Vorndran, altered by `2Oliver Brendel",
		"version"=>"1.01",
		"category"=>"Forest",
		"download"=>"http://dragonprime.net/users/Sichae/penguin.zip",
		"settings"=>array(
			"Penguin HoF and Statue,title",
				"lastpenk"=>"Latest Penguin Killer!,int|0",
				"pp"=>"Display how many per page,int|25",
				"login-disp"=>"Display on Login Page?,bool|0",
				"scity"=>"What city does the statue show in,location|".getsetting("villagename", LOCATION_FIELDS),
			"Penguin Restrictions and Hero,title",
				"minlevel"=>"Minimum level until Penguin can be seen in the forest?,range,1,18,1|7",
				"mindk"=>"Minimum amount of DKs before Penguin can be seen in the forest?,int|0",
				"city"=>"Name of Penguin City,text|Pengington",
				"mayor-name"=>"Name of the Penguin City Mayor,text|Lamington",
			"Penguin Stat Control Panel,title",
				"weap"=>"Penguin's Weapon,text|Finger Ray",
				"hpflux"=>"Multiplier of user's hitpoints to set Penguin's HP,floatrange,1,2,.05|1.25",
				"atkflux"=>"Multiplier of user's attack to set Penguin's ATK,floatrange,1,2,.05|1.1",
				"defflux"=>"Multiplier of user's defense to set Penguin's DEF,floatrange,1,2,.05|1.1",
				"exp-win"=>"Multipler of user's experience at win,floatrange,0,1,.05|.15",
				"exp-lose"=>"Multipler of user's experience at loss,floatrange,0,1,.05|.1",
				"earn-gold"=>"Does the player earn gold at DK?,bool|1",
				"The amount of gold is based from the length of the user's name then raised to the 2.5 power.,note",
				"earn-gems"=>"Does the player earn gems at DK?,bool|1",
				"Gem gain is equivalent to the user's level.,note",
		),
		"prefs"=>array(
			"Penguin Overlord Prefs,title",
				"fal"=>"Has this user fought and lost to the Penguin?,bool|0",
				"faw"=>"Has this user fought and won against the Penguin?,bool|0",
				"penk"=>"Penguin Kills,int|0",
		),
	);
	return $info;
}
function penguin_install(){
	module_addhook("forest");
	module_addhook("newday");
	module_addhook("biostat");
	module_addhook("dragonkilltext");
	module_addhook("village-desc");
	module_addhook("footer-hof");
	module_addhook("index");
	return true;
}
function penguin_uninstall(){
	return true;
}
function penguin_dohook($hookname,$args){
	global $session;
	$city = get_module_setting("scity");
	switch ($hookname){
		case "forest":
			if ($session['user']['level'] >= get_module_setting("minlevel")
				&& $session['user']['dragonkills'] >= get_module_setting("mindk")){
					addnav("Fight");
					if (!get_module_pref("faw")) addnav("`QSeek out the Penguin`0","runmodule.php?module=penguin&op=enter");
			}
			break;
		case "newday":
			set_module_pref("fal",0);
			break;
		case "dragonkilltext":
			set_module_pref("faw",0);
			set_module_pref("fal",0);
			break;
		case "biostat":
			global $target;
			$penk = get_module_pref("penk","penguin",$target['acctid']);
			if ($penk > 0) output("`^Penguin Kills: `@%s`0`n",$penk);
			break;
		case "footer-hof":
			addnav("Warrior Rankings");
			addnav("Penguin Kills","runmodule.php?module=penguin&op=hof");
			break;
		case "village-desc":
			if ($session['user']['location'] == $city){
				$id = get_module_setting("lastpenk");
				$sql = "SELECT name FROM ".db_prefix("accounts")." WHERE acctid=$id";
				$res = db_query($sql);
				$row = db_fetch_assoc($res);
				if ($id == 0 || db_num_rows($res) == 0){
					$name = "MightyE";
				}else{
					$name = $row['name'];
				}
				output("`n`QLooking around, you notice a small gaggle of penguins erecting a statue.");
				output("This is a statue of `@%s`Q, the Penguin Killer!`n",$name);
				}
				break;
		case "index":
			if (get_module_setting("login-disp")){
				$p_killer = "MightyE";
				$hero = get_module_setting("lastpenk");
				if ($hero != 0) {
					$sql = "SELECT name FROM " . db_prefix("accounts") . " WHERE acctid='$hero'";
					$res = db_query($sql);
					$row = db_fetch_assoc($res);
					$p_killer = $row['name'];
				}
				output("`QThe most recent warrior to destroy the Penguin Overlord is: `&%s`0`n`n",$p_killer);
			}
			break;
		}
	return $args;
}
function penguin_run(){
	global $session;
	$op = httpget('op');
	$page = httpget('page');

	$m_name = get_module_setting("mayor-name");
	// Fluxes
	$hpflux = get_module_setting("hpflux");
	$atkflux = get_module_setting("atkflux");
	$defflux = get_module_setting("defflux");
	// Now, let's build our stats...
	$hp = (floor($session['user']['maxhitpoints']*$hpflux));
	$atk = (floor($session['user']['attack']*$atkflux));
	$def = (floor($session['user']['defense']*$defflux));
	$name = translate_inline("Penguin Overlord");
	$badguy = array(
		"creaturename"=>$name,
		"creatureweapon"=>get_module_setting("weap"),
		"creaturelevel"=>$session['user']['level'],
		"creatureattack"=>$atk,
		"creaturedefense"=>$def,
		"creaturehealth"=>$hp,
		"creatureaiscript"=>"global \$session; if (\$session['user']['dragonkills']>8) require('modules/penguinoverlord/kabuto.php');",
		"diddamage"=>0
		);
	page_header(array("%s: The City of Penguins",get_module_setting("city")));
	switch ($op){
		case "hof":
			page_header("Hall of Fame");
			$pp = get_module_setting("pp");
			$pageoffset = (int)$page;
			if ($pageoffset > 0) $pageoffset--;
			$pageoffset *= $pp;
			$from = $pageoffset+1;
			$limit = "LIMIT $pageoffset,$pp";
			$sql = "SELECT COUNT(userid) AS c FROM " . db_prefix("module_userprefs") . " WHERE modulename = 'penguin' AND setting = 'penk' AND value >= '1'";
			$result = db_query($sql);
			$row = db_fetch_assoc($result);
			$total = $row['c'];
			$count = db_num_rows($result);
			if ($from + $pp < $total){
				$cond = $pageoffset + $pp;
			}else{
				$cond = $total;
			}
			$sql = "SELECT ".db_prefix("module_userprefs").".value, ".db_prefix("accounts").".name, ".db_prefix("accounts").".level FROM ".db_prefix("module_userprefs")." , ".db_prefix("accounts"). " WHERE acctid = userid AND modulename = 'penguin' AND setting = 'penk' AND value >= '1' ORDER BY (value+0) DESC $limit";
			$result = db_query($sql);
			$rank = translate_inline("Rank");
			$name = translate_inline("Name");
			$pk = translate_inline("Penguin Kills");
			$lev = translate_inline("Level");
			rawoutput("<big>");
			output("`c`b`^Penguin Killers of the Land`b`c`0`n");
			rawoutput("</big>");
			rawoutput("<table border='0' cellpadding='2' cellspacing='1' align='center' bgcolor='#999999'>");
			rawoutput("<tr class='trhead'><td>$rank</td><td>$name</td><td>$pk</td><td>$lev</td></tr>");
			if (db_num_rows($result)>0){
				for($i = $pageoffset; $i < $cond && $count; $i++) {
					$row = db_fetch_assoc($result);
					if ($row['name']==$session['user']['name']){
						rawoutput("<tr class='trhilight'><td>");
					} else {
						rawoutput("<tr class='".($i%2?"trdark":"trlight")."'><td>");
					}
					$j=$i+1;
					output_notl("$j.");
					rawoutput("</td><td>");
					output_notl("`&%s`0",$row['name']);
					rawoutput("</td><td>");
					output_notl("`c`@%s`c`0",$row['value']);
					rawoutput("</td><td>");
					output_notl("`c`@%s`c`0",$row['level']);
					rawoutput("</td></tr>");
					}
				}
				rawoutput("</table>");
			if ($total>$pp){
				addnav("Pages");
				for ($p=0;$p<$total;$p+=$pp){
					addnav(array("Page %s (%s-%s)", ($p/$pp+1), ($p+1), min($p+$pp,$total)), "runmodule.php?module=penguin&op=hof&page=".($p/$pp+1));
				}
			}
			blocknav("forest.php");
			addnav("Other");
			addnav("Back to HoF", "hof.php");
			break;
		case "enter":
			output("`@Exitting the forest, you stumble upon a small city.");
			output("A very small city in fact... inhabited by Penguins.");
			output("A regal looking Penguin walks over to you, and says, \"`QHello there...`@\"");
			output("You extend your hand, to which he quickly inspects it, noting the scars and such.");
			output("He jumps in glee, \"`QAre ye here to destroy the Penguin Overlord?!`@\"");
			output("He shakes his head quickly, \"`QWhere are my manners... my name is %s.`@\"",$m_name);
			addnav("Options");
			addnav("Continue","runmodule.php?module=penguin&op=cave");
			addnav("Leave","runmodule.php?module=penguin&op=leave");
			break;
		case "cave":
			output("`@The regal penguin tucks his coattails behind him, and continues to walk with you through the city.");
			output("\"`QA long time ago, one of my own children had run away from the city.");
			output("In his running, he had found this cave up here.");
			output("Inside of this cave, is a massive crystal... quite radioactive.");
			output("This crystal gave my son super-penguin strength, and since then... he has been exacting his revenge upon our city.");
			output("It is pertinent that someone destroys him soon... he may be my son, but I did not raise him to do these things.");
			output("So, will you help us, and eradicate our problem?`@\"");
			output("One small penguin walks over to you, and places a necklace around your neck.");
			addnav("Options");
			addnav("Enter Cave","runmodule.php?module=penguin&op=pre");
			addnav("Leave","runmodule.php?module=penguin&op=leave");
			break;
		case "pre":
			output("`@%s smiles, and shakes your hand.",$m_name);
			output("He thanks you, before running back to town.`0");
			$session['user']['badguy'] = createstring($badguy);
			$op = "fight";
			httpset("op",$op);
			break;
		case "leave":
			villagenav();
			addnav("Options");
			output("`@%s's smile fades and then he nods.",$m_name);
			output("\"`QI understand... it is your time.");
			output("If you wish to spend it elsewhere, I understand.");
			output("Well, take care...`@\"");
			output("He takes one last look at you, as you depart from the city.");
			output("Penguin children swarm to your feet, beckoning you not to go.");
			addnav("Fight Penguin!","runmodule.php?module=penguin&op=pre");
			break;
		}
	$battle=false;
	if ($op == "fight"){
		$battle = true;
	}
	if (isset($battle) && $battle){
		include("battle.php");
			if ($victory){
				$exp = round($session['user']['experience']*get_module_setting("exp-win"));
				$gold=0;
				$gems=0;
				if (get_module_setting("earn-gold")) $gold = round(pow(min(9,strlen($session['user']['name'])),2.5))+e_rand(500,1000);
				if (get_module_setting("earn-gems")) $gems = $session['user']['level'];
				set_module_setting("lastpenk",$session['user']['acctid']);
				set_module_pref("faw",1);
				$penk = get_module_pref("penk");
				$penk++;
				set_module_pref("penk",$penk);
				output("`n`@%s runs to the mouth of the cave, eager to shake your hand.",$m_name);
				output("\"`QThank you, so very much!");
				output("Now, we may live in peace...`@\"");
				output("`n`nNear the corner of the cave, you see a baby penguin walking inside of the cave.");
				output("Thinking nothing bad can come of it, you let the youngster on its way.");
				output("Next to the Cave, you etch another mark in, bringing your Penguin Kills to `^%s`@.",$penk);
				addnews("`@%s `Qhas slain the Penguin, and saved the entire Penguin City from death and destruction.`0",$session['user']['name']);
				$session['user']['experience']+=$exp;
				if (get_module_setting("earn-gold")) $session['user']['gold']+=$gold;
				if (get_module_setting("earn-gems")) $session['user']['gems']+=$gems;
				debuglog("attained $exp experience, $gold gold and $gems gems for slaying the Penguin!");
				output("`n`n`^Spoils:`n");
				output("`^Experience: `@%s`n",$exp);
				if (get_module_setting("earn-gold")) output("`^Gold: `@%s`n",$gold);
				if (get_module_setting("earn-gems")) output("`^Gems: `@%s`n",$gems);
			}elseif($defeat){
				$exp = round($session['user']['experience']*get_module_setting("exp-lose"));
				$session['user']['alive']=false;
				$session['user']['gold']=0;
				$session['user']['hitpoints']=0;
				set_module_pref("fal",1);
				$session['user']['experience']-=$exp;
				debuglog("lost $exp experience to the Penguin");
				output("`@Penguin strikes down with his `^%s`@, and your body is crushed.",get_module_setting("weap"));
				output("He begins to cackle, and smiles softly.");
				output("`\$Charon `@comes forth, and whisks your body off to %s`@.",getsetting('deathoverlord','`$Ramius'));
				addnews("%s `Qhas been destroyed by the Penguin Overlord.",$session['user']['name']);
				addnav("Return to the Shades","shades.php");
				blocknav("forest.php");
			}else{
				$script = "runmodule.php?module=penguin&op=fight";
				require_once("lib/fightnav.php");
				fightnav(true,true,$script);
				blocknav("forest.php");
			}
		}
	addnav("Leave");
	addnav("Return to the Forest","forest.php");
page_footer();
}
?>
