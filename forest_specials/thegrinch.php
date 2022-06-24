<?php
require_once("common.php");
require_once("lib/fightnav.php");
require_once("lib/http.php");
require_once("lib/taunt.php");
require_once("lib/events.php");
require_once("lib/battle-skills.php");

function thegrinch_getmoduleinfo(){
	$info = array(
			"name"=>"The Grinch Miniboss",
			"author"=>"`JShinobiIceSlayer, modified by Neji",
			"version"=>"1.0",
			"category"=>"Holidays|Christmas",
			"download"=>"",
			"settings"=>array(
				"Grinch - modified Elite Forest - Settings,title",
				"specialchance"=>"Chance for special event in the forest Hook,int|10",
				"categories"=>"What categorys can the creatures be from,text|grinch",
				"type the categories in single quote marks seperated by commas,note",
				"grinch"=>"Name of the Grinch,text|`2T`@he `2G`@rinch",
				"maxseen"=>"How often per day?,int|3",
				),
			"prefs"=>array(
				"seen"=>"Seen what foes today,text",
				"lastwon"=>"Last sql (no table yet),readonly",
				"enemy-0"=>"Times defeat enemy 0,readyonly",
				"enemy-1"=>"Times defeat enemy 1,readyonly",
				"enemy-2"=>"Times defeat enemy 2,readyonly",
				"enemy-3"=>"Times defeat enemy 3,readyonly",
				"enemy-4"=>"Times defeat enemy 4,readyonly",
				"enemy-5"=>"Times defeat enemy 5,readyonly",
				"enemy-6"=>"Times defeat enemy 6,readyonly",
				"enemy-7"=>"Times defeat enemy 7,readyonly",
				"enemy-8"=>"Times defeat enemy 8,readyonly",
				"enemy-9"=>"Times defeat enemy 9,readyonly",
				"enemy-10"=>"Times defeat enemy 10,readyonly",
				"enemy-11"=>"Times defeat enemy 11,readyonly",
				),
			);
	return $info;
}
function thegrinch_install(){
	module_addhook_priority("forest",0);
	module_addhook("newday");
	module_addhook("village");
	return true;
}
function thegrinch_uninstall(){

	return true;
}
function thegrinch_dohook($hookname,$args){
	$grinch = get_module_setting('grinch');
	switch($hookname){
		case "forest":
			$op = httpget('op');
			//if ($op=="") {
			addnav("`2W`@inter `2C`@hallenge");
			addnav(array("%s",$grinch),"runmodule.php?module=thegrinch&op=");
			//			}
			break;
		case "newday":
			set_module_pref("seen",0);
			break;
		case "village":
			output_notl("`c");
			output("`@Top %s`4-Slayers:`0`n`n",get_module_setting('grinch'));
			for ($i=0;$i<12;$i++) {
				$temp = grinch_gethof($i);
				output("`)%s`4: `)%s`2 (%s victories) (You: %s)`n",$temp['grinchname'],$temp['name'],$temp['kills'],(int)get_module_pref('enemy-'.$i));
			}
			output_notl("`c");
	}
	return $args;
}

function grinch_gethof($enemytype) {
	$targetlevel=19;
	$mintargetlevel=19;
	if (get_module_setting("categories")!='') {
		//$ccategory = "AND creaturecategory IN ('".implode("','",explode(",",addslashes(get_module_setting("categories"))))."')";
		$ccategory = "AND creaturecategory='".get_module_setting("categories").("-".$enemytype)."'";
	}
	$sql = "SELECT * FROM " . db_prefix("creatures") . " WHERE creaturelevel <= $targetlevel AND creaturelevel >= $mintargetlevel $ccategory ORDER BY rand(".e_rand().") LIMIT 1";
	$result = db_query($sql);
	$row = db_fetch_assoc($result);
	$return = array('grinchname'=>$row['creaturename']);
	//get player
	$sql = "SELECT * FROM ". db_prefix('module_userprefs'). " WHERE modulename='thegrinch' AND setting='enemy-".$enemytype."' order by value+0 desc";
	$result = db_query($sql);
	if (db_num_rows($result) == 0 ) {
		$return['name']="Nobody yet";
		$return['kills']=0;
	} else {
		$row = db_fetch_assoc($result);
		$sql = "SELECT name FROM ". db_prefix('accounts'). " WHERE acctid=".$row['userid']." LIMIT 1";
		$result = db_query($sql);
		$newrow = db_fetch_assoc($result);
		$return['name']=$newrow['name'];
		$return['kills']=$row['value'];

	}
	return $return;
}

function thegrinch_nav($noshowmessage=false) {
	global $session,$playermount;
	tlschema("thegrinch");
	//	mass_module_prepare(array("forest", "validforestloc"));
	addnav("Navigation");
	villagenav();
	addnav("Fight");
	//	addnav("L?Look for Something to Kill","runmodule.php?module=thegrinch&op=search");
	//	addnav("T?Go Thrillseeking","runmodule.php?module=thegrinch&op=search&type=thrill");
	addnav("Tiny Challenge","runmodule.php?module=thegrinch&op=search&enemytype=0");
	addnav("A Small Challenge","runmodule.php?module=thegrinch&op=search&enemytype=1");
	addnav("`qA Challenge","runmodule.php?module=thegrinch&op=search&enemytype=2");
	addnav("`\$A Big Challenge","runmodule.php?module=thegrinch&op=search&enemytype=3");
	addnav("`4A Huge Challenge","runmodule.php?module=thegrinch&op=search&enemytype=4");
	addnav("`\$S`4eriously?!?","runmodule.php?module=thegrinch&op=search&enemytype=5");
	addnav("`\$S`4T`\$A`4H`\$P`4!!","runmodule.php?module=thegrinch&op=search&enemytype=6");
	addnav("More Fights");
	addnav("`2M`@onster `2M`@ode","runmodule.php?module=thegrinch&op=search&enemytype=7");
	addnav("`\$M`4ega `2M`@ode","runmodule.php?module=thegrinch&op=search&enemytype=8");
	addnav("`qU`tltimate `2M`@ode","runmodule.php?module=thegrinch&op=search&enemytype=9");
	addnav("Hardcore");
	addnav("`xY`lou`v wanted it","runmodule.php?module=thegrinch&op=search&enemytype=10");
	addnav("`6P`tlease `6d`ton't `6c`tlick","runmodule.php?module=thegrinch&op=search&enemytype=11");


	addnav("Other");

	if ($noshowmessage!=true){
		output("`c`7`bThe Forest`b`0`c");
		output("`2The Elite Forest Arena, home to evil creatures and evildoers of all sorts.`n`n");
		output("You enter a vast forest where you have no idea what evil might present itself to you... but you know one thing: it will be one of the most horrible things you've ever faced.");
		output("You move as silently as a soft breeze across the thick moss covering the ground, wary to avoid stepping on a twig or any of the numerous pieces of bleached bone that populate the forest floor, lest you betray your presence to one of the vile beasts that wander the forest.`n");
		modulehook("thegrinch-desc");
	}
	modulehook("elite", array());
	module_display_events("thegrinch","runmodule.php?module=thegrinch&op=");
	tlschema();
}

function thegrinch_run(){

	global $session,$companions;

	tlschema("forest");
	$grinch = get_module_setting('grinch');

	$maxseen=get_module_setting('maxseen');
	$enemytype = (int) httpget('enemytype');

	$fight = false;
	page_header("The Forest");
	$dontdisplayforestmessage=handle_event("thegrinch");

	$op = httpget("op");
	addnav("Forest");
	addnav("Back to the Forest","forest.php");
	addnav("Actions");

	$battle = false;

	/* 	if ($session['user']['level']<15 && $session['user']['dragonkills']<40) {

		output("`2You try very hard to enter the area, but thick thorns keep you from getting inside... maybe you should grow a bit more in level....");
		addnav("Navigation");
		villagenav();
		page_footer();
		} */




	if ($op=="run"){
		if (e_rand()%3 == 0){
			output ("`c`b`&You have successfully fled your opponent!`0`b`c`n");
			$op="";
			httpset('op', "");
			unsuspend_buffs();
			foreach($companions as $index => $companion) {
				if(isset($companion['expireafterfight']) && $companion['expireafterfight']) {
					unset($companions[$index]);
				}
			}
		}else{
			output("`c`b`\$You failed to flee your opponent!`0`b`c");
		}
	}

	if ($op=="search"){
		checkday();
		$seen=get_module_pref('seen');
		if ($seen>$maxseen) {
			output("`2You think you had enough christmas hate fighting for today....");
			addnav("Navigation");
			villagenav();
			page_footer();
		}
		if ($session['user']['turns']<=0){
			output("`\$`bYou are too tired to search the forest any longer today.	Perhaps tomorrow you will have more energy.`b`0");
			$op="";
			httpset('op', "");
		}else{
			modulehook("thegrinchsearch", array());
			$args = array(
					'soberval'=>0.9,
					'sobermsg'=>"`&Faced with the prospect of death, you sober up a little.`n",
					'schema'=>'forest');
			modulehook("soberup", $args);
			if (module_events("thegrinch", get_module_setting("specialchance")) != 0) {
				if (!checknavs()) {
					// If we're showing the forest, make sure to reset the special
					// and the specialmisc
					$session['user']['specialinc'] = "";
					$session['user']['specialmisc'] = "";
					$dontdisplayforestmessage=true;
					$op = "";
					httpset("op", "");
				} else {
					page_footer();
				}
			}else{
				$session['user']['turns']--;
				$battle=true;
				if (e_rand(0,2)==1){
					$plev = (e_rand(1,5)==1?1:0);
					$nlev = (e_rand(1,3)==1?1:0);
				}else{
					$plev=0;
					$nlev=0;
				}
				$type = httpget('type');
				if ($type=="thrill"){
					$plev++;
					output("`\$You head for the section of forest which contains creatures of your nightmares, hoping to find one of them injured.`0`n");
				}

				$multi = (e_rand(0,10)==1?1:0);
				$targetlevel = ($session['user']['level'] + $plev - $nlev );
				$mintargetlevel = $targetlevel;

				if ($targetlevel<1) $targetlevel=1;
				if ($mintargetlevel<1) $mintargetlevel=1;
				if ($mintargetlevel > $targetlevel) $mintargetlevel = $targetlevel;
				//grinch target is 19
				$mintargetlevel=19;
				$targetlevel=19;
				debug("Creatures: $multi Targetlevel: $targetlevel Mintargetlevel: $mintargetlevel");
				if (get_module_setting("categories")!='') {
					//$ccategory = "AND creaturecategory IN ('".implode("','",explode(",",addslashes(get_module_setting("categories"))))."')";
					$ccategory = "AND creaturecategory='".get_module_setting("categories").("-".$enemytype)."'";
				}
				if ($multi > 1) {
					if (getsetting('allowpackmonsters',0)) $packofmonsters = (e_rand(0,5) == 0); // true or false
					switch($packofmonsters) {
						case false:							
							$sql = "SELECT * FROM " . db_prefix("creatures") . " WHERE creaturelevel <= $targetlevel AND creaturelevel >= $mintargetlevel $ccategory ORDER BY rand(".e_rand().") LIMIT $multi";
							break;
						case true:
							$sql = "SELECT * FROM " . db_prefix("creatures") . " WHERE creaturelevel <= $targetlevel AND creaturelevel >= $mintargetlevel $ccategory ORDER BY rand(".e_rand().") LIMIT 1";
							break;
					}
				} else {
					$sql = "SELECT * FROM " . db_prefix("creatures") . " WHERE creaturelevel <= $targetlevel AND creaturelevel >= $mintargetlevel $ccategory ORDER BY rand(".e_rand().") LIMIT 1";
				}
				$result = db_query($sql);
				debug($sql);
				restore_buff_fields();
				if (db_num_rows($result) == 0) {
					// There is nothing in the database to challenge you, let's
					// give you a doppleganger.
					$badguy = array();
					$badguy['creaturename']= "An evil doppleganger of ".$session['user']['name'];
					$badguy['creatureweapon']=$session['user']['weapon'];
					$badguy['creaturelevel']=$session['user']['level'];
					$badguy['creaturegold']=0;
					$badguy['creatureexp'] = round($session['user']['experience']/10, 0);
					$badguy['creaturehealth']=$session['user']['maxhitpoints'];
					$badguy['creatureattack']=$session['user']['attack'];
					$badguy['creaturedefense']=$session['user']['defense'];
					// test
					switch ($enemytype) {
						case 1:
							$badguy['creaturename']= "`\$Empowered `QK`yyuubi";
							$badguy['creatureweapon']="Mauling Fangs and Enormous Claws";
							$badguy['creaturelevel']=$session['user']['level']+5;
							$badguy['creaturegold']=8000;
							$badguy['creatureexp'] = 900;
							$badguy['creaturehealth']= 20000;
							$badguy['creatureattack']= 360;
							$badguy['creaturedefense']=330;
							break;

						default:
							$badguy['creaturename']= "`QK`yyuubi";
							$badguy['creatureweapon']="Piercing Fangs and Enormous Claws";
							$badguy['creaturelevel']=$session['user']['level'];
							$badguy['creaturegold']=3000;
							$badguy['creatureexp'] = 500;
							$badguy['creaturehealth']= 10000;
							$badguy['creatureattack']= 300;
							$badguy['creaturedefense']=280;

					}


					// end test
					$stack[] = $badguy;



				} else {
					require_once("lib/forestoutcomes.php");
					if ($packofmonsters == true) {
						$initialbadguy = db_fetch_assoc($result);
						$prefixs = array("Elite","Dangerous","Lethal","Savage","Deadly","Malevolent","Malignant");
						for($i=0;$i<$multi;$i++) {
							$initialbadguy['creaturelevel'] = e_rand($mintargetlevel, $targetlevel);
							$initialbadguy['creaturegrinch'] = $enemytype;
							$badguy = buffbadguy($initialbadguy);
							if ($type == "thrill") {
								// 10% more experience
								$badguy['creatureexp'] = round($badguy['creatureexp']*1.1, 0);
								// 10% more gold
								$badguy['creaturegold'] = round($badguy['creaturegold']*1.1, 0);
							}
							if ($type == "suicide") {
								// Okay, suicide fights give even more rewards, but
								// are much harder
								// 25% more experience
								$badguy['creatureexp'] = round($badguy['creatureexp']*1.25, 0);
								// 25% more gold
								$badguy['creaturegold'] = round($badguy['creaturegold']*1.25, 0);
								// Now, make it tougher.
								$mul = 1.25 + $extrabuff;
								$badguy['creatureattack'] = round($badguy['creatureattack']*$mul, 0);
								$badguy['creaturedefense'] = round($badguy['creaturedefense']*$mul, 0);
								$badguy['creaturehealth'] = round($badguy['creaturehealth']*$mul, 0);
								// And mark it as an 'elite' troop.
								$prefixs = translate_inline($prefixs);
								$key = array_rand($prefixs);
								$prefix = $prefixs[$key];
								$badguy['creaturename'] = $prefix . " " . $badguy['creaturename'];
							}
							$badguy['playerstarthp']=$session['user']['hitpoints'];
							if (!isset($badguy['diddamage'])) $badguy['diddamage']=0;
							$stack[$i] = $badguy;
						}
						if ($multi > 1) {
							output("`2You encounter a group of `^%i`2 %s`2.`n`n", $multi, $badguy['creaturename']);
						}
					} else {
						while ($badguy = db_fetch_assoc($result)) {
							//decode and test the AI script file in place if any
							$aiscriptfile="scripts/".$badguy['creatureaiscript'].".php";
							if (file_exists($aiscriptfile)) {
								//file there, get content and put it into the ai script field.
								$badguy['creatureaiscript']="require('".$aiscriptfile."');";
							}
							//AI setup
							// no buffing
							//$badguy = buffbadguy($badguy);
							// Okay, they are thrillseeking, let's give them a bit extra
							// exp and gold.
							if ($type == "thrill") {
								// 10% more experience
								$badguy['creatureexp'] = round($badguy['creatureexp']*1.1, 0);
								// 10% more gold
								$badguy['creaturegold'] = round($badguy['creaturegold']*1.1, 0);
							}							
							$badguy['playerstarthp']=$session['user']['hitpoints'];
							$badguy['creaturegrinch'] = $enemytype;
							$badguy['essentialleader'] = true;
							if (!isset($badguy['diddamage'])) $badguy['diddamage']=0;
							$stack[] = $badguy;
							$badguy['essentialleader'] = false;
							// add minions
							if ($enemytype>1) {
								switch ($enemytype) {
									case 10: case 11:

										$badguy['creaturename']= "`4H`)armless `QK`yyuubi `2E`@lf";
										$badguy['creatureweapon']="Tiny tail";
										$badguy['creaturelevel']=$session['user']['level']+5;
										$badguy['creaturegold']= 20000;
										$badguy['creatureexp'] = 1300;
										$badguy['creaturehealth']= 4000;
										$badguy['creatureattack']= 760;
										$badguy['creaturedefense']=730;
										$badguy['creatureaiscript']="poison1_claws";
										$badguy['creaturelose']="";
										$badguy['creaturewin']="";
										break;
									case 8: case 9:

										$badguy['creaturename']= "`4V`)ery `\$Mean `QK`yyuubi `2E`@lf";
										$badguy['creatureweapon']="A little red flute with claws";
										$badguy['creaturelevel']=$session['user']['level']+5;
										$badguy['creaturegold']=8000;
										$badguy['creatureexp'] = 900;
										$badguy['creaturehealth']= 2000;
										$badguy['creatureattack']= 660;
										$badguy['creaturedefense']=530;
										$badguy['creatureaiscript']="poison1_claws";
										$badguy['creaturelose']="";
										$badguy['creaturewin']="";
										break;
									case 4: case 5: case 6: case 7:

										$badguy['creaturename']= "`\$Mean `QK`yyuubi `2E`@lf";
										$badguy['creatureweapon']="Tiny fangs and little claws";
										$badguy['creaturelevel']=$session['user']['level']+5;
										$badguy['creaturegold']=8000;
										$badguy['creatureexp'] = 900;
										$badguy['creaturehealth']= 2000;
										$badguy['creatureattack']= 360;
										$badguy['creaturedefense']=330;
										$badguy['creatureaiscript']="poison1_claws";
										$badguy['creaturelose']="";
										$badguy['creaturewin']="";
										break;
									case 2: case 3:
										$badguy['creaturename']= "`QK`yyuubi `2E`@lf";
										$badguy['creatureweapon']="Sweet fangs and micro-claws";
										$badguy['creaturelevel']=$session['user']['level'];
										$badguy['creaturegold']=3000;
										$badguy['creatureexp'] = 500;
										$badguy['creaturehealth']= 400;
										$badguy['creatureattack']= 300;
										$badguy['creaturedefense']=280;
										$badguy['creatureaiscript']="poison1_claws";
										$badguy['creaturelose']="";
										$badguy['creaturewin']="";
									default:

								}
								$aiscriptfile="scripts/".$badguy['creatureaiscript'].".php";
								if (file_exists($aiscriptfile)) {
									//file there, get content and put it into the ai script field.
									$badguy['creatureaiscript']="require('".$aiscriptfile."');";
								}
								//AI setup
								$stack[] = $badguy;
								$stack[] = $badguy;
								if ($session['user']['dragonkills']>1500) {
									$badguy['creaturename']= "`tB`yackup `2E`@lf `2for Demigods";
									$badguy['creaturehealth']= 8000;
									$badguy['creatureattack']= 1260;
									$badguy['creaturedefense']= 1730;
									$stack[] = $badguy;
									$stack[] = $badguy;
									$stack[] = $badguy;
								}
							}

						}
					}
				}
				calculate_buff_fields();
				$attackstack = array(
						"enemies"=>$stack,
						"options"=>array(
							"type"=>"forest"
							)
						);
				$attackstack = modulehook("forestfight-start",$attackstack);
				$attackstack = modulehook("thegrinchfight-start",$attackstack);
				$session['user']['badguy']=createstring($attackstack);
				// If someone for any reason wanted to add a nav where the user cannot choose the number of rounds anymore
				// because they are already set in the nav itself, we need this here.
				// It will not break anything else. I hope.
				if(httpget('auto') != "") {
					httpset('op', 'fight');
					$op = 'fight';
				}
			}
		}
	}

	if ($op=="fight" || $op=="run" || $op == "newtarget"){
		$battle=true;
	}

	if ($battle){

		require_once("battle.php");

		if ($victory){
			require_once("lib/forestoutcomes.php");
			$op="";
			httpset('op', "");
			forestvictory($newenemies,isset($options['denyflawless'])?$options['denyflawless']:false);
			$dontdisplayforestmessage=true;
			increment_module_pref('seen',1);

			thegrinch_log(date("Y-m-d H:i:s"),$session['user'],1,$badguy);
			switch ($newenemies[0]['creaturegrinch']) {
				case 7: case 8: case 9: case 10: case 11:
					$category="Potion";
					break;
				case 5: case 6: 
					$category="Secret Scroll";
					break;
				case 3: case 4:
					$category="Advanced Equipment";
				default:
					$category="Loot";

			}
			thegrinch_getrandomitem($category,$newenemies[0]['creaturename']);
			if ($newenemies[0]['creaturegrinch']>9) {
				//one more
				thegrinch_getrandomitem($category,$newenemies[0]['creaturename']);
			}

		}elseif($defeat){
			thegrinch_log(date("Y-m-d H:i:s"),$session['user'],0,$badguy);
			require_once("lib/forestoutcomes.php");
			//copy forestdefeat
			$names = array();
			$killer = false;
			foreach ($newenemies as $badguy) {
				$names[] = $badguy['creaturename'];
				if (isset($badguy['killedplayer']) && $badguy['killedplayer'] == true) $killer = $badguy['creaturename'];
				if (isset($badguy['creaturewin']) && $badguy['creaturewin'] > "") {
					$msg = translate_inline($badguy['creaturewin'],"battle");
					output_notl("`b`&%s`0`b`n",$msg);
				}
			}
			if (count($names) > 1) $lastname = array_pop($names);
			$enemystring = join(", ", $names);
			$and = translate_inline("and");
			if (isset($lastname) && $lastname > "") $enemystring = "$enemystring $and $lastname";
			$taunt = select_taunt_array();
			//leave it for now, it's tricky 
			/*if (is_array($where)) {
			  $where=sprintf_translate($where);
			  } else {
			  $where=translate_inline($where);
			  }*/
			$deathmessage=select_deathmessage_array(true,array("{where}"),array($where));
			if ($deathmessage['taunt']==1) {
				addnews("%s`n%s",$deathmessage['deathmessage'],$taunt);
			} else {
				addnews("%s",$deathmessage['deathmessage']);
			}
			//copy end forestdefeat
			increment_module_pref('seen',1);
			output("%s`2 leaves your battered body on the forest floor, having other things to accomplish now...`n",get_module_setting('grinch'));
			$session['user']['hitpoints']=1;
			$session['user']['alive']=true;
		}else{
			fightnav(true,true,'runmodule.php?module=thegrinch&');
		}
	}	

	if ($op==""){
		// Need to pass the variable here so that we show the forest message
		// sometimes, but not others.
		thegrinch_nav($dontdisplayforestmessage);
	}
	page_footer();

}

function thegrinch_getrandomitem($category,$enemyname) {
	global $session;
	//get a random item
	require_once("modules/inventory/lib/itemhandler.php");
	$sql = "SELECT * FROM ".db_prefix("item")." WHERE class = '$category' ORDER BY rand(".e_rand().") LIMIT 1";
	$result=db_query($sql);
	if (db_num_rows($result)<1) {
		$loot=false;
	} else {
		$row = db_fetch_assoc($result);
		$loot=$row;
	}
	// get_random_item() Will return false, when no matching item is found
	// and return a proper item-array, when one is found.
	if ($loot != false) {
		// Important: When handing over the itemid, make sure to hand it as real INT,
		// otherwise the function will search for an item named "1"
		if (add_item((int)$loot['itemid']))
			output("`^Searching the lifeless body of `4%s`^ you find a `7%s`^.`n", $enemyname, $loot['name']);
	}
}

function thegrinch_log($date,$guy,$outcome,$enemy) {
	require_once("lib/playerfunctions.php");
	//$sql="INSERT INTO thegrinchlog (date,dks,hpmax,hpleft,atk,def,outcome,enemyhpleft,enemyname) VALUES ";
	$sql="('$date','".
		$guy['dragonkills']
		."','".
		$guy['maxhitpoints']	
		."','".
		$guy['hitpoints']
		."','".
		get_player_attack()
		."','".
		get_player_defense()
		."','".
		$outcome
		."',".
		(int)$enemy['creaturehealth']
		.",'".
		$enemy['creaturename']
		."')";
	;
	//debug($sql);
	set_module_pref('lastwon',$sql);
	//	db_query($sql);
	//debug($enemy);
	if ($outcome==1) {
		increment_module_pref("enemy-".$enemy['creaturegrinch'],1);	
	}
}
?>
