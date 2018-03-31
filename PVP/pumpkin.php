<?php
//module requires inventory
function pumpkin_getmoduleinfo(){
	$info = array(
		"name"=>"Halloween Pumpkin!",
		"author"=>"`2Oliver Brendel",
		"version"=>"1.0",
		"category"=>"Halloween",
		"download"=>"",
		"settings"=>array(
			"Pumpkin  Settings,title",
			"winners"=>"Winners recorded,viewonly",
			"race"=>"Are we in the hot phase?,viewonly",
			"Pumpkins"=>"Amount of pumpkins given out,int|3",
			"MaxDK"=>"Max DKs for first time pumpkin receivers,int|10",
			"rewarddp"=>"Reward of DP after end,int|500",
			),
		"requires"=>array(
			"inventory"=>"1.0|Inventory Module by XChrisX",
			),
	);
	return $info;
}

function pumpkin_install(){
	module_addhook_priority("gypsy",50);
	module_addhook("pvpwin");
	module_addhook("pvploss");
	module_addhook_priority("index",51);
	module_addhook("header-pvp");
	module_addhook("superuser");
	module_addhook("pvpadjust");
	//insert it into the table
	$name="`xH`qalloween `xP`qumpkin";
	$sql="SELECT count(itemid) as counter FROM ".db_prefix('item')." WHERE name='$name'";
	$result=db_query($sql);
	$row=db_fetch_assoc($result);
	if ($row['counter']==0) {
		$sql="REPLACE INTO item (class, name, description, gold, gems, weight, droppable, level, dragonkills, buffid, charges, link, hide, customvalue, execvalue, exectext, noeffecttext, activationhook, findchance, loosechance, dkloosechance, sellable, buyable, uniqueforserver, uniqueforplayer, equippable, equipwhere) VALUES
('Quest Items', '$name', 'Hmm, nasty eyes...! Try to keep him as long as possible... it might be important someday... do not try to lose in pvp when being attacked...', 5, 0, 1, 0, 1, 0, 0, 0, '', 0, '', '', '', '', '0', 10, 0, 0, 0, 0, 1, 1, 0, '');";
		db_query($sql); 
	}

	return true;
}
function pumpkin_uninstall(){
	return true;
}

function pumpkin_dohook($hookname,$args){
	global $session;
	$name="`xH`qalloween `xP`qumpkin";
//debug($name);
	switch($hookname){
		case "pvpadjust":
			require_once("modules/inventory/lib/itemhandler.php");
			$hasleaf=check_qty_by_name($name,$args['acctid']);
			if ($hasleaf==1) {
				output("`n`xThe %s`x drains the powers of its holder!`n`n",$name);
				$args['creatureattack']*=0.5;
				$args['creaturedefense']*=0.4;
				$args['creatureweaon']=$name;
			}
		case "header-pvp":			
			if (httpget('op')!='') break;
			require_once("modules/inventory/lib/itemhandler.php");
			$hasleaf=check_qty_by_name($name,$session['user']['acctid']);
			if (pumpkin_active()) {
				pumpkin_leftdays();
				set_module_setting("race",1);
				$sql="SELECT a.name,a.acctid,a.alive,a.location,a.dragonkills as dragonkills FROM ".db_prefix("accounts")." AS a INNER JOIN ".db_prefix("inventory")." AS b ON a.acctid=b.userid INNER JOIN ".db_prefix("item")." AS i ON b.itemid=i.itemid WHERE i.name='$name'";
debug($sql);
				$result=db_query($sql);
				while ($row=db_fetch_assoc($result)) {
					debug($row);
					//$row=array_shift(modulehook("pvpmodifytargets",array($row)));
					$who=$row['name'];
					if (!$who) break;
					if ($row['acctid']==$session['user']['acctid']) {
						output("`\$Wow... you feel everybody can attack you now, even while dead... or even alive strolling around here! The %s`\$ enforces the contest even now! Only little time until the turn of the month!`n`n",$name);
						continue;
					}
				/*	debug($who);
					debug($session['user']['location']);
					debug($row['location']);
					debug($row['dragonkills']);
					debug($hasleaf);*/
					debug($row);
					if (($session['user']['location']==$row['location'] || $row['location']=='Nin Dwelling' || $row['location']=='Iwagakure' || $row['location']=='') && ($row['alive']==0 || ($session['user']['dragonkills']*.75)<=$row['dragonkills']) && !$hasleaf) {
						//dead user with cleaver waiting for his death
						$badguy=pumpkin_setuptarget($row['acctid']);
						if ($badguy===false) break;
						$session['user']['badguy']=createstring($badguy);
						addnav(array("Attack the %s`0-holder %s`0!",$name,$who),"pvp.php?op=fight");
					}
				}
			} elseif (get_module_setting('race')==1) {
				//the end of this period
//				$sql="UPDATE ".db_prefix('modules')." SET active=0 WHERE modulename='pumpkin' LIMIT 1;";
//				db_query($sql);
				require_once("lib/datacache.php"); //necessary or not
				invalidatedatacache("hook-footer-pvp");
				invalidatedatacache("hook-pvpwin");
				invalidatedatacache("hook-pvploss");
				invalidatedatacache("hook-footer-gypsy");
				$settings=get_module_setting("winners");
				if (!is_array($settings)) $settings=array();
				$sql="SELECT DISTINCT a.acctid FROM ".db_prefix("accounts")." AS a INNER JOIN ".db_prefix("inventory")." AS b ON a.acctid=b.userid INNER JOIN ".db_prefix("item")." AS i ON b.itemid=i.itemid WHERE i.name='$name';";
				while ($row=db_fetch_assoc(db_query($sql))) {
					$settings[date("Y-n")].=$row['acctid']."|";
					require_once("lib/systemmail.php");
					$subject = array("Congratulations! You are a winner!");
					$content = array(
						"`vCongratulations! You were one of the holders of a %s`v until the end of Halloween! You will be notified of your reward soon!",
						$name
						);
				//	systemmail($row['acctid'],$subject,$content);
				}
				set_module_setting("winners",serialize($settings));
				set_module_setting("race",0);
			} 
			break;
		case "superuser":
			addnav("Actions");
			$date=(int) date("j"); //only on the first of each month
			$date=1;
			if ($date==1 && ($session['user']['superuser']&SU_MEGAUSER)==SU_MEGAUSER) {
				addnav("Halloween");
				addnav("Halloween Pumpkin Control","runmodule.php?module=pumpkin&op=controlcenter");
			}

			break;
		case "index":
			$sql="SELECT a.name,a.acctid FROM ".db_prefix("accounts")." AS a INNER JOIN ".db_prefix("inventory")." AS b ON a.acctid=b.userid INNER JOIN ".db_prefix("item")." AS i ON b.itemid=i.itemid WHERE i.name='$name';";
			$result=db_query_cached($sql,"pumpkin",300);
			$names=array();
			while ($row=db_fetch_assoc($result)) {
				if ($row) $names[]=$row['name'];
			}
			$names=implode(",",$names);
			if ($names!='') output("`@The owners of the %ss`@ are: %s`0`n",$name,$names);
			if (pumpkin_active()) {
				pumpkin_leftdays();
				output_notl("`n");
			}
			$out=file_get_contents("modules/pumpkin/snow.js");
			output_notl($out,true);
			break;
		case "gypsy":
			addnav(array("Ask about the %s`0",$name),"runmodule.php?module=pumpkin&op=ask");
			break;
		case "pvpwin":
			require_once("modules/inventory/lib/itemhandler.php");
			$hasleaf=check_qty_by_name($name,$args['badguy']['acctid']);
			$has_winner=check_qty_by_name($name,$session['user']['acctid']);
			if ($hasleaf==1 && !$has_winner) { //only give out to a different guy
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
					systemmail($args['badguy']['acctid'],array("The %s",$name),array("`vOh yes! You have won the %s`v in PVP from %s`v as you searched the attacker after the futile attempt to kill you!",$name,$args['badguy']['name']));
					}
			}
			break;
	}
	return $args;
}

function pumpkin_run(){
	global $session;
	$op = httpget("op");
	page_header("The Gypsy");
	$name="`xH`qalloween `xP`qumpkin";
	output("`b`i`c`v%s`c`i`b`n`5",$name);
	require_once("modules/inventory/lib/itemhandler.php");
	require_once("lib/superusernav.php");
	$rewarddp=get_module_setting('rewarddp');
	switch ($op) {
		case "controlcenter":
			page_header("Superuser Halloween Controlcenter");
			output("`xThis is the Halloween Controlcenter where you can issue actions regarding the Halloween Pumpkins.`n`n");
			$owners=pumpkin_owners($name);
			if ($owners!=array()) {
				output("Winners: ");
				output_notl(implode(",",$owners));
				addnav("Rewards");
				addnav(array("Give Owners %s DP",$rewarddp),"runmodule.php?module=pumpkin&op=givedp");
			}
			superusernav();
			
			addnav("Actions");
			addnav("Reset Pumpkins","runmodule.php?module=pumpkin&op=reset");
			page_footer();
			break;
		case "givedp":
			$owners=pumpkin_owners($name);
			require_once("lib/systemmail.php");
			foreach($owners as $acctid=>$ownername) {
				$mailsubject=translate_mail(array("Halloween Reward"),$acctid);
				$mailbody=translate_mail(array("`qCongratulations!`n`xYou are awarded %s donation points for holding the %s`x!",$rewarddp,$name),$acctid);
				systemmail($acctid,$mailsubject,$mailbody);
				$sql = "UPDATE " . db_prefix("accounts") . " SET donation=donation+'$rewarddp' WHERE acctid='$acctid'";
				$result=db_query($sql);
				modulehook("donation", array("id"=>$acctid, "amt"=>$rewarddp, "manual"=>true));
				if ($result) output("`xMailed %s`x for %s points...`n",$ownername,$rewarddp);
			}
			
			superusernav();
			addnav("Actions");
			addnav("Reset Pumpkins","runmodule.php?module=pumpkin&op=reset");
			
			page_footer();
			break;
		case "reset":
			page_header("Superuser Halloween Controlcenter");
			$sql="SELECT i.itemid AS itemid FROM ".db_prefix("item")." AS i WHERE i.name='$name';";
			$row=db_fetch_assoc(db_query($sql));
			$sql="DELETE FROM ".db_prefix('inventory')." WHERE itemid=".$row['itemid'];
			db_query($sql);
			output("Affected rows: %s",db_affected_rows());
			superusernav();
			page_footer();
			break;
		case "ask":
			output("\"`!So, you want to know about the %s`!? ... Well, I'll check... for free... `5\" she answers as she takes a good look into her crystal ball...`n`n",$name);
			$owners=pumpkin_owners($name);
			if (count($owners)<get_module_setting('Pumpkins') && $session['user']['dragonkills']<get_module_setting('MaxDK')) {
				$result=add_item_by_name($name,1);
				if ($result && $session['user']['dragonkills']<get_module_setting('MaxDK')) {
					output("She stops to concentrate and lifts her head: \"`!Well... There are some left... so... of course, get one! Here you go, have fun with it... and... watch your back `\$^^...`5\"");
				} else {
					output("She stops to concentrate and lifts her head: \"`!Well... err... I thought I have some here, but somebody must be quicker, please ask me again ^^...`5\"");
				}
			} else {
				if ($owners!=array()) $who=implode("`n",$owners);
					else $who="";
				if (in_array($session['user']['acctid'],$owners)) $usergot=1;
					else $usergot=0;
				if ($who=='') $who=translate_inline("Nobody yet!");
				output("She stops to concentrate and lifts her head: \"`!Well... I know who got the %s`!! `n`n`c`n`i`b`\$%s!!`!`b`i`c`n",$name,$who);
				if (!$usergot) output("Try to snatch it away... you may sucessfully fight this one in fair combat (PVP) and obtain it. Someday it will be worth quite something... Go for it! Once I'll know when the deadline is, I will announce it here... ask me again...`5\"",$name,$who);
					else output("...look into your pockets... you got one...");
			}
			output_notl("`n`n");
			pumpkin_leftdays();
			break;
		default:
	}
	addnav("Return to the Gypsy","gypsy.php");
	page_footer();
}

function pumpkin_setuptarget($acctid) {
	global $pvptimeout, $session;
	$sql = "SELECT name AS creaturename, level AS creaturelevel, weapon AS creatureweapon, gold AS creaturegold, experience AS creatureexp, maxhitpoints AS creaturehealth, attack AS creatureattack, defense AS creaturedefense, loggedin, location, laston, alive, acctid, pvpflag, boughtroomtoday, race FROM " . db_prefix("accounts") . " WHERE acctid='$acctid'";
    $result = db_query($sql);
	$row=db_fetch_assoc($result);
	if (!$row) return false;
	$row['creatureexp'] = round($row['creatureexp'],0);
	$row['playerstarthp'] = $session['user']['hitpoints'];
	$row['creatureattack'] *=0.5;
	$row['creaturedefense'] *=0.5;
	$row['fightstartdate'] = strtotime("now");
	$row['type']="pvp";
	return $row;
}

function pumpkin_leftdays() {
	static $done_already=false;
	$name="`xH`qalloween `xP`qumpkin";
	$end=date("Y")."-11-01";
	$left=strtotime(date("$end 09:00:00"))-strtotime(date("Y-m-d H:i:s"));
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
	if (!$done_already) output("`gOnly little time left until the end of the %s`g hunt!`n`n`% %s`n`0",$name,$leftstring);
	$done_already=true;
}

function pumpkin_active() {
	$date=date("m-d");
	if ($date=="11-01" || $date=="10-31") return true;
	return false;
	$date=date("t")-date("j")+1;
	if (date("G")==0) return false;
	return true;
	if ($date<7 && $date>=0) return true;
		return false;
}

function pumpkin_owners($name) {

	$sql="SELECT a.name,a.acctid FROM ".db_prefix("accounts")." AS a INNER JOIN ".db_prefix("inventory")." AS b ON a.acctid=b.userid INNER JOIN ".db_prefix("item")." AS i ON b.itemid=i.itemid WHERE i.name='$name';";
	$result=db_query($sql);
	$owners=array();
	while ($row=db_fetch_assoc($result)) {
		$owners[$row['acctid']]=$row['name'];
	}
	return $owners;

}
?>
