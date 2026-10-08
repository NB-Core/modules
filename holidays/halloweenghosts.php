<?php

require_once("lib/villagenav.php");
if (is_module_active("alignment")){
	require_once("./modules/alignment/func.php");
}

function halloweenghosts_getmoduleinfo(){
	$info = array(
			"name"=>"Halloween Ghosts by Yvo (timelocked)",
			"version"=>"1.0",
			"author"=>"Jean Yvo (Pics) & Gyururu (Text) & Oliver Brendel (Dumb face)",
			"category"=>"Holidays|Halloween",
			"download"=>"",
			"settings"=>array(	
				"Halloween Ghosts - Main Settings,title",
				"start"=>"Activation start date (mm-dd)|10-31",
				"end"=>"Activation end date (mm-dd)|11-02",				
				"ghostchance"=>"Chance to see old ghost?,range,0,100,1|20",
				"ghostlocation"=>"Where does the ghost appear?,location|".getsetting("villagename", LOCATION_FIELDS),
				"locall"=>"OR should he appear everywhere?,bool|0",
				"experienceloss"=>"Percentage: How many experience is lost when player is killed,floatrange,1,100,1|10",
				"femalename"=>"Name of the girl,text|Chikara",
				"malename"=>"Name of the dude,text|Oukuma",
				"Halloween Ghosts - Alignment Settings,title",
				"The following settings are only used if the \"Basic Alignment\" module has been activated.,note",
				/* "gaveghostgold"=>"Alignment points gained for giving gold to the ghost,range,0,5,1|1",
				   "gaveghostgem"=>"Alignment points gained for giving a gem to the ghost,range,0,5,1|2",
				   "gaveghostallgold"=>"Alignment points gained for giving away all gold to the ghost,range,0,5,1|3",
				   "gaveghostnothing"=>"Alignment points lost for not giving anything to the ghost,range,0,5,1|1", */

				),
			"prefs"=>array(
					"Ghosts Preferences,title",	
					"seenghost"=>"Seen a Ghost today?,bool|0",
					"hasattacked"=>"Has a ghost been attacked?,bool|0",
					"haseverattacked"=>"Has a ghost ever been attacked?,bool|0",
					"metwho"=>"Who has the player encountered and not attacked?,int|0",
				      ),
			"requires"=>array(
				"datemanager"=>"1.0|By Oliver Brendel",
			),
	);
	return $info;
}
function halloweenghosts_install(){
	module_addeventhook("forest", "require_once(\"modules/halloweenghosts.php\"); return halloweenghosts_test();");
	module_addhook("newday");
	module_addhook("changesetting");
	return true;
}
function halloweenghosts_uninstall(){
	return true;
}
function halloweenghosts_test(){
	global $session;
	
	$start = get_module_setting("start","halloweenghosts");
	$end = get_module_setting("end","halloweenghosts");
	require_once("modules/datemanager.php");
	$check=datemanager_datecheck($start,$end,0);

	//check for activation date
	if ($check!=1) return 0;

	$chance = get_module_setting("ghostchance","halloweenghosts");
	if (get_module_setting("locall","halloweenghosts") == 0){
		if (get_module_pref("seenghost","halloweenghosts") || $session['user']['location']!=get_module_setting("ghostlocation","halloweenghosts")) return 0; 
	}else if (get_module_setting("locall","halloweenghosts")){
		if (get_module_pref("seenghost","halloweenghosts")) return 0;
	}
	return $chance; 
}
function halloweenghosts_dohook($hookname,$args){
	switch($hookname){
		case "newday":
			set_module_pref("seenghost",0);
			set_module_pref("hasattacked",0);
			// reset only if completed, else don't 
			if (get_module_pref("metwho") == (HALLOWEEN_BROTHER|HALLOWEEN_SISTER))
				set_module_pref("metwho",0);
			break;	
		case "changesetting":
			if ($args['setting'] == "villagename") {
				if ($args['old'] == get_module_setting("ghostlocation")) {
					set_module_setting("ghostlocation", $args['new']);
				}
			}
			break;
	}
	return $args;
}

function halloweenghosts_image($imagename) {
	$pics='';
	if (is_module_active('addimages')) {
		if ($imagename!='') {
			$name=$imagename;
			$pic=file_exists($name);
		}
		$pic=true;
		if ($pic==true)	$pics.="<img style=\"max-width:50%;\" src=\"$name\" ALT='$name'>";
	}
	output_notl("`c".$pics."`c`n`n",true);
	return;
}

define("HALLOWEEN_SISTER",1);
define("HALLOWEEN_BROTHER",2);
define("HALLOWEEN_HOUND",4);
define("HALLOWEEN_SHADE",8);

function halloweenghosts_runevent($type)
{
	global $session;
	$sex = translate_inline($session['user']['sex']?"m'lady'":"good sir");
	$sex2 = translate_inline($session['user']['sex']?"he":"she");
	$sex3 = translate_inline($session['user']['sex']?"her":"his");
	require_once("lib/partner.php");
	$lover = get_partner();
	$seenghost = get_module_pref("seenghost");
	$imagefolder = "modules/halloweenghosts/images/";
	$from = "forest.php?";
	$session['user']['specialinc'] = "module:halloweenghosts";
	$op = httpget('op');	
// $suspendbuffs = false; // Just in case we want to allow train-only-buffs
	addnav("Navigation");
	switch ($op) {
		case "attack":
			set_module_pref("hasattacked",1);
			set_module_pref("haseverattacked",1);
			$battle=true;
			$who = httpget('who');
			switch($who) {
				case HALLOWEEN_SISTER:
					$creaturename = get_module_setting("femalename");
					break;
				case HALLOWEEN_BROTHER:
					$creaturename = get_module_setting("malename");
					break;
				default:
					$creaturename = translate_inline("Ghost of Neji");
					break;
			}
			$creaturename =
				$badguy = array(
						"creaturename"=>$creaturename,
						"creaturelevel"=>$session['user']['level']+5,
						"creatureweapon"=>translate_inline("Ghostly Kunai"),
						"creatureattack"=>$session['user']['attack']*2.1,
						"creaturedefense"=>$session['user']['defense']*1.95,
						"creaturehealth"=>round($session['user']['maxhitpoints']*(1.5+e_rand(1,10)/100)),
						"diddamage"=>0,
					       );
			$session['user']['badguy'] = createstring($badguy);
			require_once("lib/battle-skills.php");
			if (isset($suspendbuffs) && $suspendbuffs) suspend_buffs('allowintrain',"Time ceases to exist... You suddenly feel vulnerable... the aura of the ghost disables all your extraordinary talents and no one is able to help you now!");
			$op = "combat";
		case "combat": case "fight":
			include("battle.php");
			switch($badguy['creaturename']){
				case get_module_setting("femalename"):
					halloweenghosts_image($imagefolder."ghost-younger.jpg");
					break;
				case get_module_setting("malename"):
					halloweenghosts_image($imagefolder."ghost-older.jpg");
					break;
				default:
					halloweenghosts_image($imagefolder."hound2.jpg");
					break;

			}
			if ($victory){ //no exp at all for such a foul act
				output("`7As you deliver the finishing blow, the `jghostly apparition `7struggles as if it is reliving its final moments before dissipating into the ether, leaving only the echo of a chilling cry in the quiet forest. Your heart pounds in your chest as the reality of your encounter with a ghost sinks in. `@You have defeated a ghost.`n");
				output("`n");
				if (isset($suspendbuffs) && $suspendbuffs) unsuspend_buffs('allowintrain',"You feel that time and the energies are now flowing normally again.");
				$session['user']['specialinc'] = "";
				$session['user']['specialmisc'] = "";
				$badguy=array();
				$session['user']['badguy']="";
			}elseif ($defeat){ //but a loss of course if you die
				$exploss = $session['user']['experience']*get_module_setting("experienceloss")/100;
				output("`4An unnatural chill runs through your body as the world around you slowly fades to black. That last thing you felt was your soul being ripped away from your body by the `jghostly apparition`4. No `5Ninjutsu `4could have saved you from that. You have met with a terrible fate.`n");
				output("`n");
				addnews("%s`^ was ruthlessly killed `)by a ghostly apparation`^ in the forest.",$session['user']['name']);
				if ($exploss>0) output(" You lose `^%s percent`@  of your experience and all of your gold.",get_module_setting("experienceloss"));
				$session['user']['experience']-=$exploss;
				$session['user']['gold']=0;
				debuglog("lost $exploss experience and all gold to a halloween ghost.");
				addnav("Return");
				addnav("Return to the Shades","shades.php");
				$session['user']['specialinc'] = "";
				$session['user']['specialmisc'] = "";
				$badguy=array();
				$session['user']['badguy']="";
				if (isset($suspendbuffs) && $suspendbuffs) unsuspend_buffs('allowintrain',"");
			}else{
				require_once("lib/fightnav.php");
				$allow = true;
				fightnav($allow,false);
				if ($session['user']['superuser'] & SU_DEVELOPER) addnav("Escape to Village","village.php");
			}            	
			break;
		case "flight":
			//automatic runaway, you can flee and lose some gold 
			output("`7With the `jghost's `7chilling gaze bearing down on you, you decide that discretion is the better part of valor. You turn on your heel and run, crashing through the underbrush and leaping over fallen logs. Behind you, the ethereal wail of the `jghost `7slowy fades as you put distance between it and you. Your `@Taijutsu `7training at the academy has saved you.`n`n");
			$goldloss = round(max(0,e_rand(1,$session['user']['gold']/50)));
			if ($session['user']['gold']>=$goldloss) {
				output("You lost %s gold while rushing away hastily!",$goldloss);
				$session['user']['gold']-=$goldloss;
			} else {
				output("You lost no gold, because you can't steal from empty pockets...`n`n");
			}
			// end the event
			$session['user']['specialinc'] = "";
			break;
		case "suicide":
			//the apparation seems to look puzzled (just make a good story for each ^^)
			$who = (int)httpget('who');
			$hasmet = (int)get_module_pref("metwho");
			if ($hasmet == (HALLOWEEN_BROTHER|HALLOWEEN_SISTER)) $check = HALLOWEEN_BROTHER|HALLOWEEN_SISTER;
			else $check = $who;
			switch ($check) {
				case (HALLOWEEN_BROTHER|HALLOWEEN_SISTER):
					//met both, happy ending time
					//TODO: text that behind you appears the ghost you already met, and they re-unite.
					if ($who == HALLOWEEN_BROTHER) {
						output("`6You encounter the `jbrother ghost `6once again. But before you can interact with him, another `jspectral figure `6materializes from behind and swoops in front of you. It is the `jsister ghost`6. It seems she has been following you around ever since your last meeting.");
					} else {
						output("`6You encounter the `jsister ghost `6once again. But before you can interact with her, another `jspectral figure `6materializes from behind and swoops in front of you. It is the `jbrother ghost`6. It seems he has been following you around ever since your last meeting.");
					}
					output("`nThe two `jghostly siblings `6embrace each other with happy tears in their eyes as they are finally reunited, and the ghastly wounds that once covered their bodies slowly fade away. Carrying his sister on his back, the two give you a grateful nod and head towards the exit of the forest before dissipating into a soft ray of light.`n`n");
					output("`xThat's it, folks.`n`n`vThank you very much for playing this (short) event, the beautiful pics by `1Jean Yvo`x and the text by `yGyururu`x!`n`n");

					halloweenghosts_image($imagefolder."ghosts-united.jpg");
					break;
				case HALLOWEEN_SISTER:
					output("`7As you try to calm your nerves and approach with caution, from the darkness emerges the `jspectral figure`7 of a girl . She appears both lost and frightened, her gaze desperately searching for something with no regard to the `\$wounds `7she has suffered. It's a haunting sight, one that makes your heart ache as you wonder can she even feel them in her state anymore. After a few moments a realization dawns upon you as you remember the tragic tale of a pair of `lsiblings `7the hardships they faced during the `qThird Great Ninja War `7as they attempted to reunite with each other, only to both perish right before they could. `n`n

							Looking upon her, you can't think of any other explanation but that the siblings are the very same as the rumored ghostly pair, and before you stands the sister that wanders these woods in search of her brother. You reach out and attempt to catch her attention, but she seems to utterly disregard you. Perhaps there might be another way to help her...`n`n");
					if (get_module_setting("haseverattacked")==1) {
						output("Judging by %s visibly angered demeanor, %s seems to hold no desire in being civil with you, though...`n`n",$sex3,$sex2);
					}
					if ($hasmet==HALLOWEEN_BROTHER) {
						set_module_pref("metwho",HALLOWEEN_BROTHER|HALLOWEEN_SISTER);
					} else {
						set_module_pref("metwho",HALLOWEEN_SISTER);
					}

					break;
				case HALLOWEEN_BROTHER:
					output("`7As you try to calm your nerves and approach with caution, as the darkness peels away the `jspectral figure `7of a young man appears. His grievous `\$wounds `7are a chilling testament to the more than likely reason for his see-through state. And yet he does not seem bothered by them and rather appears as though waiting for someone, his gaze fixed on no particular point in the distance as if he was staring off somewhere terribly far. As you ponder this the tale of two `lsiblings `7 who met their tragic end during the `qThird Great Ninja War `7comes to mind, one perished at an arranged meeting location while the other did before ever reaching it. `n`n

							As you recall the rumor of the local woods being haunted returns to your mind, you can think of no other explanation except one of its origin sources is the brother before you, forever waiting to reunite with his sister. Appearing quite oblivious to your presence, perhaps there is another way to help them...`n`n");
					if (get_module_setting("haseverattacked")==1) {
						output("Judging by %s visibly angered demeanor, %s seems to hold no desire in being civil with you, though...`n`n",$sex3,$sex2);
					}
					if ($hasmet==HALLOWEEN_SISTER) {
						set_module_pref("metwho",HALLOWEEN_BROTHER|HALLOWEEN_SISTER);
					} else {
						set_module_pref("metwho",HALLOWEEN_BROTHER);
					}
					break;
			}
			// end the event
			$session['user']['specialinc'] = "";
			break;	
		case "hound":
			//player meets the scheme (hound)
			output("`7As you make leave, a `jspectral hound `7materializes in front of you, its body enveloped by a `lghostly flame`7. From the way it behaves, you could tell that it was a ninken trained by the `3Inuzuka Clan`7. It makes no move to attack. It simply stands there, watching you with a haunted gaze.`n`n");
			halloweenghosts_image($imagefolder."hound2.jpg");
			$who = HALLOWEEN_HOUND;
			output("`7You could try to flee, but you have a feeling that it won't let you go so easily. You could also try to attack it, but you have a feeling that it won't fight back. You could also try to approach it in a friendly manner, but you have a feeling that it won't respond to you.`n`n");
			output("Given it is the year 2023, you could also try to take a selfie with it, but you have a feeling that it won't be amused.`n`n");
			output("So for now, you turn back calmly and leave the `jghostly hound `7alone, staring you off.`n`n");
			// end the event
			$session['user']['specialinc'] = "";

			break;
		default:
			// event concluded
			if (get_module_pref("metwho") == (HALLOWEEN_BROTHER | HALLOWEEN_SISTER)) {
				//happy screen
				output("`c`\$Replay`c`6`nA familiar sight. The two `jghostly siblings `6embrace each other with happy tears in their eyes as they are finally reunited, and the ghastly wounds that once covered their bodies slowly fade away. Carrying his sister on his back, the two give you a grateful nod and head towards the exit of the forest before dissipating into a soft ray of light.`n`n");
				output("`xThat's it, folks.`n`n`vThank you very much for playing this (short) event, the beautiful pics by `1Jean Yvo`x and the text by `yGyururu`x!`n`n");

				halloweenghosts_image($imagefolder."ghosts-united.jpg");
				// end the event
				$buff = array(
						"name"=>"`\$S`4ibling `\$R`4eunion`0",
						"rounds"=>15,
						"wearoff"=>"`4`bYou feel happy about the halloween ghost sibling, but the buff wears off.`b`0",
						"atkmod"=>1.1,
						"defmod"=>1.1,
					     );
				apply_buff("halloweenghosts", $buff);
				$session['user']['specialinc'] = "";
				return;
			}
			$chance = e_rand(0,100);
			// meet randomly 
			output("`7You spot a `jghostly figure `7out of the corner of your eye that disappears when you try to follow it with your gaze.  If the tale is to be believed, what you saw could be one of the two siblings that haunt these woods.`n`n");
			if ($chance<30) {
				//player meets the brother 
				halloweenghosts_image($imagefolder."ghost-older.jpg");
				$who = HALLOWEEN_BROTHER;

			} elseif ($chance<50) {
				//player meets the sister
				//TODO: write a short text they meet the sister
				halloweenghosts_image($imagefolder."ghost-younger.jpg");
				$who = HALLOWEEN_SISTER;

			} else {
				// output a few lines to the player telling them they are seeing a shade in the forest rushing away
				halloweenghosts_image($imagefolder."scheme.jpg");
				$who = HALLOWEEN_SHADE;
				// add a Navigation
				addnav("Actions");
				addnav("Try to bugger off",$from."op=hound&who=".$who);
			}

			if ($who != HALLOWEEN_SHADE) {
				//if not shade, you can act
				addnav("Actions");
				addnav("Try to flee",$from."op=flight&who=".$who);
				addnav("Attack",$from."op=attack&who=".$who);
				if (get_module_pref("hasattacked")==1) {
					addnav("Approach calmly (you attacked last time...)","");
				} else {
					addnav("Approach calmly",$from."op=suicide&who=".$who);
				}

			} else {
				// nothing right now
			}

	}
	return;
}
function halloweenghosts_run(){}	
?>
