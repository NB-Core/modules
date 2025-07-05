<?php

function mountname_getmoduleinfo(){
	$info = array(
		"name"=>"Mount Naming",
		"author"=>"Chris Vorndran",
		"version"=>"1.0",
		"category"=>"Lodge",
		"download"=>"http://dragonprime.net/users/Sichae/mountname.zip",
		"vertxtloc"=>"http://dragonprime.net/users/Sichae/",
		"description"=>"This module allows a user to prepend a name for their mount, by spending donation points.",
		"settings"=>array(
			"Mount Naming Settings,title",
			"forecost"=>"Cost of initial Mount Name change,int|200",
			"aftercost"=>"Cost of any Mount Name changes after the initial,int|50",
			"maxchars"=>"Maximum Characters,int|25",
		),
		"prefs"=>array(
			"initial"=>"Has initial Mount Name been purchased,bool|0",
			"name"=>"User's Mount's Name,text|",
			"Mount Settings,title",
			"user_showmounttype"=>"Do you want to display the mount type in the name?,bool|1",
			"Example: NAME the Kyuubi with 'Yes', NAME with 'No',note",			
		),
	);
	return $info;
}
function mountname_install(){
	module_addhook("lodge");
	module_addhook("bio-mount");
	module_addhook("pointsdesc");
	module_addhook("charstats");
	return true;
}
function mountname_uninstall(){
	return true;
}
function mountname_dohook($hookname,$args){
	global $session;
	$name = get_module_pref("name");
	$showmounttype = get_module_pref('user_showmounttype');
	$mount = array();
	switch ($hookname){
		case "lodge":
			if (get_module_pref("initial")){
				$cost = get_module_setting("aftercost");
			}else{
				$cost = get_module_setting("forecost");
			}
			addnav("Use Points");
			$pav = ($session['user']['donation'] - $session['user']['donationspent']);
			if ($pav >= $cost && $session['user']['hashorse'] != 0) addnav(array("Name your Mount - (%s points)",$cost),"runmodule.php?module=mountname&op=enter");
				elseif ($session['user']['hashorse'] == 0)
				addnav(array("Name your Mount - (%s points but you have no mount!)",$cost),"");
				else
				addnav(array("Name your Mount - (%s points you do not have!)",$cost),"");
			break;
		case "pointsdesc":
			$args['count']++;
			$format = $args['format'];
			$str = translate("The ability to choose a custom mount title for %s and %s points for each change thereafter.");
			$str = sprintf($str, get_module_setting("forecost"), get_module_setting("aftercost"));
			output($format, $str, true);
			break;
		case "bio-mount":
			$name =stripslashes( get_module_pref("name",false,$args['acctid']));
			$the = translate_inline("the");
			if ($name <> ""){
				if (isset($args['mountname'])) {
				$args['basename']=$args['mountname'];
					if ($name > "") {
						if ($showmounttype) {
							$args['mountname']=$name." `&".$the." ".$args['basename'] . "`0";
						} else {
							$args['mountname']=$name." `&(".$args['basename'].")`0";
						}
						$args['newname']="$name`0";
					}
				}
			}
			break;
		case "charstats":
			if ($session['user']['hashorse'] != "" && $name <> ""){
				$id = $session['user']['hashorse'];
				$name =stripslashes(get_module_pref("name"));
				$title = "Creature";
				$head = "Equipment Info";
				$sql = "SELECT mountname FROM ".db_prefix("mounts")." WHERE mountid=$id";
				$res = db_query($sql);
				if (db_num_rows($res)>0) {
					$row = db_fetch_assoc($res);
					$mname = $row['mountname'];
					$nomen = translate_inline("the");
					if ($showmounttype) {
						$fname = sprintf("%s `&%s %s`0",$name,$nomen,$mname);
					} else {
						$fname = sprintf("%s`0",$name,$nomen,$mname);
					}
					setcharstat($head,$title,$fname);
				}
			}
			break;
		}
	return $args;
}
function mountname_run(){
	global $session;
	$op = httpget('op');
	$n = httppost('name');
	if (get_module_pref("initial")){
		$cost = get_module_setting("aftercost");
	}else{
		$cost = get_module_setting("forecost");
	}
	$name = get_module_pref("name");
	page_header("JCP's Hunter's Lodge");

	switch ($op){
		case "enter":
			output("`)JCP takes his time, in walking over to the fire, next to which you are standing.");
			output("He smiles wryly, \"`\$So, I hear that you are in the market for a Mount Name.");
			output("I hope you realize, that this will cost `^%s `\$Points.\"",$cost);
			addnav("Write Name","runmodule.php?module=mountname&op=write");
			break;
		case "write":
			if ($n == NULL){
				output("`)JCP pulls out a small sheet of paper, and is ready to copy down your mount name.");
				output("\"`\$Make sure that your name is under %s Characters.`)\"",get_module_setting("maxchars"));
				rawoutput("<form action='runmodule.php?module=mountname&op=write' method='POST'>");
				output("`)Please choose a name for your mount.`0");
				rawoutput("<input id='input' name='name' size='25' value=\"".stripslashes($name)."\"> <input type='submit' class='button' value='".translate_inline("Submit")."'>");
				rawoutput("</form>");
			}elseif (mb_strlen($n) > get_module_setting("maxchars")){
				output("`)JCP looks at you, and shakes his head.");
				output("\"`\$I am sorry, but you have gone over the limit of %s Characters.",get_module_setting("maxchars"));
				rawoutput("<form action='runmodule.php?module=mountname&op=write' method='POST'>");
				output("`)Please choose a name for your mount.`0");
				rawoutput("<input id='input' name='name' size='25' value='$n'> <input type='submit' class='button' value='".translate_inline("Submit")."'>");
				rawoutput("</form>");
			}else{
				output("`)JCP transcribes the name, and then sets to work.");
				output("You turn around, and see your mount has a new persona to it.");
				set_module_pref("name",$n);
				output("`n`n%s `^walks over to you and nuzzles your hand.",$n);
				if (get_module_pref("initial") == 0) set_module_pref("initial",1);
				$session['user']['donationspent']+=$cost;
			}
			addnav("","runmodule.php?module=mountname&op=write");
			break;
	}
	addnav("Return to the Lodge","lodge.php");
	page_footer();
}
?>
