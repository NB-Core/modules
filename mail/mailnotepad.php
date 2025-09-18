<?php

if (!defined("OVERRIDE_FORCED_NAV")) define("OVERRIDE_FORCED_NAV",true);


function mailnotepad_getmoduleinfo(){
	$info = array(
		"name"=>"Mailnotepad",
		"override_forced_nav"=>true,
		"version"=>"1.01",
		"author"=>"`2Oliver Brendel`0",
		"category"=>"Mail",
		"download"=>"",
		"description"=>"Adds an mailnotepad to the users YOM.",
		"settings"=>array(
			"Mailnotepad - Preferences,title",

			),
		"prefs"=>array(
			"mailnotepad,title",
			"comment_0"=>"Notes,text|",
			"comment_1"=>"Notes,text|",
			),
		);
	return $info;
}

function mailnotepad_install(){
	module_addhook("mailfunctions");
	return true;
}

function mailnotepad_uninstall() {
	return true;
}


function mailnotepad_dohook($hookname, $args){
	global $session;
	switch ($hookname)	{
		case "mailfunctions":
			$mailnotepad = translate_inline("Mail Notepad");
			array_push($args, array("runmodule.php?module=mailnotepad", $mailnotepad));
			addnav ("","runmodule.php?module=mailnotepad");

			break;

		default:

			break;
	}
	return $args;
}

function mailnotepad_run(){
	global $session;
	$op=httpget('op');
	$id = httpget('id');
	$comments=2; //+1=count
	popup_header("Ye Olde Poste Office");
	rawoutput("<table width='50%' border='0' cellpadding='0' cellspacing='2'>");
	rawoutput("<tr><td>");
	$t = translate_inline("Back to the Ye Olde Poste Office");
	$o = translate_inline("Back to the Mailnotepad");
	rawoutput("<a href='mail.php'>$t</a></td><td>");
	rawoutput("<a href='runmodule.php?module=mailnotepad'>$o</a>");
	addnav("","runmodule.php?module=mailnotepad");
	rawoutput("</td></tr></table>");
	output_notl("`n`n");
	$atable= db_prefix("accounts");
	require_once("lib/showform.php");
	$layout=array(
		"Mail Notepad #1,title",
		"comment_0"=>"Notes 1,textarearesizeable",
		"Mail Notepad #2,title",
		"comment_1"=>"Notes 2,textarearesizeable",
		);
	$values=array(
		"comment_0"=>stripslashes(get_module_pref('comment_0','mailnotepad')),
		"comment_1"=>stripslashes(get_module_pref('comment_1','mailnotepad')),
		);
	switch ($op) {
		default:
			output("`b`iMail Notepad`i`b");
			if (isset($session['message'])) {
				output($session['message']);
			}
			switch (httpget('subop')){
				case "save":
					for ($i=0;$i<$comments;$i++) {
						$comment = stripslashes(httppost('comment_'.$i));
						$values['comment_'.$i]=$comment;
						set_module_pref('comment_'.$i,$comment,'mailnotepad');
					}
					output("`\$Saved!`n`n");
					break;
				default:
					output_notl("`n`n");
			
			}
			$session['message']="";
			rawoutput("<form action='runmodule.php?module=mailnotepad&subop=save' method='POST'>");
			$info=showform($layout,$values);
			rawoutput("</form>");
			break;
		}
popup_footer();
}

?>
