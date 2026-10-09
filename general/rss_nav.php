<?php
// addnews ready
// mail ready
// translator ready

// ver 1.0 by Eric Stevens
// Original release

// ver 1.1 by Catscradler
// added explanation page
// moved feed links to the explanation page (people without readers didn't know why they were getting pages of RSS code)

// ver 1.2 by Oliver Brendel
// reworked for use with +nb Versions
// smaller code fixes

function rss_nav_getmoduleinfo(){
	$info = array(
		"name"=>"RSS News Feeds Display Nav",
		"version"=>"1.2",
		"author"=>"Eric Stevens, modified by Oliver Brendel",
		"category"=>"General",
		"download"=>"http://lotgd-downloads.com",
		"requires"=>array(
			"rss"=>"Rss Module modified by Oliver Brendel|1.2",
		),
	);
	return $info;
}

function rss_nav_install(){
	module_addhook("village");
	return true;
}
function rss_nav_uninstall(){
	return true;
}
function rss_nav_dohook($hookname,$args){
	switch($hookname){
	case "village":
		if (get_module_setting("show_on_about","rss")){
			addnav("Other");
			addnav("RSS News Feeds","runmodule.php?module=rss_nav&op=describe");
		}
		break;
	}
	return $args;
}
function rss_nav_run(){

	if (httpget("op")=="describe"){
		global $session;
		page_header("RSS Feed Information");
		output("This site offers RSS news feeds for periodically updated information about various aspects of the game.");
		output("Click %shere%s for more information about the RSS format.`n`n","<a href='http://www.google.com/search?q=rss+information' target='_blank'>", "</a>", true);

		output("Feeds offered on this site:`n");
		$format="`l&#149;`k %s`n";
		addnav("Navigation");
		if ($session['user']['loggedin']) {
			villagenav();
		}else{
		    addnav("Login Page","index.php");
		}
		addnav("Get RSS News Feeds");
		if (get_module_setting("do_news","rss")){
			addnav("Daily News","runmodule.php?module=rss&feed=news",false,true);
			output($format,"Daily News",true);
		}
		if (get_module_setting("do_online","rss")){
			addnav("Who's Online","runmodule.php?module=rss&feed=online",false,true);
			output($format,"Who's Online",true);
		}
		if (get_module_setting("do_motd","rss")){
			addnav("MoTD","runmodule.php?module=rss&feed=motd",false,true);
			output($format,"Message of the Day (MoTD)",true);
		}

		page_footer();
		return;
	}
	
}
function rss_nav_xmlencode($input){
	require_once("lib/sanitize.php");
	return str_replace(array("&","<",">"),array("&amp;","&lt;","&gt;"),full_sanitize($input));
}
?>
