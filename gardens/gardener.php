<?php
// translator ready
// addnews ready
// mail ready

/* gardener */
/* ver 1.0 by Shannon Brown => SaucyWench -at- gmail -dot- com */
/* 3rd December 2004 */

require_once("lib/villagenav.php");
require_once("lib/http.php");

function gardener_getmoduleinfo(){
    $info = array(
        "name"=>"Gardener",
        "version"=>"1.0",
        "author"=>"Shannon Brown `nadded questions by `2Oliver Brendel",
        "category"=>"Gardens",
        "download"=>"",
		"settings"=>array(
            "Gardener - Settings,title",
			"customtext"=>"Custom message for server,textarea|",
			"gardens"=>"Does the gazebo appear in the gardens? (setting yes nullifies city selector below),bool|1",
			"gardenerloc"=>"In which city does the gazebo appear,location|".getsetting("villagename", LOCATION_FIELDS)
		),
		"prefs"=>array(
            "Gardener - User Preferences,title",
			"seentoday"=>"Has the player visited today?,bool|0",
        )
    );
    return $info;
}

function gardener_install(){
	module_addhook("gardens");
	module_addhook("changesetting");
	module_addhook("village");
	module_addhook("newday");
	module_addhook("footer-newbieisland");
    return true;
}

function gardener_uninstall(){
    return true;
}

function gardener_dohook($hookname,$args){
    global $session;
	$gardens=get_module_setting("gardens");
    switch($hookname){
	case "changesetting":
		if (!$gardens && $args['setting'] == "villagename") {
			if ($args['old'] == get_module_setting("gardenerloc")) {
				set_module_setting("gardenerloc", $args['new']);
			}
		}
		break;
	case "gardens":
		if ($gardens) {
			addnav("Wooden Shrine");
			addnav("Gazebo","runmodule.php?module=gardener&loc=1");
			$customtext=get_module_setting("customtext");
			output_notl("`n`%%s`0", $customtext);
		}
		break;
	case "footer-newbieisland":
		output("`^This is the CENTRAL server. Please make sure you read the FAQs and check the Message of the Day (MoTD) each time you log in.");
		break;
	case "village":
		if (!$gardens &&
				$session['user']['location']==get_module_setting('gardenerloc')) {
			tlschema($args['schemas']['marketnav']);
			addnav($args['marketnav']);
			tlschema();
			addnav("Gazebo","runmodule.php?module=gardener&loc=0");
			$customtext=get_module_setting("customtext");
			output_notl("`n`n`c`@%s`0`c", $customtext);
		}
		break;
   	case "newday":
		set_module_pref("seentoday",0);
		break;
	}
    return $args;
}

function gardener_run() {
    global $session;
	$op=httpget('op');
	$seentoday=get_module_pref("seentoday");
	$gardens=httpget('loc');
	$place=translate_inline($gardens?"gardens":"square"); //not tl-ready //now it is
	require_once("modules/addimages/addimages_func.php");

	page_header("Yamato");
	output("`&`c`b`2Y`gamato, The Gardener`b`c");
	
	// set to == 1 when test complete
	if($seentoday==1){
		output("`7You walk towards `2Y`gamato `7again. ");
		output("`~\"I usually plant seeds into the soil...but I wouldn't mind putting you in there with them!\" `7he exclaims.`n`n");
		if (get_module_pref("user_addimages") != 1) gardener_addimage("gardener3.gif"); 	
		output("`n`n`7You freeze after you see the look on his face.");
		addnav("Navigation");
		if ($gardens) addnav("G?Return to the Gardens","gardens.php");
		if (!$gardens) villagenav();
	}elseif ($op==""){
		if (get_module_pref("user_addimages") != 1) gardener_addimage("gardener.gif"); 		
		output("`7In one corner of the %s, a path leads away through the trees.",$place);
		output("Beyond a white gazebo, `2Y`gamato `7is tending a small patch of `\$f`Ql`to`@w`!e`Vr`%s`7. As you approach, he straightens, and greets you with a smile.");
		output("`7\"`%Welcome to my gardens!`7\" he exclaims.");
		output("`7\"`%I know they're a little rough now, but I've a reward for you, if you know how to look after them!`7\" he exclaims.`n`n");
		output("Seeing as how the rest of the Gardens are meticulously well kept, you wonder what the small man is so concerned about.");
		output("The offer of a reward, however, is intriguing.`n`n");
		addnav("Try for a Reward","runmodule.php?module=gardener&op=ask&loc=$gardens");
		addnav("Navigation");
		if ($gardens) addnav("G?Return to the Gardens","gardens.php");
		if (!$gardens) villagenav();
	}elseif ($op=="ask"){

		// The questions have their expected answer associated with them
		// now, so you can change questions at your whim as long as you
		// put the correct answers there.
		$phrases = array(
			"It is ok to get romantic in an adult manner in the Gardens.|0",
			"It is ok to hit players in the Gardens.|0",
			"It is ok to get undressed in the Gardens.|0",
			"The Gardens are a place where you can use websites and email addresses.|0",
			"You can be rude to other players in the Gardens.|0",
			"You can say anything you like in the Gardens.|0",
			"You may roleplay a violent fight in the Gardens.|0",
			"You may roleplay a bedroom love scene in the Gardens.|0",
			"You may roleplay that another player is defenseless.|0",
			"You may upset other players.|0",
			"You may try to annoy others.|0",
			"You may ask every player in the Gardens to marry you.|0",
			"It is ok to discuss game secrets in the Gardens.|0",
			"The Gardens are for adults only, so you can speak about anything there.|0",
			"You may ask players to behave if they are breaking rules.|1",
			"The Gardens are a good place to rest from fighting.|1",
			"It is ok to hug other players in the Gardens.|1",
			"You may roleplay in the Gardens.|1",
			"You may get married in the Gardens.|1",
			"You should report players being cruel to others.|1",
			"General: It is advisable to refer your own character when you create a multi-char.|0",
			"General: You can have as many characters as you want.|1",
			"U can use any lAnGuAgE u lik 4 th3 g4m3, sl4ng rulez.|0",
			"General: Insulting someone does always cause only a temporary punishment.|0",
			"General: Another of your characters is best used getting your main character funds.|0",
			"General: If you have discovered a bug in the game, report it immediately.|1",
		);

		$random=array_rand($phrases);
		$myphrase=$phrases[$random];
		list($q,$a) = explode("|", $myphrase);
		set_module_pref("expectanswer", $a);

		output("`2Y`gamato `7asks you his question:`0`n`n");
		output("`7\"`%%s`7\"",$q);
		addnav("Answer");
		addnav("True","runmodule.php?module=gardener&op=answer&val=1&loc=$gardens");
		addnav("False","runmodule.php?module=gardener&op=answer&val=0&loc=$gardens");
	}else{
		$val = httpget('val');

		// Did we get it wrong?
		if ($val != get_module_pref('expectanswer')) {
			// bad result
			if (get_module_pref("user_addimages") != 1) gardener_addimage("gardener2.gif"); 				
			output("`7\"`%Oh dear, I hope you're new around here!`7\" he exclaims.");
			output("\"`%I'm afraid that answer is wrong.`7\"`n`n");
			output("`7You wander away sadly, determined to try harder next time.");
		}else{  // answer is correct
			if (get_module_pref("user_addimages") != 1) gardener_addimage("gardener4.gif"); 			
			output("`7\"`%Hey, you know what the Gardens are all about!`7\" he exclaims.");
			output("\"`%You're right, of course, and I have something for you.`7\"`n`n");
			$gift=e_rand(1, 17);
			if($gift==7) {
				output("`^He hands you a `%gem`^!");
				$session['user']['gems']++;
			}else{
				$vargold=e_rand(0,20);
				$addgold=$vargold+(round(max(10,(200-$session['user']['dragonkills']))*0.1)*$session['user']['level']);
				output("`^He hands you a bag containing %s gold!",$addgold);
				$session['user']['gold']+=$addgold;
			}
		}
		set_module_pref("seentoday",1);
		// And the correct return link(s)
		addnav("Navigation");
		if ($gardens) {
			addnav("G?Return to the Gardens","gardens.php");
		}
		villagenav();
	}
	page_footer();
}

function gardener_addimage($args) {
	if (!file_exists("modules/gardener/".$args)) return;
	output_notl("`c<img src=\"modules/gardener/".$args."\">`c<br>\n",true);
}

?>
