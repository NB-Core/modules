<?php
require_once("./modules/alignment/func.php");
/*
   Temple of Shadow and Light
   File Name: temple.php
Author: Chris Vorndran (Sichae)
Version: 1.0

One of my moderators notices a barreness to the Gardens.
So, him and I concocted some ideas.
I rushed out and decided to make this one.
I hope it all works out.
Wish me luck!

v1.0
Going into Testing.

v1.2 (11/4/04)
Forest Event Fixed by WebPixie

v1.25
White Space Removed
Transalation Ready

v1.3
Based Alignment off of Deeds, rather than a choice

v1.4
Now works directly with the Alignment Module.
 */
function temple_getmoduleinfo(){
	$info = array(
			"name"=>"Temple of Shadow and Light",
			"author"=>"Chris Vorndran",
			"version"=>"1.45",
			"category"=>"Gardens",
			"download"=>"http://dragonprime.net/users/Sichae/temple.zip",
			"vertxtloc"=>"http://dragonprime.net/users/Sichae/",
			"description"=>"This module provides something new for the Gardens. By helping/destroying people in the forest, a user will be able to trade in points for certain boons.",
			// Yes, I said Gardens ^_^
			"settings"=>array(
				"Temple of Shadow and Light Settings,title",
				"times"=>"How many times can you encounter a special per DK,int|20",
				"alignamnt"=>"How much does this increase Alignment,int|5",
				"Temple Costs,title",
				"speccost"=>"How much does increment specialty cost,int|1",
				"statcost"=>"How much do attack and defense cost.int|6",
				),
			"prefs"=>array(
				"Temple of Shadow and Light Preferences,title",
				"used"=>"How many times has the special been found this DK,int|0",
				"points"=>"How many points has the user earned,int|0",
				"quota"=>"Has the quota been filled for this dk,bool|0",
				)
				);
	return $info;
}
function temple_install(){
	module_addhook("gardens");
	module_addhook("dragonkilltext");
	module_addeventhook("forest", "return 100;");
	return true;
}
function temple_uninstall(){
	return true;
}
function temple_dohook($hookname,$args){
	global $session;
	global $texts;
	$align=get_module_pref("alignment","alignment");
	$evilalign=get_module_setting('evilalign','alignment');
	$goodalign=get_module_setting('goodalign','alignment');
	switch($hookname){
		case "gardens":
			addnav("Explore");
			addnav("Temple of Shadow and Light","runmodule.php?module=temple&op=enter");
			break;
		case "dragonkilltext":
			if ($align>=$goodalign){
				$color = translate_inline("`&white`0");
			}elseif ($align<=$evilalign){
				$color = translate_inline("`)black`0");
			}else{
				$color = translate_inline("`7gray`0");
			}
			output("`n`n`@A small faerie walks over to you and begins to sap a %s`@ energy from you.",$color);
			output(" As your spirit is drained, the small faerie returns to the gardens.");
			set_module_pref("used",0);
			set_module_pref("quota",0);
			//if (is_module_active('alignment')) {A
			//		set_module_pref('alignment',get_module_pref('alignment','alignment')-50);
			//		}
			break;
	}
	return $args;
}
function temple_runevent($type){
	temple_helpers($type);
}
function temple_helpers($type){
	global $session;
	$evilalign=get_module_setting('evilalign','alignment');
	$goodalign=get_module_setting('goodalign','alignment');
	$align=get_module_pref("alignment","alignment");
	$alignamnt=get_module_setting("alignamnt");
	$used=get_module_pref("used");
	$quota=get_module_pref("quota");
	$times=get_module_setting("times");
	$from = "forest.php?";
	$session['user']['specialinc'] = "module:temple";
	$op = httpget('op');
	if ($align==0){
		output("You look around and see nothing.");
		output(" A brief image of the gardens flashes in your mind, but you dismiss it.");
	} else {
		if ($op=="" || $op=="search"){
			if ($quota == 1){
				if ($align>=$goodalign){
					$apref = translate_inline("`&good`0");
				}if ($align<=$evilalign){
					$apref = translate_inline("`)bad`0");
				}else{
					$apref = translate_inline("`7normal`0");
				}
				output("A small faerie wanders out and beats you on the nose.");
				output(" You have already done enough %s deeds for this lifetime.",$apref);
			} else {
				output("You wander into a small clearing and from the outside, you can hear shrill cries.`n");
				output("You pick up your things and dash out of the forest and over to the source of crying.`n");
				output("You stop in the middle of your trek and wonder. \"`3Should I stay or should I go?\"");
				addnav("Help Them", $from . "op=help");
				addnav("Destroy Them", $from . "op=dest");
			}
		}
	}
	if ($op=="help"){
		output("You cautiously run over to the source of the screaming and you find");
		switch(e_rand(1,3)){
			case 1:
				output(" a little girl, being attacked by a wolf.");
				output(" You walk over and beat the wolf on the head, making it scamper away.");
				output(" The girl stands up and hugs you tightly.`n`n");
				output(" `3\"Thanks so much!\"");
				if (is_module_active('alignment')) {
					align("+$alignamnt");
				}
				$points=get_module_pref("points")+1;
				set_module_pref("points",$points);
				$used=get_module_pref("used")+1;
				set_module_pref("used",$used);
				if ($used==$times){
					set_module_pref("quota",1);
				}

				break;
			case 2:
				output(" a little boy, stuck in a well.");
				output(" You take out a long silver rope and lower it into the well.");
				output(" You hoist the boy out and he hugs you.`n`n");
				output(" `3\"Thanks a bunch!\"");
				if (is_module_active('alignment')) {
					align("+$alignamnt");
				}
				$points=get_module_pref("points")+1;
				set_module_pref("points",$points);
				$used=get_module_pref("used")+1;
				set_module_pref("used",$used);
				if ($used==$times){
					set_module_pref("quota",1);
				}
				break;
			case 3:
				output(" an old man, trapped under a fallen log.");
				output(" You walk over and easily lift the log off of him.");
				output(" He gains his stance and kindly shakes your hand.`n`n");
				output(" `3\"Thanks Kiddo!\"");
				if (is_module_active('alignment')) {
					align("+$alignamnt");
				}
				$points=get_module_pref("points")+1;
				set_module_pref("points",$points);
				$used=get_module_pref("used")+1;
				set_module_pref("used",$used);
				if ($used==$times){
					set_module_pref("quota",1);
				}
				break;
		}
	}
	if($op=="dest"){
		output("You cautiously run over to the source of the screaming and you find");
		switch(e_rand(1,3)){
			case 1:
				output(" a little girl, being attacked by a wolf.");
				output(" You heckle the wolf and it begins to chew into the girl harder.");
				output(" Your sadistic self can't help but watch.");
				output(" In the end, the little girl dies.");
				output(" You shrug it off and continue in the woods.");
				if (is_module_active('alignment')) {
					align("-$alignamnt");
				}
				$points=get_module_pref("points")+1;
				set_module_pref("points",$points);
				$used=get_module_pref("used")+1;
				set_module_pref("used",$used);
				if ($used==$times){
					set_module_pref("quota",1);
				}
				break;
			case 2:
				output(" an old man, trapped under a log.");
				output(" You walk over and sit on the log.");
				output(" You watch as the old man writhes about and screams.");
				output(" The screams die out and you look down and see a puddle of blood.");
				output(" You finish up your sandwich and walk off.");
				if (is_module_active('alignment')) {
					align("-$alignamnt");
				}
				$points=get_module_pref("points")+1;
				set_module_pref("points",$points);
				$used=get_module_pref("used")+1;
				set_module_pref("used",$used);
				if ($used==$times){
					set_module_pref("quota",1);
				}
				break;
			case 3:
				output(" a little boy, stuck in a well.");
				output(" You look down and hear the little boy call out for help.");
				output(" Smiling, you chuck a few rocks down, bludgeoning the kid.");
				output(" After you throw a rather large rock, no sound is heard.");
				output(" You dust off your hands and walk off into the woods.");
				if (is_module_active('alignment')) {
					align("-$alignamnt");
				}
				$points=get_module_pref("points")+1;
				set_module_pref("points",$points);
				$used=get_module_pref("used")+1;
				set_module_pref("used",$used);
				if ($used==$times){
					set_module_pref("quota",1);
				}
				break;
		}
	}
}
function temple_run(){
	global $session;
	$evilalign=get_module_setting('evilalign','alignment');
	$goodalign=get_module_setting('goodalign','alignment');
	$align=get_module_pref("alignment","alignment");
	$points=get_module_pref("points");
	$quota=get_module_pref("quota");
	$speccost = get_module_setting("speccost");
	$statcost = get_module_setting("statcost");
	if ($align>=$goodalign){
		$talign = translate_inline("`&Light`0");
	}elseif ($align<=$evilalign){
		$talign = translate_inline("`)Shadow`0");
	} else {
		$talign = translate_inline("`7Neutrality`0");
	}
	page_header("Temple of Shadow and Light");
	modulehook("header-temple",array());
	$op = httpget('op');
	output("`c`b`&Temple of Shadow and Light`0`b`c`n");
	if ($op=="enter"){
		output("A vast temple folds out in front of you.");
		output(" A grand tapestry lines the floor, depicting a heroic battle against the Dragon Queen.");
		output(" A surge of power overwhelms you and you collapse to the ground.");
		output("`n`n You awake in a small chamber and look all around.");
		output(" Not a single thing to look upon.");
		output(" A small faerie flutters over to you.`n`n");
		output(" \"`5So, you have found this temple, eh?`0\" she smirks.`n`n");
		output(" You look upon her and laugh.`n`n");
		output(" \"`5Do you want my services or not?`0\" she scoffs.`n`n");
		addnav("Answer");
		addnav("Yes, I do","runmodule.php?module=temple&op=agree");
		addnav("No, I'm leaving","gardens.php");
	}elseif ($op=="agree"){
		addnav("Gardens");
		addnav("Return to Reality","gardens.php");
		if($points > 0) {
			output("The tiny faerie frees you and takes you over to a small pedestal.");
			output(" \"`5So...it seems you are aligned with the %s.",$talign);
			output(" `5Well, I do offer some services. What would you like?`0\"");
			addnav("Options");
			if ($points >= $speccost) addnav(array("Increase Specialty (%s point)",$speccost),"runmodule.php?module=temple&op=spec");
			if ($points >= $statcost){
				addnav("Options");
				addnav(array("Increase Attack",$statcost),"runmodule.php?module=temple&op=atk");
				addnav(array("Increase Defense",$statcost),"runmodule.php?module=temple&op=def");
			}

			output("`n`n`2You have a total of `\$%s point(s) to spend`2.",$points);
		}elseif ($points==0){
			output("Why don't you try going to the forest and feeding your alignment.`n`n");
		}
	}
	if ($op=="spec"){
		output("The tiny faerie taps you on the brow, and a small speck of blood drapes down.");
		$points = get_module_pref("points")-$speccost;
		set_module_pref("points",$points);
		require_once("lib/increment_specialty.php");
		increment_specialty("`^");
		addnav("Gardens");
		addnav("Return to Reality","gardens.php");
	}
	if ($op=="atk"){
		output("The tiny faerie attacks at your arms, and your muscles get larger.");
		$session['user']['attack']+=1;
		$points=get_module_pref("points")-$statcost;
		set_module_pref("points",$points);
		addnav("Gardens");
		addnav("Return to Reality","gardens.php");
	}
	if ($op=="def"){
		output("The tiny faerie attacks at your back, and your skin gets a bit more hard.");
		$session['user']['defense']+=1;
		$points=get_module_pref("points")-$statcost;
		set_module_pref("points",$points);
		addnav("Gardens");
		addnav("Return to Reality","gardens.php");
	}
	page_footer();
}
?>
