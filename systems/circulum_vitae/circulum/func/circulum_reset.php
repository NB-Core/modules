<?php
//some parts from dragon.php
function circulum_do_reset() {
	global $session;
	restore_buff_fields();
	require_once("modules/circulum/func/circulum_nochange.php");
	modulehook("circulum-prereset",array("acctid"=>$session['user']['acctid']));
	
	$nochange=circulum_get_account_nochanges();
	$sql = "DESCRIBE " . db_prefix('accounts');
	$result = db_query($sql);
	//debug($nochange);
	$skip=array("acctid","email","title","login","password","translatorlanguages");
	while ($row = db_fetch_assoc($result)) {
		//debug($row);
		if (in_array($row['Field'],$skip)) continue;
		if (array_key_exists($row['Field'],$nochange) && $nochange[$row['Field']]) {
			//don't change it
			//debug($row['Field']." not reset");
		}elseif (isset($nochange[$row['Field']]) && !$nochange[$row['Field']]){
			//debug($row['Field']." reset");
			$session['user'][$row['Field']] = $row['Default'];
		}
	}
	if (!array_key_exists("gold",$nochange) || !$nochange['gold'])
		$session['user']['gold'] = get_module_setting("startgold","circulum");
	if (!array_key_exists("gems",$nochange) || !$nochange['gems'])
		$session['user']['gems'] = get_module_setting("startgems","circulum");
	if (!array_key_exists("title",$nochange) || !$nochange['title']) {	
		require_once("lib/names.php");
		require_once("lib/titles.php");
		$newtitle = get_dk_title((int)$session['user']['dragonkills'], (int)$session['user']['sex']);
		$newname = change_player_title($newtitle);
		debug("dk title set to $newtitle");
		debug("name set to $newname from ".$session['user']['name']);
		$session['user']['title'] = $newtitle;
		$session['user']['name'] = $newname;
	}
	if (!array_key_exists("maxhitpoints",$nochange) || !$nochange['maxhitpoints']) {
		$session['user']['maxhitpoints'] = get_module_setting('maxhitpoints','circulum');
		$session['user']['hitpoints']=$session['user']['maxhitpoints'];
	}
	
	if ($session['user']['restorepage']=='') {
		$session['user']['restorepage']="village.php";
	}
	
	//add in here to make resets on your own. either to delete selected module prefs (add the modulename to the array) or execute your own code here
	$moduleprefstokill=modulehook("circulum-moduleprefs",array());
	debug($moduleprefstokill);
	if ($moduleprefstokill!==array()) {
		$modulenames='';
		foreach ($moduleprefstokill as $module) {
			$modulenames.="'".$module."',";
		}
		$modulenames=substr($modulenames,0,strlen($modulenames)-1);
		$sql="DELETE FROM ".db_prefix('module_userprefs')." WHERE modulename IN ($modulenames) AND userid={$session['user']['acctid']};";
		debug($sql);
		db_query($sql);
	}
	//
	require_once("lib/debuglog.php");
	debuglog($session['user']['name']." went through the circulum vitae");
	increment_module_pref('circuli',1,'circulum');
	
}

?>
