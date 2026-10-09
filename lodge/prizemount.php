<?php
// addnews ready
// translator ready
// mail ready

require_once("lib/systemmail.php");

use Lotgd\Mounts;

function prizemount_getlist(){
	$mounts=",0,None";
	// The table lock is necessary since some place where it can be called
	// from already have a lock and if we don't lock we'll error there.
	db_query("LOCK TABLES ".db_prefix("mounts")." WRITE");
	$sql = "SELECT mountid,mountname,mountcategory FROM " .
		db_prefix("mounts") .  " ORDER BY mountcategory";
	$result = db_query($sql);
	//unlock it now, since we are done.
	db_query("UNLOCK TABLES");

	while ($row = db_fetch_assoc($result)){
		$mounts.="," . $row['mountid'] . "," . $row['mountcategory'] .
			": ". color_sanitize($row['mountname']);
	}
	return $mounts;
}

function prizemount_getmoduleinfo(){
	$info = array(
			"name"=>"Prize Mount",
			"author"=>"JT Traub<br>w/ minor modifications by Chris Vorndran and Oliver Brendel",
			"version"=>"1.16",
			"category"=>"Lodge",
			"download"=>"core_module",
			"settings"=>array(
				"Prize Mount Module Settings,title",
				"mountid"=>"Which mount to award to players who have days?,enum".prizemount_getlist()."|0",
				"increment"=>"Per how many \$ is the game day reward?,range,1,100,1|5",
				"daysPerIncrement"=>"How many game days are awarded per (setting)\$ donated?,range,2,10,1|3",
				"awarding"=>"Are prize mounts being handed out?,bool|0",
				),
			"prefs"=>array(
				"Prize Mount User Preferences,title",
				"oldmount"=>"Id of old mount,viewonly",
				"daysleft"=>"How many days will the user get a special mount?,int|0",
				),
		     );
	return $info;
}

function prizemount_install(){
	module_addhook("pre-newday");
	module_addhook("donation");
	module_addhook("donator_point_messages");
	module_addhook("footer-stables");
	module_addhook("header-stables");
	return true;
}
function prizemount_uninstall(){
	return true;
}

function prizemount_dohook($hookname,$args){
	global $session, $playermount;
	$newhorse = get_module_setting("mountid");
	require_once("lib/debuglog.php");
	// Do nothing if a prize mount id hasn't been set up!
	if ($newhorse == 0) return $args;

	// Mount Upgrades Interface
	// Mounts which upgrade from a prizemount are considered prizemounts.
	$prizemounts=array($newhorse => 1);
	while($newhorse && is_module_active("mountupgrade")){
		$newhorse=get_module_objpref("mounts",$newhorse,"upgradeto","mountupgrade");
		if ($newhorse) $prizemounts[$newhorse]=1;
	}
	$sql="SELECT mountname FROM ".db_prefix('mounts')." WHERE mountid=".get_module_setting('mountid');
	$result=db_query_cached($sql,"prizemountname",1200);
	if (db_num_rows($result)<1) 
		$mount=translate_inline("No name provided");
	else {
		$row=db_fetch_assoc($result);
		$mount=$row['mountname'];
	}
	$currency=getsetting('paypalcurrency','USD');
	$increment=get_module_setting('increment');
	switch($hookname){
		case "donator_point_messages":
			$args['messages'][]=sprintf_translate("`7You will also get for `\$every full %s %s donated %s days`7 with the prize mount `\$%s`7. This counts per donation, not in total. `n`lIn plain English: You can donate 5 times %s 1, no mount. You donate 1 time %s 5, 10 days with the mount. You donate 3 times %s 5, 30 days with the mount...`0",$currency,$increment,get_module_setting('daysPerIncrement'),$mount,$currency,$currency,$currency,$currency);
			break;
		case "header-stables":
			if (array_key_exists($session['user']['hashorse'],$prizemounts)) {
				blocknav("stables.php?op=buymount", true);
				blocknav("stables.php?op=sellmount", true);
			}
			break;
		case "footer-stables":
			if (array_key_exists($session['user']['hashorse'],$prizemounts)) {
				blocknav("stables.php?op=buymount", true);
				blocknav("stables.php?op=sellmount", true);
				$op = httpget("op");
				if ($op == "examine") {
					output("`n`7While the creature you are examining is beautiful, you realize you cannot bear to part with your special mount.`n");
					blocknav("stables.php?op=buymount", true);
				} elseif ($op == "") {
					output("`n`7Regardless of how tempting the offer is, you know you cannot bear to part with your special mount.`n");
					blocknav("stables.php?op=sellmount", true);
				}
			}
			break;
		case "donation":
			if (!get_module_setting("awarding")) break;
			$amt = $args['amt']; // This amount is in donator points, not dollars
			$divide = getsetting('dpointspercurrencyunit',100)*$increment;
			debuglog(serialize($args),false,$args['id']);
			debuglog("Amount ".$amt." divided by ".$divide,false,$args['id']);
			// 500 is $5.00
			$adddays = floor($amt/$divide) * get_module_setting("daysPerIncrement");
			debuglog("Added $adddays days to the prizemount",false,$args['id']);
			if ($adddays == 0) break;
			$curdays = get_module_pref("daysleft", "prizemount", $args['id']);
			debuglog("Curdays: ".$curdays. " for prizemount",false,$args['id']);
			if ($adddays < 0) {
				$adddays = (abs($adddays) > $curdays) ? -$curdays : $adddays;
				debuglog("donation reversed, stealing $adddasys back for prizemount",false,$args['id']);	
				systemmail($args['id'], array("Donation reversed!"),
						array("A previously made donation of \$%s has been reversed by PayPal.  The game is therefore removing %s days from those you have remaining on your prize mount.  If this causes the expiration of your prizemount, you will keep it for the rest of this game day and your previous mount will be returned to you on the next new day.", round($amt/100, 2), abs($adddays)));
			} else {
				if (!isset($args['silent'])) $args['silent']=0;
				if ($args['silent']!=1) {
					if ($curdays) {
						systemmail($args['id'], "Donation recorded!",
								sprintf_translate("You have been awarded %s additional game days use of the prize mount for your donation of %s %s.  Thank you for your donation.", $adddays, $currency, round($amt/100, 2)));
					} else {
						systemmail($args['id'], "Donation recorded!",
								sprintf_translate("You have been awarded %s game days use of the prize mount for your donation of %s %s.  Your uses will begin on your next new day.  Thank you for your donation.", $adddays, $currency, round($amt/100, 2)));
					}
				}
			}
			$days = $curdays + $adddays;
			debuglog("Days: ".$days,false,$args['id']);
			set_module_pref("daysleft", $days, "prizemount", $args['id']);
			debuglog("Set Prizemount Days to $days",false,$args['id']);
			$now_curdays = get_module_pref("daysleft", "prizemount", $args['id']);
			debuglog("User now has $now_curdays prizemount days",false,$args['id']);
			break;
		case "pre-newday":
			$days = get_module_pref("daysleft");
			$prizemount = get_module_setting("mountid");
			debuglog("Prizemount: $days days before and $prizemount as prizemount and ".$session['user']['hashorse']." as player mount id");
			if ($days == 0) {
				// We either have no prize ever, or we need to restore the old
				// mount
				$id = (int)get_module_pref("oldmount");
				if ($session['user']['hashorse'] == $prizemount) {
					// They had an old mount possibly
					// Delete the marker
					$sql = "DELETE FROM " . db_prefix("module_userprefs") . " WHERE modulename='prizemount' AND setting='oldmount' AND userid='{$session['user']['acctid']}'";
					db_query($sql);
					// Give them back their old mount
					modulehook("loseprizemount");
					$session['user']['hashorse'] = $id;
					if ($id > 0) {
						Mounts::getInstance()->loadPlayerMount($id);
						$playermount = Mounts::getInstance()->getPlayerMount();
					} else {
						$playermount = [];
					}
					set_module_pref("oldmount", 0);
					// Handle the renaming of named mounts
					modulehook("stable-mount");
					debuglog("removed prizemount from user due to expiration");
				} else {
					// They didn't have a prize mount, do nothing
				}
			} else {
				debuglog("Assigning or reducing the prizemount");
				$id = get_module_pref("oldmount");
				if ($session['user']['hashorse'] != $prizemount) {
					// This is first newday after getting a prize mount
					set_module_pref("oldmount", $session['user']['hashorse']);
					// Args to a hook MUST be an array
					$args2 = array('prizemount'=>$prizemount);
					$args2 = modulehook("gainprizemount", $args2);
					$id = (int)$args2['prizemount'];
					$session['user']['hashorse'] = $id;
					if ($id > 0) {
						Mounts::getInstance()->loadPlayerMount($id);
						$playermount = Mounts::getInstance()->getPlayerMount();
					} else {
						$playermount = [];
					}
					debuglog("assigned prizemount to user");
					// Handle the renaming of named mounts
					modulehook("stable-mount");
				} else {
					// They have had the prize mount for a while, and it's
					// still valid, do nothing.
				}
				$days--;
				set_module_pref("daysleft", $days);
				debuglog("Set prizemount Days to $days on a newday");
				if ($days == 0) {
					output("`n`&This is your last game day for your awarded mount.`0`n`n");
				} else {
					output("`n`&You have %s additional game %s left on your awarded mount.`0`n`n",
							$days, translate_inline($days == 1? "day" : "days"));
				}
			}
			break;
	}
	return $args;
}

function prizemount_run(){
}
?>
