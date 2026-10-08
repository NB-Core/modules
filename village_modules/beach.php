<?php
if (isset($_GET['op']) && $_GET['op']=="download"){ // this offers the module on every server for download
 $dl=join("",file("beach.php"));
 echo $dl;
}
function beach_getmoduleinfo(){
	$info = array(
		"name"=>"Beach Resort",
		"author"=>"eph, based on Dark Alley by Spider",
		"version"=>"1.0",
		"category"=>"Village|Beach",
		"download"=>"modules/beach.php?op=download",
		"settings"=>array(
			"specialchance"=>"Chance for Something Special at the beach,range,0,100,1|15",
			"beachloc"=>"In what city is the beach?,location|".getsetting("villagename", LOCATION_FIELDS)
		)
	);
	return $info;
}

function beach_install(){
	module_addhook("village");
	module_addhook("changesetting");
	return true;
}

function beach_uninstall(){
	return true;
}

function beach_dohook($hookname, $args){
	global $session;
	switch($hookname){
	case "changesetting":
		if ($args['setting'] == "villagename") {
			if ($args['old'] == get_module_setting("beachloc")) {
				set_module_setting("beachloc", $args['new']);
			}
		}
		break;
	case "village":
		if ($session['user']['location'] == get_module_setting("beachloc")){
			addnav($args["gatenav"]);
			addnav("Beach Resort", "runmodule.php?module=beach");
		}
		break;
	}
	return $args;
}

function beach_run(){
	global $session;
	require_once("lib/villagenav.php");
	require_once("lib/events.php");
	require_once("lib/http.php");
	require_once("lib/commentary.php");
	page_header("Beach Resort");

	$op = httpget('op');
	$com = httpget('comscroll');
	if (!$op && $com=="") {
		if (module_events("beach", get_module_setting("specialchance", "beach")) != 0) {
			if (checknavs()) {
				page_footer();
			} else {
				$session['user']['specialinc'] = "";
				$session['user']['specialmisc'] = "";
				$op = "";
				httpset("op", "");
			}
		}
	}

	addnav("At the Beach");
	addnav("Other");
	villagenav();
	modulehook("beach");

	output("`^`c`bThe Beach Resort`b`c`n");
	if (file_exists("modules/beach/beach.jpg")) rawoutput("<center><img src='modules/beach/beach.jpg' align='center'></center><br>");
	output("`^White sand which feels pleasantly warm beneath your naked feet. Crystal blue water, reflecting the sunlight. Palm trees that move gently in the slight breeze. This place must be heaven.`n`n");
	output("Many tourists from all over the land have spread the blankets here for a sunbath or a picknick. Some children play in the shallow waters with an inflated ball, others are busy building sandcastles. All around you is the hustle and bustle of a lively holiday resort.`n`n");
	modulehook("beach-desc");
	module_display_events("beach", "runmodule.php?module=beach");
	addcommentary();
	viewcommentary("beach-site","Speak",20,"says");
	page_footer();
}

?>
