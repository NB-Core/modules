<?php

function buyablog_getmoduleinfo(){
	$info = array(
		"name"=>"Buy a blog",
		"author"=>"`2Oliver Brendel",
		"version"=>"1.0",
		"category"=>"Lodge",
		"download"=>"http://lotgd-downloads.com",
		"settings"=>array(
			"Buy a blog Module Settings,title",
			"cost"=>"How many donator points does the permission to blog cost?,int|400",
		),
		"requires"=>array(
		"mightyblogs"=>"1.1|MightyE Blogs Public Release, Eric Stevens (core release)",
		),
	);
	return $info;
}

function buyablog_install(){
	module_addhook("lodge");
	module_addhook("pointsdesc");
	return true;
}
function buyablog_uninstall(){
	return true;
}

function buyablog_dohook($hookname,$args){
	global $session;
	$cost = get_module_setting("cost");
	switch($hookname){
	case "pointsdesc":
		$args['count']++;
		$format = $args['format'];
		$str = translate("If you want to blog you can buy the permission for %s points");
		$str = sprintf($str, $cost);
		output($format, $str, true);
		break;
	case "lodge":
		if (!get_module_pref("canblog","mightyblogs")) addnav(array("Blog permission (%s points)", $cost),"runmodule.php?module=buyablog&op=enter");
			else
			addnav(array("Change blog signature (free)", $cost),"runmodule.php?module=buyablog&op=signature");
		break;
	}
	return $args;
}

function buyablog_run(){
	require_once("lib/sanitize.php");
	global $session;
	$op = httpget("op");
	$cost = get_module_setting("cost");
	page_header("Hunter's Lodge");
	switch ($op) {
		case "enter":
			output("`7J. C. Petersen turns to you. \"`&The permission to enter something in the common blog under your name costs %s points,`7\" he says.", $cost);
			output("\"`&Will this suit you?`7\"`n`n");
			addnav("Confirm");
			addnav("Yes", "runmodule.php?module=buyablog&op=confirm");
			addnav("No", "lodge.php");
			break;
		case "confirm":
			addnav("L?Return to the Lodge","lodge.php");
			$pointsavailable = $session['user']['donation'] -
				$session['user']['donationspent'];
			if($pointsavailable >= $cost){
				output("`7J. C. Petersen hands you out a secret permission card reading: \"Permission: Hereby you can publish your personal thoughts in the common blog under your name.\"");
				output("`n`n`7He also notes: \"`&Do not violate the rules here... if you get rude, publish things that do not belong here, the permission to blog can be `\$permanently`& revoked.`7\"");
				output("`n`n`7\"`&You can also set up a signature. If you don't want one, you can leave now.`7\" he reminds.");
				addnav("More");
				addnav("Place a signature","runmodule.php?module=buyablog&op=signature");
				set_module_pref("canblog", 1, "mightyblogs");
				$session['user']['donationspent'] += $cost;
			} else {
				output("`7J. C. Petersen looks down his nose at you.");
				output("\"`&I'm sorry, but you do not have the %s points required to be granted the permission to blog. Please return when you have enough points, thank you.`7\"", $cost);
			}
			break;
		case "signature":
			addnav("L?Return to the Lodge","lodge.php");
			output("`7J. C. Petersen turns to you. \"`&Please enter a signature down there, an existing is displayed and will be changed then,`7\" he says.`n`n");
			rawoutput("<form action='runmodule.php?module=buyablog&op=buysignature' method='post'>");
			addnav("", "runmodule.php?module=buyablog&op=buysignature");
			rawoutput("<input type='input' name='signature' value=\"".(get_module_pref("blogsig","mightyblogs"))."\"><br><br>"); //already addslashed
			rawoutput("<input type='submit' value='".translate_inline("Submit")."'>");
			rawoutput("</form>");
			break;
		case "buysignature":
			$sig=httppost('signature');
			addnav("L?Return to the Lodge","lodge.php");
			output("`7J. C. Petersen nods. \"`&So your new signature is %s`%`7\".`n`n",$sig);
			set_module_pref("blogsig",$sig,"mightyblogs");
			break;			
	}
	page_footer();
}
?>
