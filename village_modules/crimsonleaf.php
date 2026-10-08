<?php
//module requires inventory
function crimsonleaf_getmoduleinfo(){
	$info = array(
		"name"=>"Find the crimson leaf clover!",
		"author"=>"`2Oliver Brendel",
		"version"=>"1.0",
		"category"=>"Gypsy",
		"download"=>"",
		"settings"=>array(
			"Crimson Leaf Clover Settings,title",
			"winners"=>"Winners recorded,viewonly",
			"race"=>"Are we in the hot phase?,viewonly",
			),
		"requires"=>array(
			"inventory"=>"1.0|Inventory Module by XChrisX",
			),
	);
	return $info;
}

function crimsonleaf_install(){
	module_addhook_priority("gypsy",50);
	module_addhook("pvpwin");
	module_addhook("pvploss");
	module_addhook_priority("index",51);
	module_addhook("header-pvp");
	module_addhook("superuser");
	//insert it into the table
	if (!is_module_active('crimsonleaf')) {
		$sql="INSERT INTO item (class, name, description, gold, gems, weight, droppable, level, dragonkills, buffid, charges, link, hide, customvalue, execvalue, exectext, noeffecttext, activationhook, findchance, loosechance, dkloosechance, sellable, buyable, uniqueforserver, uniqueforplayer, equippable, equipwhere) VALUES
('Quest Items', '`qCrimson `2Leaf `gClover', 'Hmm, this is the famous leaf clover! Try to keep it as long as possible... it might be important someday... do not try to lose in pvp when being attacked...', 5, 0, 1, 0, 1, 0, 0, 0, '', 0, '', '', '', '', '0', 10, 0, 0, 0, 0, 1, 1, 0, '');";
		db_query($sql);
	}

	return true;
}
function crimsonleaf_uninstall(){
	return true;
}

function crimsonleaf_dohook($hookname,$args){
	global $session;
	$name="`qCrimson `2Leaf `gClover";

	switch($hookname){
		case "header-pvp":			
			if (httpget('op')!='') break;
			$date=date("t")-date("j");
			if ($date<7 && $date>=0) {
				crimsonleaf_leftdays();
				set_module_setting("race",1);
				$name="`qCrimson `2Leaf `gClover";
				$sql="SELECT a.name,a.acctid,a.alive,a.location,a.dragonkills as dragonkills FROM ".db_prefix("accounts")." AS a INNER JOIN ".db_prefix("inventory")." AS b ON a.acctid=b.userid INNER JOIN ".db_prefix("item")." AS i ON b.itemid=i.itemid WHERE i.name='$name';";
				$result=db_query($sql);
				$row=db_fetch_assoc($result);
				$row=array_shift(modulehook("pvpmodifytargets",array($row)));
				$who=$row['name'];
				if (!$who) break;
				if ($row['acctid']==$session['user']['acctid']) {
					output("`\$Wow... you feel everybody can attack you now, even while dead... or even alive strolling around here! The %s`\$ enforces the contest even now! Only little time until the turn of the month!`n`n",$name);
					break;
				}
				if (($session['user']['location']==$row['location'] || $row['location']=='') && ($row['alive']==0 || $session['user']['dragonkills']<$row['dragonkills'])) {
					//dead user with cleaver waiting for his death
					$badguy=crimsonleaf_setuptarget($row['acctid']);debug($badguy);
					if ($badguy===false) break;
					$session['user']['badguy']=createstring($badguy);
					addnav(array("Attack the %s`0-holder %s`0!",$name,$who),"pvp.php?op=fight");
				}
			} elseif (get_module_setting('race')==1) {
				//the end of this period
				$sql="UPDATE ".db_prefix('modules')." SET active=0 WHERE modulename='crimsonleaf' LIMIT 1;";
				db_query($sql);
				require_once("lib/datacache.php"); //necessary or not
				invalidatedatacache("hook-footer-pvp");
				invalidatedatacache("hook-pvpwin");
				invalidatedatacache("hook-pvploss");
				invalidatedatacache("hook-footer-gypsy");
				$settings=get_module_setting("winners");
				if (!is_array($settings)) $settings=array();
				$sql="SELECT a.acctid FROM ".db_prefix("accounts")." AS a INNER JOIN ".db_prefix("inventory")." AS b ON a.acctid=b.userid INNER JOIN ".db_prefix("item")." AS i ON b.itemid=i.itemid WHERE i.name='$name';";
				$row=db_fetch_assoc(db_query($sql));
				$settings[date("Y-n")]=$row['acctid'];
				set_module_setting("winners",serialize($settings));
				set_module_setting("race",0);
				require_once("lib/systemmail.php");
				$subject = array("Congratulations! You are a winner!");
				$content = array(
					"`vCongratulations! You were the holder of the %s`v until the end of this month! You will be notified of your reward soon!",
					$name
					);
				systemmail($row['acctid'],$subject,$content);
			} 
			break;
		case "superuser":
			addnav("Actions");
			$date=(int) date("j"); //only on the first of each month
			if ($date==1 && ($session['user']['superuser']&SU_MEGAUSER)==SU_MEGAUSER) addnav("Reset Crimson Leaf Clover","runmodule.php?module=crimsonleaf&op=reset");
			break;
		case "index":
			$sql="SELECT a.name,a.acctid FROM ".db_prefix("accounts")." AS a INNER JOIN ".db_prefix("inventory")." AS b ON a.acctid=b.userid INNER JOIN ".db_prefix("item")." AS i ON b.itemid=i.itemid WHERE i.name='$name';";
			$result=db_query_cached($sql,"crimson leaf",300);
			$row=db_fetch_assoc($result);
			if ($row) output("`@The owner of the %s`@ is: %s`0`n",$name,$row['name']);
			$date=date("t")-date("j");
			if ($date<7 && $date>=0) {
				crimsonleaf_leftdays();
				output_notl("`n");
			}
			break;
		case "gypsy":
			addnav(array("Ask about the %s`0",$name),"runmodule.php?module=crimsonleaf&op=ask");
			break;
		case "pvpwin":
			require_once("modules/inventory/lib/itemhandler.php");
			$hasleaf=check_qty_by_name($name,$args['badguy']['acctid']);
			if ($hasleaf==1) {
				//he has the leaf
				remove_item_by_name($name,1,$args['badguy']['acctid']);
				$result=add_item_by_name($name,1);
				if ($result) {
					require_once("lib/systemmail.php"); //mail the victim
					output("`\$Yes! You snatched away the %s`\$! Now... you need to check your back more often... But you feel that something good may happen too.`0`n",$name);
					systemmail($args['badguy']['acctid'],array("The %s",$name),array("`vOh no! You have lost the %s`v in PVP to %s`v!",$name,$session['user']['name']));
				}
			}
			break;
		case "pvploss":
			require_once("modules/inventory/lib/itemhandler.php");
			$hasleaf=check_qty_by_name($name);
			if ($hasleaf==1) {
				//he has the leaf
				remove_item_by_name($name,1);
				$result=add_item_by_name($name,1,$args['badguy']['acctid']);
				if ($result) {
					output("`\$No! You lost the %s`\$! Now... if you are quick, you can snatch it away again... but be quick or somebody else will do this!`0`n",$name);
					require_once("lib/systemmail.php"); //mail the victim
					systemmail($args['badguy']['acctid'],array("The %s",$name),array("`vOh yes! You have won the %s`v in PVP from %s`v as you searched the attacker after the futil attempt to kill you!",$name,$args['badguy']['name']));
					}
			}
			break;
	}
	return $args;
}

function crimsonleaf_run(){
	global $session;
	$op = httpget("op");
	page_header("The Gypsy");
	$name="`qCrimson `2Leaf `gClover";
	output("`b`i`c`v%s`c`i`b`n`5",$name);
	require_once("modules/inventory/lib/itemhandler.php");

	switch ($op) {
		case "reset":
			$sql="SELECT i.itemid AS itemid FROM ".db_prefix("item")." AS i WHERE i.name='$name';";
			$row=db_fetch_assoc(db_query($sql));
			require_once("modules/inventory/lib/itemhandler.php");
			output("Affected rows: %s",inventory_legacy_delete_item_from_all_inventories($row['itemid']));
			villagenav();
			break;
		case "ask":

			output("\"`!So, you want to know about the %s`!? ... Well, I'll check... for free... `5\" she answers as she takes a good look into her crystal ball...`n`n",$name);
			$sql="SELECT a.name,a.acctid FROM ".db_prefix("accounts")." AS a INNER JOIN ".db_prefix("inventory")." AS b ON a.acctid=b.userid INNER JOIN ".db_prefix("item")." AS i ON b.itemid=i.itemid WHERE i.name='$name';";
			$result=db_query($sql);
			$row=db_fetch_assoc($result);
			$who=$row['name'];
			if ($who=='') {
				$result=add_item_by_name($name,1);
				if ($result && $session['user']['dragonkills']<1) {
					output("She stops to concentrate and lifts her head: \"`!Well... Nobody has it... so... of course, because I have it here! Here you go, have fun with it... and... watch your back ^^...`5\"");
				} else {
					output("She stops to concentrate and lifts her head: \"`!Well... err... I thought I have it here, but somebody must be quicker, please ask me again ^^...`5\"");
				}
			} else {
				if ($row['acctid']==$session['user']['acctid']) $who=translate_inline("`\$YOU!");
				output("She stops to concentrate and lifts her head: \"`!Well... I know who got the %s`!! `n`n`cIt is `n`i`b`\$%s!!`!`b`i`c`nTry to snatch it away... you may sucessfully fight this one in fair combat (PVP) and obtain it. Someday it will be worth quite something... Go for it! Once I'll know when the deadline is, I will announce it here... ask me again...`5\"",$name,$who);
			}
			output_notl("`n`n");
			crimsonleaf_leftdays();
			break;
		default:
	}
	addnav("Return to the Gypsy","gypsy.php");
	page_footer();
}

function crimsonleaf_setuptarget($acctid) {
	global $pvptimeout, $session;
	$sql = "SELECT name AS creaturename, level AS creaturelevel, weapon AS creatureweapon, gold AS creaturegold, experience AS creatureexp, maxhitpoints AS creaturehealth, attack AS creatureattack, defense AS creaturedefense, loggedin, location, laston, alive, acctid, pvpflag, boughtroomtoday, race FROM " . db_prefix("accounts") . " WHERE acctid='$acctid'";
    $result = db_query($sql);
	$row=db_fetch_assoc($result);
	if (!$row) return false;
	$row['creatureexp'] = round($row['creatureexp'],0);
	$row['playerstarthp'] = $session['user']['hitpoints'];
	$row['fightstartdate'] = strtotime("now");
	$row['type']="pvp";
	return $row;
}

function crimsonleaf_leftdays() {
	$name="`qCrimson `2Leaf `gClover";
	$left=strtotime(date("Y-m-t 00:00:00"))-strtotime(date("Y-m-d H:i:s"));
	$left+=86400;
	$secs=$left%60;
	$left/=60;
	$mins=$left%60;
	$left/=60;
	$hours=$left%24;
	$left/=24;
	$days=floor($left);
	$leftstring='';
	if ($days>1) $d="days"; else $d="day";
	if ($hours>1) $h="hours"; else $h="hour";
	if ($mins>1) $m="minutes"; else $m="minute";
	if ($secs>1) $s="seconds"; else $s="second";
	if ($days) $leftstring.=" ".$days." ".translate_inline($d);
	if ($hours) $leftstring.=" ".$hours." ".translate_inline($h);
	if ($mins) $leftstring.=" ".$mins." ".translate_inline($m);
	if ($secs) $leftstring.=" ".$secs." ".translate_inline($s);
	output("Only little time left until the end of the %s`0 period!`n`n`% %s`n`0",$name,$leftstring);
}
?>
