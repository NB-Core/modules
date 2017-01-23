<?php

function namechange_getmoduleinfo(){
	$info = array(
		"name"=>"Name Change",
		"author"=>"JT Traub, modified by `2Oliver Brendel(based on titlechange.php)",
		"version"=>"1.0",
		"download"=>"",
		"category"=>"Lodge",
		"settings"=>array(
			"Name Change Module Settings,title",
			"initialpoints"=>"How many donator points needed to get first title change?,int|5000",
			"extrapoints"=>"How many additional donator points needed for subsequent title changes?,int|500",
			"bold"=>"Allow bold?,bool|0",
			"italics"=>"Allow italics?,bool|0",
			"length"=>"Allow how many chars (remember:UTF-8 take up more bytes! And mind the table hard limit) can a title have?,int|30",
			"minlen"=>"Minimum Length of name,int|3",
		),
		"prefs"=>array(
			"Name Change User Preferences,title",
			"timespurchased"=>"How many title changes have been bought?,int|0",
		),
	);
	return $info;
}

function namechange_install(){
	module_addhook("lodge");
	module_addhook("pointsdesc");
	require_once("lib/tabledescriptor.php");
	$blocktable=array(
		'timestamp'=>array('name'=>'timestamp', 'type'=>'datetime', ),
		'newname'=>array('name'=>'newname','type'=>'varchar(255)'),
		'acctid'=>array('name'=>'acctid','type'=>'bigint'),
		'key-PRIMARY' => array('name'=>'PRIMARY', 'type'=>'primary key', 'unique'=>'1', 'columns'=>'timestamp,acctid'),
		);
	synctable(db_prefix("debuglog_names"), $blocktable, true);
	return true;
}
function namechange_uninstall(){
	return true;
}

function namechange_dohook($hookname,$args){
	global $session;
	$times = get_module_pref("timespurchased");
	$threshold = get_module_setting("initialpoints") + ($times * get_module_setting("extrapoints"));
	$need = $session['user']['donation']-$threshold;
	switch($hookname){
	case "pointsdesc":
		$args['count']++;
		$format = $args['format'];
		$str = translate("The ability to change your game name upon reaching %s and every %s points thereafter. (this doesn't use up those points) [NOTE: Your login will not change and stays unique. You may not choose a name already in use by somebody. You already chose a name %s times and need now %s or more to choose a new one!");
		$str = sprintf($str, get_module_setting("initialpoints"),
				get_module_setting("extrapoints"),$times,$threshold);
		output($format, $str, true);
		break;
	case "lodge":
		// If they have less than what they need just ignore them
		if ($need<0) {
			addnav(array("Change your name (you miss %s points)",abs($need)),"");
		} else {
			addnav("Change your name (free)","runmodule.php?module=namechange&op=namechange");
		}
		break;
	}
	return $args;
}

function namechange_run(){
	require_once("lib/sanitize.php");
	require_once("lib/names.php");
	global $session;
	$op = httpget("op");

	$namelength=get_module_setting('length');
	page_header("Hunter's Lodge");
	switch ($op) {
		case "namechange":
			output("`3`bChange Name`b`0`n`n");
			output("`7Because you have earned sufficient points, you have been granted the ability to change your name to one of your choosing.");
			output("The name must be appropriate, and the admin of the game can reset if it isn't (as well as penalize you for abusing the game).");
			output("The name may not be more than %s characters long including any characters used for colorization!.`n`n",$namelength);
			output("Note that special characters (Kanji i.e.) count to up to a length of 4 normal characters. You will have to check the preview if your name is shown correctly.`n`n");
			$otitle = $session['user']['playername'];
			if ($otitle=="`0") $otitle="";
			output("`7Your name is currently`^ ");
			rawoutput($otitle);
			output_notl("`0`n");
			output("`7which looks like %s`n`n", $otitle);
			if (httpget("err")==1) output("`\$Please enter a name.`n");
			output("`7How would you like your name to look?`n");
			rawoutput("<form action='runmodule.php?module=namechange&op=namepreview' method='POST'>");
			$n=htmlentities($otitle, ENT_COMPAT, getsetting("charset", "ISO-8859-1"));
			rawoutput("<input id='input' name='newname' width='$namelength' maxlength='$namelength' value=\"$n\">");
			rawoutput("<input type='submit' class='button' value='Preview'>");
			rawoutput("</form>");
			addnav("", "runmodule.php?module=namechange&op=namepreview");
			output("`n`nNote: This might take a while... =)");
			break;
		case "namepreview":
			$ntitle = stripslashes(rawurldecode(httppost('newname')));
			$ntitle=newline_sanitize($ntitle);

			$minlen=get_module_setting("minlen");
			if (str_replace(" ","",$ntitle)=="") {
				redirect("runmodule.php?module=namechange&op=namechange&err=1");
			}
			if (!get_module_setting("bold")) $ntitle = str_replace("`b", "", $ntitle);
			if (!get_module_setting("italics")) {
				$ntitle = str_replace("`i", "", $ntitle);
				$ntitle = str_replace("`B", "", $ntitle);
			}
			if (!getsetting('spaceinname',0)) {
				$ntitle = str_replace(" ", "", $ntitle);
			} 
			$ntitle = preg_replace("/[`][cHw]/", "", $ntitle);
			$ntitle = sanitize_html($ntitle);
			if (strlen($ntitle)>get_module_setting('length') || strlen($ntitle)<$minlen) {
				output("`nYour title may only be %s characters, but at least %s characters long.`n`n",$strlen($ntitle),$minlen);
				rawoutput("<form action='runmodule.php?module=namechange&op=namepreview' method='POST'>");
				rawoutput("<input id='input' name='newname' width='$namelength' maxlength='$namelength' value=\"$ntitle\">");
				rawoutput("<input type='submit' class='button' value='Preview'>");
				rawoutput("</form>");
				addnav("", "runmodule.php?module=namechange&op=namepreview");
				break;
			}

			$rawtitle=sanitize($ntitle);

//sanity check if the name is already taken
			$sql="SELECT playername,login FROM accounts"; //yes, many records, nasty check, but better than just another field in the db. names don't get changed frequently. if they do, you can alter this here easily.
			$result = db_query($sql);
			while ($row=db_fetch_assoc($result)) {
				$checklogin=sanitize($row['login']);
				if ($checklogin==$rawtitle && $checklogin!=$session['user']['login']) {
					output("Sorry, this name is already taken.");
					break 2;
				}
				$checkname=sanitize($row['playername']);
				if ($checkname==$rawtitle) {
					output("Sorry, this name is already taken.");
					break 2;
				}
			}

			$nname = get_player_title();
			output("`7Your new name will look like this: %s`0`n", $ntitle);
			output("`7Your entire game name will look like: %s %s`0`n`n",
					$nname,$ntitle);
			output("`7Is this how you wish it to look?");
			addnav("`bConfirm New Name`b");
			addnav("Yes", "runmodule.php?module=namechange&op=changename&newname=".rawurlencode($ntitle));
			addnav("No", "runmodule.php?module=namechange&op=namechange");
			break;
		case "changename":
			$ntitle=stripslashes(rawurldecode(httpget('newname')));
			$fromname = $session['user']['name'];
			$session['user']['playername'] = $ntitle;
			$newname = change_player_name($ntitle);
			$session['user']['name'] = $newname;
			$sql="Insert into ".db_prefix('debuglog_names')." (timestamp,acctid,newname) VALUES ('".date("Y-m-d h:m:s")."',".$session['user']['acctid'].",'".db_real_escape_string(sanitize($newname))."')";
			$result=db_query($sql);
			addnews("%s`^ has become known as %s.",$fromname,$session['user']['name']);
			debuglog("changed the player name from $fromname to ".$session['user']['name']);
			set_module_pref("timespurchased", get_module_pref("timespurchased")+1);
			output("Your new name has been set.");
			modulehook("namechange", array());
			break;
	}
	addnav("Navigation");
	addnav("L?Return to the Lodge","lodge.php");
	page_footer();
}
?>
