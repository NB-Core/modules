<?php
function adminpvp_getmoduleinfo(){
	$info = array(
		"name"=>"Admin PvP Immunity",
		"version"=>"1.0",
		"author"=>"`2Oliver Brendel",
		"category"=>"PVP",
		"download"=>"",
		 "settings"=>array(
					"PvP Admin Immunity - Preferences, title",
					"who"=>"Enter here seperated by comma the acctid of the admins, text|1,7",
					),
	);
	return $info;
}

function adminpvp_install(){
	module_addhook_priority("pvpmodifytargets", 100);
	return true;
}

function adminpvp_uninstall(){
	return true;
}

function adminpvp_dohook($hookname,$args){
	global $session;
	switch ($hookname) {
	case "pvpmodifytargets":
		$who=explode(",",get_module_setting('who'));
		$alt=array();
		$sql="SELECT acctid FROM ".db_prefix('accounts')." WHERE uniqueid='".$session['user']['uniqueid']."';";
		$result=db_query($sql);
		while ($row=db_fetch_assoc($result)) {
			$alt[]=$row['acctid'];
		}
		foreach ($args as $key=>$row) {
			if (in_array($row['acctid'],$who)) {
				$args[$key]['invalid'] = 1;
				if ($session['user']['location']!=$row['location']) continue;
				output("`vYou see %s`v sleeping here, laid down in a bed of crimson roses, surrounded by the most delicate women you have ever seen.`nYou are overwhelmed and helpless... you cannot attack...`n`n",$row['name']);
				output_notl("`n`0");
			}
			//block alt.
			if (in_array($row['acctid'],$alt)) {
				//is alt
				$args[$key]['invalid'] = 1;
				if ($session['user']['location']!=$row['location']) continue;
				output("`vYou see %s`v sleeping here, unfortunately you cannot attack due to restrictions about multichars.`n`n",$row['name']);
				output_notl("`n`0");				
			}
		}

		
	break;
	}
	return $args;
}

function adminpvp_run(){
}


?>