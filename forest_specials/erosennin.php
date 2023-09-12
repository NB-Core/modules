<?php
/*
   Meet Ero-Sennin in the woods...

   you find him peeping at some bathing girls...

   v1.01 minor fixes
   v1.02 fix in the gain of attackpoints (forgot to add)
   v1.0.3 added hook

 */

function erosennin_getmoduleinfo() {
	$info = array(
			"name"=>"Ero Sennin - The Perverted Hermit",
			"author"=>"`2Oliver Brendel",
			"version"=>"1.02",
			"category"=>"Forest Specials",
			"download"=>"http://lotgd-downloads.com",
			"settings"=>array(
				"Ero Sennin - Preferences, title",
				"Meet him and maybe the frog boss,note",
				"name"=>"Name (coloured) of Ero-Sennin,text|`QEro-`gSennin",
				"charme"=>"Charm value for female players to get stalked,int|30",
				"experienceloss"=>"Percentage: How many experience is lost/won after a fight,floatrange,1,100,1|10",
				),
			"prefs"=>array(
				"favour"=>"Favours with Ero-Sennin,int|0",
				),
		     );
	return $info;
}
function erosennin_install() {
	module_addeventhook("forest", "return 100;");
	return true;
}

function erosennin_uninstall() {
	return true;
}

function erosennin_dohook($hookname,$args) {
	return $args;
}

function erosennin_runevent($type,$link) {
	global $session;
	$from = "forest.php?";
	$session['user']['specialinc'] = "module:erosennin";
	$op = httpget('op');
	$erosennin=get_module_setting("name");
	$charmmax=get_module_setting("charme");
	switch ($op) {
		case "":
			output("`3You walk along a peaceful road... after you walked a few minutes, you see a little bath house built near a hot spring right beside the road.");
			output(" You decide to get a bit closer... since it's on your way though.");
			$adj=($session['user']['sex']?translate_inline("disgusting"):translate_inline("interesting"));
			output("`n`nOh! That's %s... an old man sits right at a wooden fence and peeks through a hole!",$adj);
			output("`n`nWhat do you want to do?");
			addnav("Call To Order",$link."op=disturb");
			addnav("Peek Together",$link."op=peek");
			modulehook("erosennin_favours",array("favour"=>get_module_pref('favour')));
			addnav("Walk away",$link."op=walk");
			break;
		case "peek":
			$rand=e_rand(1,2);
			rawoutput("<center><img src='modules/erosennin/images/onsen$rand.jpg'></center><br>");
			//provide a hook for more options, like rasengan, if ero-sennin is pleased
			//end
			output("`3You ask silently if you can take a look too... the old man realizes your presence and takes a look at you.`n`n");
			output("'`QShhh... get your own hole! I am gathering data right now!`3'");
			if ($session['user']['sex'] && $charmmax<$session['user']['charm']) {
				increment_module_pref("favour",1);
				output("`n`nHe takes a `\$very`3 good look at you... and starts to drool.");
				output("'`QOh... what ripe fruits you have brought with you... you have some awesome things there...`3'");
				output(" You feel somehow ill seeing that old geezer gaze at you like that.");
				output("`n`nYou start to run away, but he is after you... and won't leave your trace for a while.");
				apply_buff('senninpeek1',
						array(
							"name"=>"`QEro-Sennin",
							"rounds"=>100,
							"wearoff"=>"You seem to have lost him. Finally.",
							"atkmod"=>0.8,
							"defmod"=>0.9,
							"minioncount"=>1,
							"survivenewday"=>1, //he keeps following :D
							"roundmsg"=>"`)'`QWhooow, what a nice rear! Show it to me, baby!`)'",
							"schema"=>"module-erosennin",
						     ));
				$session['user']['specialinc'] = "";
				forest(true);
			}
			output("In fact, you find a nice hole to peek through.");						output(" You take a good look... and well.. what nice bodies... rrrr....");
			output(" fresh, young females... ready to be gazed at...");
			$randomchance=e_rand(1,3);
			increment_module_pref("favour",1);
			switch ($randomchance) {
				case "1":
					output("You are amazed... nice bodies... freshly riped... you feel `%energized`3!`n`n");

					apply_buff('senninpeek2',
							array(
								"name"=>"`QEro-Sennin Peek",
								"rounds"=>30,
								"wearoff"=>"Your memory fades away.",
								"atkmod"=>1.2,
								"defmod"=>1.1,
								"minioncount"=>1,
								"roundmsg"=>"You remember the nice female bodies!",
								"schema"=>"module-erosennin",
							     ));
					break;
				case "2":
					output("You are not that satisfied... you have higher standards.`n`n");
					break;
				case "3":
					increment_module_pref("favour",-1);
					output("Oh my! You little oaf! You leaned against the fence too strongly!");
					output("The ladies are now a bit angry... and the old man is gone!");
					output("`n`n`$ You are beaten to a pulp by the bathing ladies!`n`n");
					addnews("%s`^ was beaten to a pulp for peeking by half-naked ladies!",$session['user']['name']);
					$session['user']['hitpoints']=e_rand(1,$session['user']['hitpoints']/2);
					break;

			}
			$gendercall=(!$session['user']['sex']?translate_inline("boy"):translate_inline("cutie"));
			if (e_rand(1,3)==1 && $randomchance<>3)	{
				increment_module_pref("favour",-1);
				output("'`QHey, %s, I like your style. I will teach you a secret to let you gain some offensive power.`3'",$gendercall);
				output("`n`nYou ask him: '`@And what is your name?`3'... and it takes a few moments...");
				output(" then he says: '`QThank you for asking! I am the `!Gama-Sennin`Q from the Myouboku Mountain!`3'`n`n");
				output("You ponder about him... and realize you have heard of him before: '`@You are no Gama-Sennin (frog hermit)! You are the legendary %s`@!!!'`3",$erosennin);
				output("`n`nAfter a few hours of argument, you leave the place with some new secrets in your brain.");
				output("`n`nYou `^gain`3 `$ two `3temporary attackpoints! (will vanish after the DK)");
				$session['user']['attack']+=2;
				debuglog("Gained 2 attackpoints from erosennin");
			}
			$session['user']['specialinc'] = "";
			break;
		case "walk":
			output("`3You don't mind the old man peeping... and continue on your journey.`n`n");
			$session['user']['specialinc'] = "";
			break;
		case "disturb": //players who try to harm her have to fight against her protector ;) and they receive no mercy
			increment_module_pref("favour",-1);
			output("`3You walk towards him... he doesn't seem to realize anything except for nudity...`n");
			output("You utter loudly: '`@What are you doing here, old man? Peeking is a crime, you know?`3'");
			output(" He seems to be very surprised and turns around... he has some sad look in his eyes... but now he seems to be angry!`3`n`n");
			output("'`QBaka baka baka... You scared the nice ladies away... you need to be taught a lesson!`3'`n`n");
			output("`^Inu...Ii.. Tori... Saru... O-hitsuji... Ninpou Kuchiyose no Jutsu!`3`n`n");
			$selection=0;
			require_once("lib/battle-skills.php");
			if ($session['user']['level']<5)
			{
				output("A small frog warrior appears right before you... and attacks immediately.");
				$badguy = array(
						"creaturename"=>translate_inline("a small Frog Warrior"),
						"creaturelevel"=>$session['user']['level']+1,
						"creatureweapon"=>translate_inline("Frog Kiss"),
						"creatureattack"=>$session['user']['level']+$session['user']['dragonkills']+1,
						"creaturedefense"=>$session['user']['defense'],
						"creaturehealth"=>($session['user']['level']*10+round(e_rand($session['user']['level'],($session['user']['maxhitpoints']-$session['user']['level']*10)))),
						"diddamage"=>0,);
			} elseif ($session['user']['level']<10) {
				output("A big frog warrior appears right before you... and attacks immediately.");
				$badguy = array(
						"creaturename"=>translate_inline("a Greater Frog Warrior"),
						"creaturelevel"=>$session['user']['level']+1,
						"creatureweapon"=>translate_inline("Two scimitars"),
						"creatureattack"=>$session['user']['level']+$session['user']['dragonkills']+3,
						"creaturedefense"=>$session['user']['defense']+1,
						"creaturehealth"=>($session['user']['level']*10+round(e_rand($session['user']['level'],($session['user']['maxhitpoints']-$session['user']['level']*10)))),
						"diddamage"=>0,);
			} elseif ($session['user']['hashorse']>0) {
				$id = $session['user']['hashorse'];
				$sql = "SELECT mountname,mountbuff FROM ".db_prefix("mounts")." WHERE mountid=$id";
				$result = db_query($sql);
				$row = db_fetch_assoc($result);
				$mname = sanitize($row['mountname']);
				if (stristr($mname,"Gamabunta") && isset($session['bufflist']['mount']) && !$session['bufflist']['mount']['suspended'] )
				{
					output("Oh no! It seems that he summoned the frog boss! It's `^%s`3!",$row['mountname']);
					suspend_buff_by_name("mount",array("`b`n`nWell, %s`3 has disappeared from your side... and is now loyal to the strange old man... you have to fight against your own mount!`b`0",$row['mountname']));
					$badguy = array(
							"creaturename"=>$row['mountname'],
							"creaturelevel"=>$session['user']['level']+1,
							"creatureweapon"=>translate_inline("Suiton Teppoudama"),
							"creatureattack"=>$session['user']['level']+$session['user']['dragonkills']+5,
							"creaturedefense"=>$session['user']['defense']+$session['user']['dragonkills'],
							"creaturehealth"=>($session['user']['level']*10+50+round(e_rand($session['user']['level'],($session['user']['maxhitpoints']-$session['user']['level']*10)))),
							"diddamage"=>0,);
				} else {
					output("Oh no! It seems that he summoned the frog boss! It's `^%s`3!",translate_inline("Gamabunta"));
					$badguy = array(
							"creaturename"=>translate_inline("Gamabunta"),
							"creaturelevel"=>$session['user']['level']+1,
							"creatureweapon"=>translate_inline("Suiton Teppoudama"),
							"creatureattack"=>$session['user']['level']+$session['user']['dragonkills']+5,
							"creaturedefense"=>$session['user']['level']+$session['user']['dragonkills'],
							"creaturehealth"=>($session['user']['level']*10+50+round(e_rand($session['user']['level']+50,($session['user']['maxhitpoints']-$session['user']['level']*10)))),
							"diddamage"=>0,);
				}

			} else {
				//our dude is >=level 10 and has no horse, the poor guy
				output("A small mounted frog warrior appears right before you... and attacks immediately. `nHe smirks upon the fact that you are indeed without a steed... and parades around you.");
				$badguy = array(
						"creaturename"=>translate_inline("a small mounted Frog Warrior"),
						"creaturelevel"=>$session['user']['level']+3,
						"creatureweapon"=>translate_inline("Frog Kiss"),
						"creatureattack"=>$session['user']['level']+$session['user']['dragonkills']+5,
						"creaturedefense"=>$session['user']['defense']+5,
						"creaturehealth"=>($session['user']['level']*15+round(e_rand($session['user']['level'],($session['user']['maxhitpoints']-$session['user']['level']*10)))),
						"diddamage"=>0,);
			}
			$battle=true;
			$session['user']['badguy'] = createstring($badguy);
			$op = "combat";
			httpset('op', $op);
		case "combat": case "fight":
			include("battle.php");
			if ($victory){ //no exp at all for such a foul act
				output("`n`n`@...%s`^ dies by your hand. You have managed to survive...somehow.",$badguy['creaturename']);
				addnews("%s`^ survived an encounter with %s`^.",$session['user']['name'],$erosennin);
				$session['user']['specialinc'] = "";
				$exploss = $session['user']['experience']*get_module_setting("experienceloss")/100;
				if ($exploss>0) output(" You gain `^%s percent`@	experience!",get_module_setting("experienceloss"));
				$session['user']['experience']+=$exploss;
				$badguy=array();
				$session['user']['badguy']="";
				$id = $session['user']['hashorse'];
				if (isset($id) && (int)$id>0) {
					$sql = "SELECT mountname FROM ".db_prefix("mounts")." WHERE mountid=$id";
					$result = db_query($sql);
					$row = db_fetch_assoc($result);
					$mname = sanitize($row['mountname']);
					if (stristr($mname,"Gamabunta")) {
						unsuspend_buff_by_name("mount",array("`b`n`n%s`@ vanishes... and after a few minutes reappear at your side... as loyal and healthy as ever!`b`0",$row['mountname']));
					}
				}
			}elseif ($defeat){ //but a loss of course if you die
				$id = $session['user']['hashorse'];
				if (isset($id) && (int)$id>0) {
					$sql = "SELECT mountname FROM ".db_prefix("mounts")." WHERE mountid=$id";
					$result = db_query($sql);
					$row = db_fetch_assoc($result);
					$mname = sanitize($row['mountname']);
					if (stristr($mname,"Gamabunta"))
					{
						unsuspend_buff_by_name("mount",array("`b`n`n%s`@ will wait for you in the mortal world again.`b`0",$row['mountname']));
					}
				}
				$exploss = (int)$session['user']['experience']*get_module_setting("experienceloss")/100;
				output("`n`n`@You are dead... struck down by %s `@.`n",$badguy['creaturename']);
				if ($exploss>0) output(" You lose `^%s percent`@	of your experience and all of your gold.",get_module_setting("experienceloss"));
				$session['user']['experience']-=$exploss;
				$session['user']['gold']=0;
				debuglog("lost $exploss experience and all gold to Ero-Sennin.");
				addnews("%s`^ was killed by %s`^ sent out %s`^.",$session['user']['name'],$badguy['creaturename'],$erosennin);
				addnav("Return");
				addnav("Return to the Shades","shades.php");
				$session['user']['specialinc'] = "";
				$badguy=array();
				$session['user']['badguy']="";
			}else{
				require_once("lib/fightnav.php");
				$allow = true;
				fightnav($allow,false);
				if ($session['user']['superuser'] & SU_DEVELOPER) addnav("Escape to Village","village.php");
			}
	}
}

function erosennin_run(){
}

?>
