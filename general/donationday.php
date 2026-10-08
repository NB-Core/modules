<?php
/*just a simple mod to give players donation points since I do not want them to donate to me,
visit dragonprime.net for a German translation*/

function donationday_getmoduleinfo(){
	$infos = array(
		"name"=>"Donation Day",
		"version"=>"1.0",
		"author"=>"Oliver Brendel",
		"category"=>"General",
		"download"=>"http://lotgd-downloads.com",
		"description"=>"Get Donationpoints on a new day and after a dk",
		"settings"=>array(
			"Donation Day,title",
			"donationdk"=>"Amount of donation points for a DK,int|20",
			"donationnewday"=>"Amount of donation points each newday a char gets,int|1",
		),
	);
	return $infos;
}
function donationday_install(){
	module_addhook("newday");
	module_addhook("dragonkill");	
	return true;
}
function donationday_uninstall(){
	return true;
}
function donationday_dohook($hookname,$args)
{
	global $session;
	$donationdk=get_module_setting("donationdk");
	$donationnewday=get_module_setting("donationnewday");
	switch($hookname){
	case "dragonkill":
		output("`^`nBecause you suceeded in killing the `@Green Dragon`^, you are awarded %s donation points",$donationdk);
		$session['user']['donation']+=$donationdk;
		break;
	case "newday":
		$session['user']['donation']+=$donationnewday;
		break;
	}
	return $args;
}

function donationday_run(){
return true;
}

?>
