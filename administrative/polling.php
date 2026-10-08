<?php
require_once("lib/nltoappon.php");
function polling_getmoduleinfo(){
	$info = array(
		"name"=>"Polling For Propositions",
		"author"=>"Chris Vorndran",
		"version"=>"1.0",
		"category"=>"Administrative",
		"download"=>"http://dragonprime.net/users/Sichae/polling.zip",
		"vertxtloc"=>"http://dragonprime.net/users/Sichae/",
		"description"=>"Allows for a poll-type survey to be conducted via the grotto, on certain propositions.",
		"settings"=>array(
			"Poll Settings,title",
			"isactive"=>"Is this Poll active,bool|0",
			"subject"=>"Subject line for the nav,text|Hiring New Staff",
			"blurb"=>"Blurb about subject,textarea|",
			"Poll Choices,title",
			"one"=>"Option One,text|Yes",
			"two"=>"Option Two,text|No",
			"three"=>"Option Three,text|",
			"Poll Results,title",
			"voteone"=>"How many votes for option one,viewonly|0",
			"votetwo"=>"How many votes for option two,viewonly|0",
			"votethree"=>"How many votes for option three,viewonly|0",
		),
		"prefs"=>array(
			"hasvote"=>"Has the user Voted,bool|0",
			)
		);
	return $info;
}
function polling_install(){
	module_addhook("superuser");
	return true;
}
function polling_uninstall(){
	return true;
}
function polling_dohook($hookname,$args){
	global $session;
	switch ($hookname){
		case "superuser":
			if (get_module_setting("isactive") == 1){
			addnav("*Check Often*");
			addnav(array("`^`bPoll For`b`0: %s",get_module_setting("subject")),"runmodule.php?module=polling&op=enter");
		}
			break;
	}
	return $args;
}
function polling_run(){
	global $session;
	$subject = get_module_setting("subject");
	$one = get_module_setting("one");
	$two = get_module_setting("two");
	$three = get_module_setting("three");
	$voteone = get_module_setting("voteone");
	$votetwo = get_module_setting("votetwo");
	$votethree = get_module_setting("votethree");
	$hasvote = get_module_pref("hasvote");
	page_header("$subject");
	$ord = translate_inline($three==""?"two":"three");
	$oneare = translate_inline($voteone != 1?"are":"is");
	$ones = translate_inline($voteone != 1?"s":"");
	$twoare = translate_inline($votetwo != 1?"are":"is");
	$twos = translate_inline($votetwo != 1?"s":"");
	$threeare = translate_inline($votethree != 1?"are":"is");
	$threes = translate_inline($votethree != 1?"s":"");
			
	$op = httpget('op');

	switch ($op){
		case "enter":
			if ($hasvote == 0){
			output("`3You notice a large congregation.");
			output(" Today they are talking about some new subject, about: `^%s`3.",$subject);
			output(" You notice a small booth, and see %s levers.",$ord);
			if ($three<> ""){
				output(" One for `#%s3, one for `@%s `3and one for `\$%s`3.",$one,$two,$three);
			}else{
				output(" One for `#%s `3and one for `@%s`3.",$one,$two);				
			}
			if (get_module_setting("blurb") <> ""){
				output("`n`n`c%s`c`0",nltoappon(get_module_setting("blurb")));
			}
			addnav("Levers");
			addnav(array("%s",$one),"runmodule.php?module=polling&op=one");
			addnav(array("%s",$two),"runmodule.php?module=polling&op=two");
			if ($three <> "") addnav(array("%s",$three),"runmodule.php?module=polling&op=three");
			addnav("Other");
			addnav("View Results","runmodule.php?module=polling&op=view");
			if ($session['user']['superuser'] & SU_MEGAUSER){
				addnav("Other");
				addnav("Megausers Only","runmodule.php?module=polling&op=mega");
			}
		}else{
			output("You have already cast your vote.");
			addnav("Other");
			addnav("View Results","runmodule.php?module=polling&op=view");
			if ($session['user']['superuser'] & SU_MEGAUSER){
				addnav("Other");
				addnav("Megausers Only","runmodule.php?module=polling&op=mega");
			}
		}
		break;
		case "one":
			output("`3You pull the lever for `^%s`3.",$one);
			$voteone++;
			set_module_setting("voteone",$voteone);
			$hasvote++;
			set_module_pref("hasvote",$hasvote);
			break;
		case "two":
			output("`3You pull the lever for `^%s`3.",$two);
			$votetwo++;
			set_module_setting("votetwo",$votetwo);
			$hasvote++;
			set_module_pref("hasvote",$hasvote);
			break;
		case "three":
			output("`3You pull the lever for `^%s`3.",$three);
			$votethree++;
			set_module_setting("votethree",$votethree);
			$hasvote++;
			set_module_pref("hasvote",$hasvote);
			break;
		case "view":
			output("`3You notice a small booth, and see %s charts.",$ord);
			if ($three<> ""){
				output(" One for `#%s3, one for `@%s `3and one for `\$%s`3.",$one,$two,$three);
			}else{
				output(" One for `#%s `3and one for `@%s`3.",$one,$two);				
			}
			output("`n`nYou note that there %s `^%s `3vote%s for `^%s`3.",$oneare, $voteone, $ones, $one);
			output("`n`nYou note that there %s `^%s `3vote%s for `@%s`3.",$twoare, $votetwo, $twos, $two);
			if ($three <> "") output("`n`nYou note that there %s `^%s `3vote%s for `\$%s`3.",$threeare, $votethree, $threes, $three);
			break;
		case "mega":
			output("`3Do you wish to clear all Data?");
			addnav("Options");
			addnav("Wipe all Data","runmodule.php?module=polling&op=megayes");
			break;
		case "megayes":
			// The wipe link is only offered to megausers; enforce the same here.
			check_su_access(SU_MEGAUSER);
			output("`3All Polling Results Cleared.");
			output("`nAll Votes Burned.");
			output("`nPoll Is Inactive.");
			set_module_setting("voteone",0);
			set_module_setting("votetwo",0);
			set_module_setting("votethree",0);
			set_module_setting("isactive",0);
			$sql = "DELETE FROM ".db_prefix("module_userprefs")." WHERE modulename='polling' AND setting='hasvote'";
			db_query($sql);
			break;
	}
	addnav("Return");
	addnav("Return to the Grotto","superuser.php");
page_footer();
}
?>
