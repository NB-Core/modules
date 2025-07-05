<?php

// Write a small lotgd module with all functions that hooks into the lodge and displays a color table.

function lodge_colortable_getmoduleinfo(){
	$info = array(
		"name"=>"Lodge Color Table",
		"version"=>"1.0",
		"author"=>"copied from Darkhorse Tavern",
		"category"=>"Lodge",
		"download"=>"",
		"settings"=>array(
			"Color Table,title",
		),
	);
	return $info;
}

function lodge_colortable_install(){
	module_addhook("lodge");
	return true;
}

function lodge_colortable_uninstall(){
	return true;
}

function lodge_colortable_dohook($hookname,$args){
	global $session;
	switch($hookname){
	case "lodge":
		addnav("Colors");
		addnav("Color Table","runmodule.php?module=lodge_colortable");
		addnav("General");
		break;
	}
	return $args;
}

function lodge_colortable_run(){
	global $session, $output;
	$from = "runmodule.php?module=lodge_colortable";
	page_header("Color Table");
	output("`b`c`@Color Table`0`c`b`n");
	$colors = $output->get_colormap_escaped_array();
	rawoutput("<table><tr><td>");
	output("`2Color Code");
	rawoutput("</td><td>");
	output("`@Example");
	rawoutput("</td></tr>");	
	$i=0;
	foreach ($colors as $code) {
		if ($i==0) rawoutput("<tr>");
		output_notl("<td>&#0096;".stripslashes($code)."</td><td>`".stripslashes($code)."This is the example</td>",true);
		if ($i==5) rawoutput("</tr>"); 
		$i++;
		$i=$i%5;
	}
	if ($i!=0) rawoutput("</tr>"); 
	rawoutput("</tr></table>");
//		output("`1&#0096;1 `2&#0096;2 `3&#0096;3 `4&#0096;4 `5&#0096;5 `6&#0096;6 `7&#0096;7 `n`!&#0096;! `@&#0096;@ `#&#0096;# `\$&#0096;\$ `%&#0096;% `^&#0096;^ `&&#0096;& `n `)&#0096;) `q&#0096;q `Q&#0096;Q `n`% got it?`0\"`n  You can practice below:", true);
	rawoutput("<form action=\"".$from."\" method='POST'>");
	$testtext = httppost('testtext');
	$try = translate_inline("Try");
	rawoutput("<input name='testtext' id='testtext'><input type='submit' class='button' value='$try'></form>");
	addnav("",$from);
	rawoutput("<script language='JavaScript'>document.getElementById('testtext').focus();</script>");
	if ($testtext) {
		output("`0You entered %s`n", prevent_colors(HTMLEntities($testtext, ENT_COMPAT, getsetting("charset", "ISO-8859-1"))),true);
		output("It looks like %s`n", $testtext);
	}
	output("`0`n`nThese colors can be used in your name, and in any conversations you have.");
	addnav("Navigation");
	addnav("Back to the lodge","lodge.php");
	page_footer();
}
