<?php


function pvpavatars_getmoduleinfo(){
	$info = array(
			"name"=>"Avatars in PvP",
			"version"=>"1.0",
			"author"=>"`2Oliver Brendel",
			"category"=>"Clan",
			"download"=>"",
			"settings"=>array(
				"Clan Reqs - Settings,title",
				"mindks"=>"How many dks do founders need, range,1,30,1|8",
				),
			"requires"=>array(
				"avatar"=>"1.01|JT Traub`n`&modified by `2Oliver Brendel Core >1.1.1",
				),
		     );
	return $info;
}

function pvpavatars_install(){
	module_addhook("header-pvp");
	return true;
}

function pvpavatars_uninstall(){
	return true;
}

function pvpavatars_dohook($hookname,$args){
	global $session;
	switch($hookname){
		case "header-pvp":
			if (httpget('act')!='attack' && httpget('op')!='fight') break;
			$badguy = unserialize($session['user']['badguy']);
			if ($badguy===false) break;
			$acctid=0;
			if (!array_key_exists('enemies',$badguy)) {
				$acctid=$badguy['acctid'];
			} else { //v 1.1.1 and up
				$enemy=array_shift($badguy['enemies']);
				if (isset($enemy['acctid']))
					$acctid=$enemy['acctid'];
			}
			if ($acctid>0) {
				$image=pvpavatars_picture($acctid);
				output_notl("`c$image`c`n`n",true);
			}
			break;
	}
	return $args;
}

function pvpavatars_run () {

}

function pvpavatars_picture($user) {
	require_once("avatar/func.php");
	if (get_module_pref("validated","avatar",$user) && get_module_pref("avatar","avatar",$user)!='') {
		$url = get_module_pref("avatar","avatar",$user);
		$image = "";
		try {
			
			$check = addimage_checkRemoteFile($url);
			if ($check['exists']) {
				if (get_module_setting("restrictsize")) {
					$maxwidth = get_module_setting("maxwidth");
					$maxheight = get_module_setting("maxheight");
					$image = addimage_getimage($url,"Preview",true,$maxwidth,$maxheight);
				}
			} else {
				// bad file
				$image="<img align='left' src='".$url."' /><p>".$check['code']." -- ".$check['description']."</p>";
			}
		} catch (Throwable $e) {
			output("Sorry, something went wrong getting that pic: %s", $e->getMessage());
			return '';
		}
		return $image;
	} else return '';
}
?>
