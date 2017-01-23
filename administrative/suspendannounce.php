<?php

function suspendannounce_getmoduleinfo(){
	$info = array(
		"name"=>"Suspension Announce",
		"version"=>"1.0",
		"author"=>"`2Oliver Brendel based on Server Maintenance Suspension by Eric Stevens",
		"category"=>"Administrative",
		"download"=>"",
		"settings"=>array(
				"Settings for the Announce,title",
				"reason"=>"Reason for the announce,text",
				),
	);
	return $info;
}

function suspendannounce_install(){
	module_addhook("everyhit");
	return true;
}

function suspendannounce_uninstall(){
	return true;
}

function suspendannounce_dohook($hookname,$args){
	switch($hookname){
	case "everyhit":
		$permithits = array(
			"home.php"=>true,
			"index.php"=>true,
			"login.php"=>true,
			"installer.php"=>true,
		);
		global $session;
		$reason = get_module_setting('reason');
		$script = substr($_SERVER['SCRIPT_NAME'],strrpos($_SERVER['SCRIPT_NAME'],"/")+1);
		if (isset($permithits[$script])
			|| $session['user']['superuser']&SU_MANAGE_MODULES
			|| $session['user']['superuser']&SU_MEGAUSER 
			){
			$ha='h1';output_notl("`\$");
			rawoutput("<$ha>");
			output("The server will be suspended for maintenance shortly, please log out immediately at a location you prefer.");
			output_notl("`nReason: %s",$reason);
			rawoutput("</$ha>");
			//users get sent to the village or shades depending on their alive
			//status if they try to navigate.
			//This is actually a bug, but I haven't bothered to track it down,
			//and it seems somewhat reasonable given we warn them that it is
			//coming with a MOTD.
			if ($session['user']['loggedin']) {
				output("`\$This means YOU.");
				output("Get your upgrades done and deactivate the server suspension module you fool!`n`n");
				output("Users who attempt to navigate during the outtage will be returned to the village or shades, depending on their alive status.`n`n");
			}
		}else{
			output_notl("`\$");
			$ha='h1';
			rawoutput("<$ha>");
			output("The server will be suspended for maintenance shortly, please log out immediately at a location you prefer.");
			output_notl("`nReason: %s",$reason);
			rawoutput("</$ha>");
			}
		break;
	}
	return $args;
}
?>
