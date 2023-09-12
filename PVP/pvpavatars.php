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
		$picname = get_module_pref("avatar","avatar",$user);
		try {
			$check = avatar_checkRemoteFile($picname);
			if (!$check['exists']) {
				//set_module_pref('validated',0,'avatar',$user);
				return "<p>Error while fetching picture<br/>".$check['code']." -- ".$check['description']."!";
			}
			$imageBlob = file_get_contents($picname);
			$imagick = new Imagick();
			$imagick->readImageBlob($imageBlob);
			$pic_height = $imagick->getImageHeight();
			$pic_width = $imagick->getImageWidth();
		} catch (Throwable $e) {
			output("Sorry, something went wrong getting that pic: %s", $e->getMessage());
			return '';
		}
		$image="<img align='center' src='".get_module_pref("avatar","avatar",$user)."' ";
		if (get_module_setting("restrictsize","avatar")) {
			//stripped lines from Anpera's avatar module =)
			$maxwidth = get_module_setting("maxwidth","avatar");
			$maxheight = get_module_setting("maxheight","avatar");
			if ($pic_width > $maxwidth) $image.=" width=\"$maxwidth\" ";
			if ($pic_height > $maxheight) $image.=" height=\"$maxheight\" ";
		}
		$image.=">";
		return $image;
	} else return '';
}
?>
