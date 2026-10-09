<?php

function gardensroster_getmoduleinfo(){
	$info = array(
		"name"=>"Gardens and Shades Roster",
		"author"=>"Chris Vorndran modified by Oliver Brendel",
		"category"=>"Gardens",
		"version"=>"1.0",
		"download"=>"http://dragonprime.net/user/Sichae/gardensroster.zip",
		"vertxtloc"=>"http://dragonprime.net/users/Sichae/",
		"prefs"=>array(
			"location"=>"Is this user somewhere?,enum,0,nowhere,1,gardens,2,shades|0",
		),
	);
	return $info;
}
function gardensroster_install(){
	module_addhook("gardens");
	module_addhook("village");
	module_addhook("shades");
	//module_addhook("footer-runmodule");
	return true;
}
function gardensroster_uninstall(){
	return true;
}
function gardensroster_dohook($hookname,$args){
	global $session;
	switch ($hookname){
		case "village":
		case "footer-runmodule":
			set_module_pref("location",0);
			break;
		case "gardens":
			set_module_pref("location",1);
			$sql = "SELECT name FROM ".db_prefix("accounts")." INNER JOIN ".db_prefix("module_userprefs")." ON acctid=userid WHERE modulename='gardensroster' AND setting='location' AND value=1 AND loggedin=1 AND laston > '".date("Y-m-d H:i:s",strtotime("-".getsetting("LOGINTIMEOUT",300)." seconds"))."'";
			$res = db_query_cached($sql,"gardensroster",30);
			$parsing = array();
			while ($row = db_fetch_assoc($res)) {
				$parsing[] = $row['name'];
				//if ($name == $session['user']['name']) $name = translate_inline("Yourself");
				//$parsing = sprintf("%s`@%s%s", $parsing, $parsing == "" ? "" : ", ",$name);
			}
			if ($parsing != array()) {
				output("`@Looking around the Gardens you see... ");
				$parsing=implode("`@,",$parsing);
				$parsing=str_replace($session['user']['name'],translate_inline("Yourself"),$parsing);
				output_notl($parsing."`n`n");
			}
			break;
		case "shades":
			set_module_pref("location",2);
			$sql = "SELECT name FROM ".db_prefix("accounts")." INNER JOIN ".db_prefix("module_userprefs")." ON acctid=userid WHERE modulename='gardensroster' AND setting='location' AND value=2 AND loggedin=1 AND laston > '".date("Y-m-d H:i:s",strtotime("-".getsetting("LOGINTIMEOUT",300)." seconds"))."'";
			$res = db_query_cached($sql,"shadesroster",30);
			$parsing = array();
			while ($row = db_fetch_assoc($res)) {
				$parsing[] = $row['name'];
				//if ($name == $session['user']['name']) $name = translate_inline("Yourself");
				//$parsing = sprintf("%s`@%s%s", $parsing, $parsing == "" ? "" : ", ",$name);
			}
			if ($parsing != array()) {
				output("`n`)Looking around the shadows you see... ");
				$parsing=implode("`),",$parsing);
				$parsing=str_replace($session['user']['name'],translate_inline("Yourself"),$parsing);
				output_notl($parsing."`n`n");
			}
			break;
		}
	return $args;
}
?>
