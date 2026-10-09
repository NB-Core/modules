<?php
require_once("lib/titles.php");
require_once("lib/names.php");
require_once("lib/commentary.php");
function drpap_getmoduleinfo(){
	$info = array(
		"name"=>"Dr. Paprika MD",
		"author"=>"Chris Vorndran",
		"version"=>"1.0",
		"category"=>"Village|Shops",
		"download"=>"http://dragonprime.net/users/Sichae/drpap.zip",
		"vertxtloc"=>"http://dragonprime.net/users/Sichae/",
		"description"=>"Allows users to get a sex change operation.",
			"settings"=>array(
				"Dr Paprika MD - Settings,title",
				"times"=>"How many times are users allowed to visit Dr Paprika?,int|2",
				"The amount of times per users is never reset by default. This is to impede the rampant usage of it,note",
				"reset"=>"Does the amount of times reset at DK?,bool|0",
				"paploc"=>"Where is Dr Paprika's Office located,location|".getsetting("villagename", LOCATION_FIELDS),
				"Dr Paprika MD - Cost Settings,title",
				"dk"=>"How many DKs are needed before this is available,int|5",
				"gold"=>"Cost in gold for operation,int|5000",
				"gems"=>"Cost in gems for operation,int|10",
				"name"=>"What is Dr. Paprikas name (Dr. still stays in title)?,text|Paprika",
			),
			"prefs"=>array(
				"Dr Paprika MD - Prefs,title",
				"count"=>"How many times has this person changed their gender?,int|0",
			),
	);
	return $info;
}
function drpap_install(){
	module_addhook("village");
	module_addhook("changesetting");
	module_addhook("dragonkilltext");
	module_addhook("moderate");
	return true;
}
function drpap_uninstall(){
	return true;
}
function drpap_dohook($hookname,$args){
	global $session;
	switch ($hookname){
		case "village":
			if ($session['user']['location'] == get_module_setting("paploc") && $session['user']['dragonkills'] >= get_module_setting("dk")){
				tlschema($args['schemas']['marketnav']);
				addnav($args['marketnav']);
				tlschema();
				addnav(array("Dr %s`0's Office",get_module_setting("name")),"runmodule.php?module=drpap&op=enter");
			}
			break;
	    case "changesetting":
		    if ($args['setting'] == "villagename") {
				if ($args['old'] == get_module_setting("paploc")) {
					set_module_setting("paploc", $args['new']);
				}
			}
			break;
		case "dragonkilltext":
			if (get_module_setting("reset") == 1) set_module_pref("count",0);
			break;
		case "moderate":
			$name=get_module_setting("name");
			$args['drpap'] = "$name's Waiting Room";
			break;
	}
	return $args;
}
function drpap_run(){
	global $session;
	$c = get_module_pref("count");

	$t = get_module_setting("times");
	$gold = get_module_setting("gold");
	$gems = get_module_setting("gems");

	$op = httpget('op');
	$dec = httpget('dec');

	$gen = translate_inline($session['user']['sex']==0?"sir":"madam");
	$ngen = translate_inline($session['user']['sex']==1?"sir":"madam");
	$g = translate_inline($gems==1?"Gem":"Gems");
	$pap=get_module_setting("name");
	page_header("Dr %s's Office",sanitize($pap));

	switch ($op){
		case "enter":
			if ($c < $t){
				output("`3Subtle tunes play in the background; classic rock.");
				output("In the Center of the waiting room, is a large table - covered with out-of-date magazines.");
				output("A beautiful receptionist looks at you, across a pane of glass.");
				output("She glances at you, and waves, \"`%Hello there %s.",$gen);
				output("Are you here to see `QDr %s`%?",$pap);
				output("He is the only doctor in %s, excelling in the field of `\$Gender Changes`%.`3\"",get_module_setting("paploc"));
				addnav("Where to?");
				addnav(array("Dr %s`0's Room",$pap),"runmodule.php?module=drpap&op=office");
				addnav("Waiting Room","runmodule.php?module=drpap&op=waitroom");
			}else{
				output("`3\"`%I am sorry %s, but you have already had %s `\$Gender Changes`%.",$gen,$c);
				output("Since the limit is %s, we are able to deny you service now.",$t);
				output("Please take care.`3\"");
				if (get_module_setting("reset") == 1) output("`3The receptionist adds, \"`%If you come back with some `@dragon's blood`%, we might be able to work something out.`3\"");
			}
			break;
		case "office":
			if ($dec != "yes"){
				if ($session['user']['gold'] >= $gold && $session['user']['gems'] >= $gems){
					output("`3You walk in a white room, very white.");
					output("A long operating table, is accompanied by a small swivel chair.");
					output("Strutting out from the shadows, `QDr %s `3appears, and shakes your hand.",$pap);
					output("\"`\$So, you wish to have a `%Gender Change `\$operation?`3\"");
					output("`n`n`QDr %s `qpoints to a sign, stating \"`\$Gender Operations cost `^%s Gold`\$ and `5%s `%%s`\$.`q\"",$pap,$gold,$gems,$g);
					addnav("Choices");
					addnav("Yes","runmodule.php?module=drpap&op=office&dec=yes");
					addnav("Return to the Waiting Room","runmodule.php?module=drpap&op=waitroom");
				}else{
					output("`QDr %s `3stares at your blankly.",$pap);
					output("\"`\$I am sorry %s, but you do not have the proper funds for this operation.`3\"",$gen);
					output("`QDr %s `3points to a sign, stating \"`%Gender Operations cost `^%s `%Gold and `5%s `%%s.`3\"",$pap,$gold,$gems,$g);
				}
			}else{
				output("`QDr %s `3nods and begins to prep himself.",$pap);
				output("\"`\$Please lay down on the operating table... I will be with your shortly...`3\"");
				output("`QDr %s `3hovers over you and then pulls out a tiny bottle of ether.",$pap);
				output("He presses it to your nose and instructs, \"`\$Please count to One Hundred...`3\"");
				output("You begin to count...`n`n");
				for ($i = 1; $i <= $session['user']['level']; $i++){
					output("%s...`n",$i);
				}
				if ($session['user']['sex'] == 0){
					$session['user']['sex'] = 1;
				}else{
					$session['user']['sex'] = 0;
				}
				$c++;
				set_module_pref("count",$c);
				$session['user']['gold']-=$gold;
				$session['user']['gems']-=$gems;
				$newtitle = get_dk_title($session['user']['dragonkills'], $session['user']['sex']);
				$newname = change_player_title($newtitle);
				$session['user']['title'] = $newtitle;
				$session['user']['name'] = $newname;
				output("`nYou awaken hours later, your muscles shuddering.");
				output("You grasp the mirror to your side, and then look at yourself.");
				output("Surprisingly, you are now a healthy looking %s.",$ngen);
				output("You stand quickly, and shake `QDr %s`Q's `3hand, thanking him profusely.",$pap);
				output("Taking up your clothes, you strut out from the office, smiling happily.");
			}
			break;
		case "waitroom":
			output("`3All around you, people are sitting.");
			output("Some are thumbing through magazines, whilst others are tapping their feet in anticipation.`n`n");
			addcommentary();
			viewcommentary("drpap","Nervous People are Around",15,"says");
			addnav("Return to Lobby","runmodule.php?module=drpap&op=enter");
			break;
	}
	addnav("Leave");
	villagenav();
page_footer();
}
?>
