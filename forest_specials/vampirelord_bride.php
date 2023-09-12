<?php
/*
Meet the vampirelord_bride in the woods...

*/
function vampirelord_bride_getmoduleinfo() {
	$info = array(
		"name"=>"Vampire Lord's Bride",
		"author"=>"`2Oliver Brendel",
		"version"=>"1.0",
		"category"=>"Forest Specials",
		"download"=>"",
		"settings"=>array(
			"The Bride of Baluski - Preferences, title",
			"level"=>"Level at which the player might escape (2-15),floatrange,2,15,1|10",
			"overwhelm"=>"1 in x chance they are really really strong,floatrange,2,25,1|6",
			"weapon"=>"Name of her Weapon,text|`QTen`qro",
			"name"=>"Name of the Bride,text|`!S`^asam`!i",
			"experienceloss"=>"Percentage: How many experience is lost after a fight,floatrange,1,100,1|10",
		),
	"requires"=>array(
		"vampirelord"=>"1.1|Mike Counts, rewritten `2Oliver Brendel",
		),
	);
	return $info;
}
function vampirelord_bride_install() {
	module_addeventhook("forest", "return 50;");
	return true;
}
function vampirelord_bride_uninstall() {
	return true;
}
function vampirelord_bride_dohook($hookname,$args) {
	return $args;
}

function vampirelord_bride_addimage($args) {
	if (is_module_active('addimages')) {
		if (get_module_pref('user_addimages','addimages')) {
			output_notl("`c<img src=\"modules/vampirelord_bride/".$args."\" alt=\"$args\">`c<br>\n",true);
		}
	}
}

function vampirelord_bride_runevent($type,$link) {
	global $session;
	$from = "forest.php?";
	$session['user']['specialinc'] = "module:vampirelord_bride";
	$op = httpget('op');
	$vampirelord=get_module_setting("vampirelord","vampirelord");
	$bride=sprintf_translate("`4T`\$he `jBride `4Of The %s",$vampirelord);
	if (!is_module_active("vampirelord")) $vampirelord=translate_inline("Vampire Lord");
	require_once("./modules/alignment/func.php");
	switch ($op) {
		case "":
			vampirelord_bride_addimage('wedding_0.jpg');
			output("`3You are on your way through the forest you suddenly feel dizzy... and after a short while you see a plain scene, looking like where a wedding might take place soon...`n`nA cake ready to be eaten, an altar, everything...Strange, you see nobody around...`n`n");
			output_notl("`n`n");
			output("`n`4What do you do?");
			addnav("Stand and wait",$link."op=stand");
			addnav("Eat the cake",$link."op=cake");
			addnav("Run away",$link."op=run");
			break;
		case "stand":
			output("`3After a few minutes you notice somebody sits on the small shrine-like building on the right side...`n");
			vampirelord_bride_addimage('wedding_1.jpg');
			$ali=vampirelord_bride_get();
			output_notl("`n`n");
			if ($ali==0 || $session['user']['acctid']==7) {
				$gender=(!$session['user']['sex']?translate_inline("nin"):translate_inline("cutie"));
				output("\"`\$So, what do we have here? An evil %s trying to sneak through the woods?",$gender);
				output(" Trying to interrupt...something?`3\"...");
				if ((is_module_active('curse_seal') && get_module_pref('hasseal','curse_seal')>0) || $session['user']['acctid']==7) {
					output("Her eyes narrow as she focusses you and continues... \"`\$You bear the sign of my husband. The sign only he can have bestowed you, I don't smell the `@Snake`\$ taint on you... so you fight him?");
					addnav("Who are you?",$link."op=who");
					addnav("Choices");
					addnav("Yes, I do",$link."op=yes");
					addnav("No, I don't",$link."op=no");
					addnav("Attack her",$link."op=combatready");
				} else {
					addnav("Get combat-ready",$link."op=combatready");
				}
			} elseif ($ali==1) {
				output("It seems nobody she is not really interested in you... she vanishes into a cloud of dust and everything goes back to silent and you don't really have time to waste...so you move on.");
				addnav("Continue your journey",$link."op=leave");
			} else {
				output("\"`\$Greetings! You try to interrupt a little ceremony here... `bour`b renewal ceremony...`3\", says %s`3.",$bride);
				output("`n`n\"`\$So much for politeness. Prepare to `bdie`b!`3\"");
				addnav("Get combat-ready",$link."op=combatready&good=1");
			}
			break;
		case "yes":
			vampirelord_bride_addimage('wedding_4.jpg');
			output("`3She seems to ... smile... well, you certainly hope that is just a smile and not bloodlust you see...`n`n");
			output("\"`\$Great... so... as you are true to my husband and myself... what do you like?`n`nI have a bag of stuff I recently obtained from ...er... an unexpected wedding guest... some gold or a kiss on the cheek...maybe...?`3\"...`n`n`4What do you choose?");
			addnav("The Bag",$link."op=bag");
			addnav("The Gold",$link."op=gold");
			addnav("The `\$Kiss",$link."op=kiss");
			break;
		case "gold":
			vampirelord_bride_addimage('wedding_4.jpg');
			$gold=e_rand(32,35)*e_rand(3,min(50,$session['user']['intelligence']))*2;
			output("She silently hand you a pouch with %s gold in it, and leaves you without another word.",$gold);
			$session['user']['gold']+=$gold;
			$session['user']['specialinc'] = "";
			break;
		case "cake":
			output("`3After a few minutes you notice somebody sits on the small shrine-like building on the right side... wearing a large katana on the back, and absolutely pissed...`n");
			vampirelord_bride_addimage('wedding_angry.jpg');
			output("\"`\$Time to die, human!`3\"...");
			addnav("Defend yourself!",$link."op=combatready&good=2");
			break;
		case "bag":
			vampirelord_bride_addimage('wedding_4.jpg');
			if (is_module_active('inventory')) {
				require_once("modules/inventory/lib/itemhandler.php");
				$limit=4;
				$sql="SELECT itemid,name,uniqueforplayer FROM ".db_prefix('item')." WHERE class='Loot' OR class='Potion' ORDER BY RAND() LIMIT $limit;";
				$result=db_query($sql);
				$row=db_fetch_assoc($result);
				output("`3She hands you a bag... you search through it while she takes her leave...`n`n");
				$bagempty=1;
				while ($row=db_fetch_assoc($result)) {
					$amount=e_rand(1,3);
					if ($row['uniqueforplayer']) {
						$amount=1;
						if (check_qty_by_id((int)$row['itemid'])>0) continue;
					}
					output("`n`n`7You receive %s x %s`7!",$amount,$row['name']);
					$bagempty=0;
					add_item_by_id($row['itemid'],$amount);
				}
				if ($bagempty) output("`n`n`7And it's empty... what a bummer...");
			} else {
				$gold=e_rand(32,35)*e_rand(3,min(50,$session['user']['intelligence']))*2;
				output("She silently hand you a pouch with %s gold in it, and leaves you without another word.",$gold);
				$session['user']['gold']+=$gold;
			}
			$session['user']['specialinc'] = "";
			break;
		case "kiss":
			vampirelord_bride_addimage('wedding_3.jpg');
			output("`3\"`\$Then take my `b`jKiss`b`\$ that will clear your conscience, wipe away your good and evil deeds and grant you a new start on your alignment... you can become evil again ... I give you this chance... do you really want to do this...`iand don't tell my husband...`i`3\"");
			output("`n`nShe looks ... aroused...");
			addnav("Yes, I want her!",$link."op=kissher");
			addnav("Chicken out!",$link."op=chicken");
			break;
		case "kissher":
			$halloween=(date("m-d")=="11-01"?1:0);
			$halloween= $halloween || (date("m-d")=="10-31"?1:0);
			
		
			if (!$halloween) {
				vampirelord_bride_addimage('wedding_kiss.jpg');
			} else {
				vampirelord_bride_addimage('release.jpg');
			}
			output("`4Without another word, she takes your head and presses herself against you, so much you nearly stumble.`n`n");
			output("The world begins to spin in bitter sweetness brought by her cold hot lips... you see only whit flakes dancing in front of you eyes... ");

			if ($halloween) {
				output("as you two separate, she wisphers into your ear: `3\"`\$By the way... `QH`qappy `QH`qalloween`\$... 謳え,`q Utae,\$ 羚騎士, `qGamyūsa`3\"...`n`n`kYou think you've been had... `\$<h1>O.o</h1>",true);
				addnav("Survive...",$link."op=combatready");
				$session['user']['specialmisc']="halloween";
			} else {
				output("whatever good or evil you've done vanishes from your conscience, as you wake up several hours later on an empty clearing, you feel `#Neutral`3.");
				require_once("modules/alignment/func.php");
				$align=get_module_setting('goodalign','alignment')-get_module_setting('evilalign','alignment');
				set_align(round($align/2)+get_module_setting('evilalign','alignment'));
				addnews("`%%s`7 had a not-so-grave encounter with the %s`7 in the forest.", $session['user']['name'],$bride);
				$session['user']['specialinc'] = "";
			}
			
			break;
		case "chicken":
			vampirelord_bride_addimage('wedding_bride.jpg');
			output("`3\"`\$I thought so... now leave this place...`3\"");
			addnav("Continue your journey",$link."op=leave");
			break;
		case "who":
			vampirelord_bride_addimage('wedding_casual.jpg');
			output("`3\"`\$Ah, I haven't introduce myself, I am %s`\$, %s`\$. Your name is of no importance to me, as we will soon part, hopefully?`3\"",get_module_setting('name'),$bride);
			output("`n`n\"`\$So ... you have not answered my question. Do you fight `@Orochimaru`\$?`3\"");
			addnav("Choices");
			addnav("Yes, I do",$link."op=yes");
			addnav("No, I don't",$link."op=no");
			break;
		case "no":
			vampirelord_bride_addimage('wedding_bride.jpg');
			output("`3\"`\$In this case, we have nothing more to talk. I will rip that seal off your skin!`3\"");
			addnav("Defend yourself!",$link."op=combatready&good=1");
			break;
		case "run":
			output("`3Ah, only a weird place... better be on the road, maybe doing some killing and looting?");
			addnav("Continue your journey",$link."op=leave");
			break;
		case "leave":
			output("`3You continue on your journey and forget that weird place very soon.`n`n");
			$session['user']['specialinc'] = "";
			break;
		case "hilfeichbineinadminholtmichhierraus":
			output("Due to your powers as a god you teleport yourself out of it.");
			$session['user']['specialinc'] = "";
			break;
		case "combatready":
			require_once("lib/battle-skills.php");
			$extraatt=e_rand(1,$session['user']['level']);
			$extradef=$extraatt;
			$extrahp=$extraatt*20;
			$good=((int)httpget('good'))+1;
			if (e_rand(0,1)) { //attack+defence depends on the dks... the more, the bigger the thread, the harder they fight... and win with it usually
				$extrahp=round(e_rand($session['user']['level']+50,($session['user']['maxhitpoints']-$session['user']['level']*10)));
				$extraatt=e_rand(10,$session['user']['dragonkills']+5);
				$extradef=e_rand(10,$session['user']['dragonkills']+5);
			}
			$badguy = array(
				"creaturename"=>$bride, 
				"creaturelevel"=>$session['user']['level']+e_rand(1,3),
				"creatureweapon"=>get_module_setting('weapon'),
				"creatureattack"=>$session['user']['level']*$good+$session['user']['dragonkills']+$extraatt,
				"creaturedefense"=>$session['user']['level']*$good+$session['user']['dragonkills']+$extradef,
				"creaturehealth"=>$session['user']['level']*10+50+$extrahp*$good,
				"creatureaiscript"=>"require('modules/vampirelord_bride/script.php');",
				"image"=>"modules/vampirelord_bride/wedding_fight.jpg",
				"diddamage"=>0,
			);			
		   	$battle=true;
			$session['user']['badguy'] = createstring($badguy);
			$op = "combat";
			httpset('op', $op);
			if ($session['user']['specialmisc']=="halloween") {
				$badguy['creaturename']="\$ネリエル・トゥ・オーデルシュヴァンク, `QN`qerieru `QT`qu `QŌ`qderushuvanku";
				$badguy['image']='modules/vampirelord_bride/release.jpg';
				$badguy['creatureweapon']="羚騎士 `QG`qamyūsa";
				$badguy["creatureattack"]*=2;
				$badguy["creaturehealth"]*=2;
				$badguy['hidehitpoints']=1;
				$session['user']['badguy'] = createstring($badguy);
				
			} 		
		case "combat": case "fight":

			include("battle.php");
			if ($victory){ //no exp at all
				output("`n`n`@...`!%s`^ vanishes somehow, turning into dust and, like the morning mist, dissipating into the woods.... You have not seen the last of her, you are sure of that... but for now, you live....",$bride);
				addnews("%s`^ survived an encounter with %s`^ in the woods of %s.",$session['user']['name'],$bride,$session['user']['location']);
				if (is_module_active('inventory')) {
					require_once("modules/inventory/lib/itemhandler.php");
					$limit=4;
					$sql="SELECT itemid,name,uniqueforplayer FROM ".db_prefix('item')." WHERE class='Loot' OR class='Scroll' ORDER BY RAND() LIMIT $limit;";
					$result=db_query($sql);
					output("`n`nShe leaves some stuff behind:`n`n");
					$bagempty=1;
					while ($row=db_fetch_assoc($result)) {
						$amount=e_rand(1,3);
						if ($row['uniqueforplayer']==1) {
							$amount=1;
							if (get_inventory_item((int)$row['itemid'])) continue;
						}
						output("`n`n`7You receive %s x %s`7!",$amount,$row['name']);
						$bagempty=0;
						add_item_by_id($row['itemid'],$amount);
					}
					if ($bagempty) output("`n`n`7All of it useless junk... what a bummer...");
				}
				$session['user']['specialinc'] = "";
				$session['user']['specialmisc']="";
				$badguy=array();
				$session['user']['badguy']="";
		    }elseif ($defeat){ //but a loss of course if you die
				$exploss = $session['user']['experience']*get_module_setting("experienceloss")/100;
				output("`n`n`@You are nearly dead... struck down by %s`@.`n",$badguy['creaturename']);
				if ($session['user']['specialmisc']!="halloween") output("Sadly, it seems now dinner is served....`n");
				if ($session['user']['specialmisc']!="halloween") vampirelord_bride_addimage('wedding_dinner.jpg');
				if ($exploss>0) output(" You lose `^%s percent`@  of your experience and all of your gold.",get_module_setting("experienceloss"));
				$session['user']['experience']-=$exploss;
				$session['user']['gold']=0;
				debuglog("lost $exploss experience and all gold due to the vampirelord_bride ".sanitize($bride));
				if ($session['user']['specialmisc']=="halloween") {
					addnews("%s`^ was found lifeless and badly violated in the woods of %s`^.",$session['user']['name'],$session['user']['location']);
				} else {
					addnews("%s`^ was found sucked dry of all blood in the woods of %s`^.",$session['user']['name'],$session['user']['location']);
				}
				addnav("Return");
				addnav("Return to the Shades","shades.php");
				$session['user']['specialinc'] = "";
				$session['user']['specialmisc']="";
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

function vampirelord_bride_run(){
}

function vampirelord_bride_get() {
	$evilalign = get_module_setting('evilalign','alignment');
	$goodalign = get_module_setting('goodalign','alignment');
	$useralign = get_module_pref('alignment','alignment');
	//0 equals evil, 1 equals neutral, 2 equals good alignment
	if ($useralign <= $evilalign) return 0;
	if ($useralign >= $goodalign) return 2;
	return 1;
}
?>
