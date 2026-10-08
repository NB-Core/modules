<?php
if (isset($_GET['op']) && $_GET['op']=="download"){ // this offers the module on every server for download
 $dl=join("",file("beach_sandcastle.php"));
 echo $dl;
}

// This module was based on Red Yates Pumpkin Carving Contest, with the texts
// changed to sandcastle building. The location was moved from a city to the beach.
// I added an option to trample other people's sandcastles and get a mean title
// for that.

function beach_sandcastle_getmoduleinfo(){
	$info = array(
		"name"=>"Beach - Sandcastle Contest",
		"author"=>"idea by eph, using code by Red Yates",
		"version"=>"1.0",
		"category"=>"Village|Beach",
		"download"=>"modules/beach_sandcastle.php?op=download",
		"settings"=>array(
			"Sandcastle Module Settings, title",
			"perday"=>"Tries at building per day, int|1",
			"trampleperday"=>"Tries at trampling per day, int|1",
			"cost"=>"How much to build a sandcastle, int|40",
			"winner"=>"Whose sandcastle is on display, int|0",
		),
		"prefs"=>array(
			"Sandcastle User Prefs, title",
			"tries"=>"Times built today,int|0",
			"tramples"=>"Times trampled today,int|0"
		),
	);
	return $info;
}

function beach_sandcastle_install(){
	if (!is_module_installed("beach")) {
    output("This module requires the Beach Resort module to be installed.");
    return false;
	}
	module_addhook("beach");
	module_addhook("newday");
	module_addhook("beach-desc");
	return true;
}

function beach_sandcastle_uninstall(){
	return true;
}

function beach_sandcastle_dohook($hookname, $args){
	global $session;
	switch($hookname){
	case "beach":
		addnav("At the Beach");
		addnav("Sandcastle Contest", "runmodule.php?module=beach_sandcastle");
		break;
	case "newday":
		set_module_pref("tries",0);
		set_module_pref("tramples",0);
		break;
	case "beach-desc":
		$winner=get_module_setting("winner");
		if ($winner>0){
			$sql = "SELECT name FROM ". db_prefix("accounts") .
				" WHERE acctid='$winner'";
			$result = db_query_cached($sql, "beach_sandcastlewinner");
			$row = db_fetch_assoc($result);
			output("`n`QA lovely sandcastle with high towers and a moat sits between smaller sandcastles. It bears a tiny flag on its tower reading \"`&%s`^\".`n",$row['name']);
		}
		break;
	}
	return $args;
}

function beach_sandcastle_run(){
	global $session;
	page_header("Sandcastle Building Contest");

	$cost=get_module_setting("cost");
	$perday=get_module_setting("perday");
	$tries=get_module_pref("tries");
	$trampleperday=get_module_setting("trampleperday");
	$tramples=get_module_pref("tramples");
	$op=httpget('op');
	if ($op==""){
		if ($tries<$perday){
			$title=translate_inline($session['user']['sex']?"babe":"dude");
			output("`Q`c`bSandcastle Building Contest`b`c`n");
			output("`^A section of the beach is reserved for building sandcastles. Your curiosity makes you walk over to the area to take a closer look. Some sandcastles are just crude heaps of sand, others are intricately decorated with seashells and other natural materials.");
			output("As you wander along the aisle of miniature buildings you also notice a blonde guy in red speedos who looks like he was the cover model for Tynan's latest bodybuilder catalogue.");
			output("He comes over to you with a professional grin on his face and says, \"`QHello there, %s!",$title);
			output("My name's Guy, and I am the judge here. How do you like the entries for our daily sandcastle building contest? Care to give it a try too?");
			output("It's only `^%s`Q gold.`^\"",$cost);
				addnav(array("Trample down sandcastles"),
						"runmodule.php?module=beach_sandcastle&op=trample");
			if ($session['user']['gold']<$cost){
				output("`n`nToo bad you don't have that amount of gold with you.");
				output("You head back to the public area of the beach.");
			}else{
				addnav(array("Build a sandcastle (`^%s`0 gold)",$cost),
						"runmodule.php?module=beach_sandcastle&op=build");
			}
		}else{
			output("`^You walk over to Guy to convince him to let you have a second entry in the contest, but he's busy flexing his muscles and doesn't notice you. So you give up and wander back to the beach.`n`n");
		}
		addnav("Back to the beach", "runmodule.php?module=beach");
	}elseif ($op=="build"){
		$tries++;
		set_module_pref("tries",$tries);
		$session['user']['gold']-=$cost;
		debuglog("spent $cost gold to build a sandcastle.");
		addnav("Back to the beach", "runmodule.php?module=beach");
		output("`^You decide to build a sandcastle.");
		output("You pay Guy his `^%s`^ gold, and get a bucket and a small shovel in return.",$cost);
		output("Excited you go to work and let the child in you get the upper hand, as Guy watches with interest.`n`n");
		output("After a lot of effort, you wipe your brow and look at what you've accomplished.`n`n");
		$result=e_rand(1,30);
		if ($result==30){ //30, 1:30
			set_module_setting("winner",$session['user']['acctid']);
			apply_buff('pumpkinwin',array("name"=>"`^Sandcastle Master","rounds"=>15, "atkmod"=>1.03,"defmod"=>1.01, "schema"=>"module-beach_sandcastle"));
			$reward=4*$cost;
			$session['user']['gold']+=$reward;
			$richer=$reward-$cost;
			debuglog("won $reward gold from building a sandcastle.");
			output("`^Guy applauds your work with loud clapping.");
			output("\"`QSuperb! Excellent! A work of art indeed.");
			output("I can't remember the last time I've seen such a sandcastle.");
			output("You win this contest!`^\"");
			output("Guy hands a large seashell purse with some gold over to you.`n`n");
			output("This needs to go on display, definitely.`^\"`n`n");
			output("You beam with pride as you watch him pin a flag to your castle's tower. Satisfied you walk back to the beach, %s gold richer than when you came in.",$richer);
		}elseif ($result<6){ //1 to 5, 5:30, 1:6
			output("Your sandcastle is barely more than a heap of sand.");
			output("Guys gives you a toothy grin and shakes his head, \"`QPerhaps next time, eh?`^\"");
			output("`n`nDisappointed, you head back to the beach.");
		}elseif ($result<30 && $result >20){ //21 to 29, 9:30, 3:10
			$reward=2*$cost;
			$session['user']['gold']+=$reward;
			$richer=$reward-$cost;
			debuglog("won $reward gold from building a sandcastle.");
			output("Your sandcastle is nicely decorated with some seashells, yet slightly warped to the left.");
			output("Guy looks at your work closely and says, \"`QYes, very nice.");
			output("This is a high quality sandcastle. On another day this might have won first prize.`^\"");
			output("Guy tosses you a small seashell purse with some gold in it.`n`n");
			output("You head back to the beach, `^%s`^ gold richer than when you came in.",$richer);
		}elseif ($result>5 && $result<11){ //6 to 10, 5:30, 1:6
			output("With some imagination your work can be called a sandcastle. It has a warped tower and the moat ate the west wall away. Some of the decoration fell off...");
			output("Guy looks at your sandcastle skeptically, \"`QI can tell you put, err, your heart and soul into this.");
			output("Maybe you should exercise a bit with the kids.");
			output("Then surely you'll do better next time.`^\"`n`n");
			output("Disappointed, you head back to the beach.");
		}else{ //11 to 20, 9:30, 3:10
			$session['user']['gold']+=$cost;
			debuglog("won $cost gold from building a sandcastle.");
			output("Your sandcastle has two towers and a small moat, looking rather average.");
			output("Guy looks at your work and says, \"`QNot bad.");
			output("This is quite nice, really.");
			output("With some more architectural genuity, this could easily win first prize.`^\"`n`n");
			output("Guy hands a small seashell purse out to you.");
			output("You head back to the beach, having won back your %s gold.",$cost);
		}
	}elseif ($op=="trample"){
		addnav("Back to the beach", "runmodule.php?module=beach");
		if ($tramples<$trampleperday){
			output("`^You decide the fun about sandcastles is wrecking them when they are finished, and so you mow through the alley of miniature buildings like a dragon trampling a city.`n`n");
			$tramples++;
			set_module_pref("tramples",$tramples);
			$trample=e_rand(1,4);
			switch($trample)
			{
			case"1":
				output("Like an actor in a cheap dinosaur costume you mow through a sandcastle, until you are stopped by a glint in the corner of your eye.`n You examine the decoration of the ruined sandcastle closer and find a gem!`n`n Then you quickly make your way back to the beach before Guy can catch you.");
				$session['user']['gems']+=1;
			break;
			case"2": case"3":
				output("Nobody dares to stop you on your rampage, and you stomp on each sandcastle with delight until none is left. This reminded you pleasantly of your childhood, when you used to dash through heaps of fallen leaves.`n`n");
				output("Only now do you notice the crying children all around you, and you wince slightly under the accusing looks of their parents and Guy.");
				if (is_module_active('alignment')) {
					if (get_module_pref('alignment','alignment') < 10 && e_rand(0,50)==50){
					$name=$session['user']['name'];
					addnews("%s `7was awarded the title of Big Bad Meanie for being such a nasty person!",$name);
					$newtitle="Big Bad Meanie";
					require_once("lib/names.php");
					$newname = change_player_title($newtitle);
					$session['user']['title'] = $newtitle;
					$session['user']['name'] = $newname;
					output("`n`nFor being such nasty person you have been awarded the title of Big Bad Meanie!`n");
					}
					else 					set_module_pref('alignment',(get_module_pref('alignment','alignment')-3),'alignment');
				}
			break;
			case"4": case"5": case"6": case"7": case"8":
			output("`^Feeling like Godzilla, you cackle manically at the crying children, until you are faced by Guy. \"`QYou weirdo made the children cry. I'll show you how we handle troublemakers!\" `^You quickly realize that this guy indeed was the cover model of Tynan's latest bodybuilding catalogue, as Guy beats you up with no mercy.`n`n");
			output("When he finally lets go of you, you are barely alive.");
			$session['user']['hitpoints']=1;
			$name=$session['user']['name'];
			addnews("%s `7was beaten up at the beach for frightening the children!",$name);
			break;
			}
		} else output("`^You decide you caused enough wreckage here for today.");
		
	}

	page_footer();
}

?>
