<?php

function shadestalk_getmoduleinfo(){
    $info = array(
        "name"=>"Another Place In The Shades",
        "version"=>"1.0",
        "author"=>"`2Oliver Brendel",
        "category"=>"Shades",
        "download"=>"",
        "settings"=>array(
            "Shadestalk - Settings,title",
			
			),        
    );
    return $info;
}

function shadestalk_install(){
	module_addhook("shades");
    return true;
}

function shadestalk_uninstall(){
    return true;
}

function shadestalk_dohook($hookname,$args){
    global $session;
    switch($hookname){
		case "shades":
			addnav("Places");
			addnav("The Mourning Cemetery","runmodule.php?module=shadestalk&op=talk");
			break;
		}
    return $args;
}

function shadestalk_run () {
	global $session;
	$op = httpget("op");
	require_once("lib/commentary.php");
	addcommentary();	
	page_header("The Mourning Cemetery");
	output("`b`i`c`)The `~M`)ourning `~C`)emetery`c`i`b`n");
	output("`)As you wander around, mad with despair, you see many other souls that have more to mourn about their lost life than the vast expanse of the shades can bear.`n`n");
	output("Many have suffered greatly and want only to share their crazy thoughts about their end with others to ease their pain a little bit.");
	output("`nOther fellow deceased shinobi gather around you...`n`n");
	addnav("Navigation");
	addnav("Return to the Shades","shades.php");
	commentdisplay("`n`n`@Mourn about your lost life....`n","shadestalk-mourning","",30,"mourns");
	page_footer();
}
?>
