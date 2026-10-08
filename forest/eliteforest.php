<?php
require_once("common.php");
require_once("modules/eliteforest/functions.php");
require_once("lib/fightnav.php");
require_once("lib/http.php");
require_once("lib/taunt.php");
require_once("lib/events.php");
require_once("lib/battle-skills.php");

function eliteforest_getmoduleinfo(){

 $info = array(
	"name"=>"Elite Forest",
	"author"=>"`JShinobiIceSlayer",
	"version"=>"1.0",
	"category"=>"Forest",
	"download"=>"",
	"settings"=>array(
		"Elite Forest - Settings, title",
			"specialchance"=>"Chance for special event in the forest Hook,int|10",
			"maxseen"=>"How many elite fights per day?,int|3",
			"categories"=>"What categories can the creatures be from,text|elite,other",
			"type the categories in single quote marks separated by commas,note",
		),
	"prefs"=>array(
		"seen"=>"Seen what foes today,text",

		),
	);
 return $info;
}
function eliteforest_install(){
	module_addhook("eliteforest");
	module_addhook("newday");
 return true;
}
function eliteforest_uninstall(){

 return true;
}
function eliteforest_dohook($hookname,$args){
 
 switch($hookname){
	case "eliteforest":
		addnav("Nearby Forest");
		addnav("Elite Forest Arena","runmodule.php?module=eliteforest&op=");
		break;
	case "newday":
		set_module_pref("seen",0);
		break;
}
 return $args;
}

function eliteforest_run(){
	
	global $session,$companions;
	
	tlschema("forest");

	$maxseen=(int)get_module_setting("maxseen");
	$enemytype = (int) httpget('enemytype');

	$fight = false;
	page_header("The Forest");
	$dontdisplayforestmessage=handle_event("eliteforest");

	$op = httpget("op");

	$battle = false;

	if ($session['user']['level']<15 && $session['user']['dragonkills']<40) {

		output("`2You try very hard to enter the area, but thick thorns keep you from getting inside... maybe you should grow a bit more in level....");
		addnav("Navigation");
		villagenav();
		page_footer();
	}




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
		if ($seen>=$maxseen) {
			output("`2You think you had enough for today....");
			addnav("Navigation");
			villagenav();
			page_footer();
		}
		if ($session['user']['turns']<=0){
			output("`\$`bYou are too tired to search the forest any longer today.	Perhaps tomorrow you will have more energy.`b`0");
			$op="";
			httpset('op', "");
		}else{
			modulehook("eliteforestsearch", array());
			$args = array(
				'soberval'=>0.9,
				'sobermsg'=>"`&Faced with the prospect of death, you sober up a little.`n",
				'schema'=>'forest');
			modulehook("soberup", $args);
			if (module_events("eliteforest", get_module_setting("specialchance")) != 0) {
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
				
				$multi = 1;
				$targetlevel = ($session['user']['level'] + $plev - $nlev );
				$mintargetlevel = $targetlevel;
				if (getsetting("multifightdk", 10) <= $session['user']['dragonkills']) {
					if (e_rand(1,100) <= getsetting("multichance", 25)) {
						$multi = e_rand(getsetting('multibasemin',2),getsetting('multibasemax',3));
						if ($type == "thrill") {
							$multi += e_rand(getsetting("multithrillmin", 1),getsetting("multithrillmax", 2));
							if (e_rand(0,1)) {
								$targetlevel++;
								$mintargetlevel = $targetlevel - 1;
							} else {
								$mintargetlevel = $targetlevel-1;
							}						
						}
						$multi = min($multi, $session['user']['level']);
					}
				} else {
					$multi = 1;
				}
				if ($targetlevel<1) $targetlevel=1;
				if ($mintargetlevel<1) $mintargetlevel=1;
				if ($mintargetlevel > $targetlevel) $mintargetlevel = $targetlevel;
				debug("Creatures: $multi Targetlevel: $targetlevel Mintargetlevel: $mintargetlevel");
				if (get_module_setting("categories")!='') $ccategory = "AND creaturecategory IN ('".implode("','",explode(",",addslashes(get_module_setting("categories"))))."')";
				if ($multi > 1) {
					if (getsetting('allowpackmonsters',0)) $packofmonsters = (e_rand(0,5) == 0); // true or false
					switch($packofmonsters) {
						case false:							
							$sql = "SELECT * FROM " . db_prefix("creatures") . " WHERE creaturelevel <= $targetlevel AND creaturelevel >= $mintargetlevel AND forest=1 $ccategory ORDER BY rand(".e_rand().") LIMIT $multi";
							break;
						case true:
							$sql = "SELECT * FROM " . db_prefix("creatures") . " WHERE creaturelevel <= $targetlevel AND creaturelevel >= $mintargetlevel AND forest=1 $ccategory ORDER BY rand(".e_rand().") LIMIT 1";
							break;
					}
				} else {
					$sql = "SELECT * FROM " . db_prefix("creatures") . " WHERE creaturelevel <= $targetlevel AND creaturelevel >= $mintargetlevel AND forest=1 $ccategory ORDER BY rand(".e_rand().") LIMIT 1";
				}
				$result = db_query($sql);
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
							if (!isset($badguy['diddamage'])) $badguy['diddamage']=0;
							$stack[] = $badguy;
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
				$attackstack = modulehook("eliteforestfight-start",$attackstack);
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

			elitelog(date("Y-m-d H:i:s"),$session['user'],1,$badguy);
		}elseif($defeat){
			elitelog(date("Y-m-d H:i:s"),$session['user'],0,$badguy);
			require_once("lib/forestoutcomes.php");
			forestdefeat($newenemies);
			increment_module_pref('seen',1);
		}else{
			fightnav(true,true,'runmodule.php?module=eliteforest&');
		}
	}	

	if ($op==""){
		// Need to pass the variable here so that we show the forest message
		// sometimes, but not others.
		eliteforest($dontdisplayforestmessage);
	}
	page_footer();
	
}

function elitelog($date,$guy,$outcome,$enemy) {
	require_once("lib/playerfunctions.php");
	$sql="INSERT INTO eliteforestlog (date,dks,hpmax,hpleft,atk,def,outcome,enemyhpleft,enemyname) VALUES ";
	$sql.="('$date','".
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
debug($sql);
	db_query($sql);
}
?>
