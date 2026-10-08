<?php

function addimages_getmoduleinfo(){
	$info = array(
		"name"=>"Add Images Handler only",
		"version"=>"2.0",
		"author"=>"`2Oliver Brendel ",
		"category"=>"Images",
		"download"=>"",
		"settings"=>array(
			"Add Images Handler,title",
			"This module is only a dummy to host image handling centrally!,note",
			"restrict_size"=>"Restrict pic sizes for generic output,bool|1",
			"maxwidth"=>"Max Width for images,int|600",
			"maxheight"=>"Max Height for images,int|400",
			"thumbnail_path"=>"Path to the thumbnail directory,|thumbs",
			),
		"prefs"=>array(
			"Add Images Module User Preferences,title",
			"user_addimages"=>"Display Ingame Images?,bool|1",
		),
	);
	return $info;
}

function addimages_install(){
	return true;
}

function addimages_uninstall(){
	return true;
}

function addimages_dohook($hookname,$args){
	return $args;
}

?>
