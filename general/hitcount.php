<?php

function hitcount_getmoduleinfo(){
	$info = array(
		"name"=>"Hit Counter",
		"author"=>"Chris Vorndran",
		"version"=>"0.4",
		"category"=>"General",
		"download"=>"http://dragonprime.net/users/Sichae/hitcount.zip",
		"vertxtloc"=>"http://dragonprime.net/users/Sichae/",
		"description"=>"This module will display a small hitcounter on the Index Page.",
		"settings"=>array(
			"Hit Counter Settings,title",
			"count"=>"Current Hit Count,int|100",
			"hook"=>"Where do you wish for this to hook,enum,1,Above Login (Near Statue),2,Below Display Selector|2",
		),
		);
	return $info;
}
function hitcount_install(){
	module_addhook("index");
	module_addhook("footer-home");
	return true;
}
function hitcount_uninstall(){
	return true;
}
function hitcount_dohook($hookname,$args){
	global $session;
	switch ($hookname){
			case "index":
			if (get_module_setting("hook") == 2){
				break;
			}else{
				$count = get_module_setting("count");
				increment_module_setting("count",1,"hitcount");
				rawoutput("<big>");
				output("`n`bCurrent Hit Count: `^%s`n`n`b`0",number_format($count));
				rawoutput("</big>");
			}
				break;

			case "footer-home":
			if (get_module_setting("hook") == 1){
				break;
			}else{
				$count = get_module_setting("count");
				increment_module_setting("count",1,"hitcount");
				rawoutput("<big>");
				output("`c`bCurrent Hit Count: `^%s`n`c`b`0",number_format($count));
				rawoutput("</big>");
			}
				break;
		}
	return $args;
}
function hitcount_run(){
}
?>