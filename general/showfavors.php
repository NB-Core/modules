<?php
/*
Meet the showfavors in the woods...

*/
function showfavors_getmoduleinfo()
{
	$info = array(
	"name"=>"Show Favours",
	"author"=>"`2Oliver Brendel",
	"version"=>"1.0",
	"category"=>"Stats",
	"download"=>"",
	);
	return $info;
}
function showfavors_install()
{
	module_addhook_priority("gypsy",80);
	return true;
}
function showfavors_uninstall()
{
	return true;
}
function showfavors_dohook($hookname,$args)
{
	global $session;
	switch($hookname) {
		case "gypsy":
			output("`5You also have `%%s`5 favours with %s`5.",$session['user']['deathpower'],getsetting('deathoverlord','`$Ramius'));
		break;
	}
	return $args;
}

function showfavors_run(){
}

?>
