<?php
function pvpquotes_getmoduleinfo(){
	$info = array(
		"name"=>"PvP Quotes",
		"version"=>"1.0",
		"author"=>"`2Oliver Brendel `@inspired by `tXChrisX",
		"category"=>"PVP",
		"download"=>"",
		"prefs"=>array(
			"PvP Quotes Preferences,title",
			"user_win"=>"Give quotes you want to have displayed to the enemy if you attack somebody and defeat him (quotes separated by ; please),text|",
			"user_defeat"=>"Give quotes you want to have displayed to the enemy if somebody attacks you and you defeat him (quotes separated by ; please),text|",
		
			),
	);
	return $info;
}

function pvpquotes_install(){
	module_addhook("pvpwin");
	module_addhook("pvploss");
	return true;
}

function pvpquotes_uninstall(){
	return true;
}

function pvpquotes_dohook($hookname,$args){
	global $session;
	switch ($hookname) {
		case "pvpwin":
			$message=stripslashes(get_module_pref("user_win"));
			$message=explode(";",$message);
			$message=str_replace("`i","",$message);
			$message=str_replace("`b","",$message);
			$message=str_replace("`c","",$message);
			//not used anymore
			//if ($message) $args['pvpmsgadd']=sprintf(addslashes("`n`lThe last words you hear are: \"`\$%s`l\"`2`n`n"),addslashes($message[e_rand(0,count($message)-1)]));
			break;
		case "pvploss":
			$message=stripslashes(get_module_pref("user_defeat","pvpquotes",$args['badguy']['acctid']));
			$message=explode(";",$message);
			$show=$message[e_rand(0,count($message)-1)];
			$show=str_replace("`i","",$show);
			$show=str_replace("`c","",$show);
			$show=str_replace("`b","",$show);
			if ($show) output_notl("`lThe last words you hear are: \"`\$%s`l\"`2`n`n",$show);
			break;

	break;
	}
	return $args;
}

function pvpquotes_run(){
}


?>
