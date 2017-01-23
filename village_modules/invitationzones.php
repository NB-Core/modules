<?php
function invitationzones_getmoduleinfo(){
	$info = array(
		"name"=>"Fightingzones by Invitation",
		"version"=>"1.0",
		"author"=>"`2Oliver Brendel",
		"category"=>"Fighting Zones",
		"download"=>"",
		"settings"=>array(
					"Fightingzones by Invitation - Preferences, title",
					"goldcost"=>"Costs per DK in Gold,int|150",
					"name"=>"Name of the female arena owner,text|`&K`%otori",
					"timeout_days"=>"How many days does an arena last?,int|36",
					),
		"requires"=>array(
			"fightingzone"=>"1.2|Fighting Zone by Oliver Brendel",
			),
	);
	return $info;
}

function invitationzones_install(){
	module_addhook("fightingzones");
	$fb=array(
		'battleid'=>array('name'=>'battleid', 'type'=>'int(11) unsigned', 'extra'=>'auto_increment'),
		'challenger'=>array('name'=>'challenger', 'type'=>'int(11) unsigned'),
		'opponent'=>array('name'=>'opponent', 'type'=>'int(11) unsigned'),
		'date'=>array('name'=>'date', 'type'=>'datetime','default'=>'0000-00-00 00:00:00'),
		'length'=>array('name'=>'length', 'type'=>'smallint unsigned'),
		'zonetype'=>array('name'=>'zonetype', 'type'=>'smallint unsigned','default'=>'0'),
		'individualname'=>array('name'=>'individualname', 'type'=>'varchar(250)','default'=>''),
		'spectators'=>array('name'=>'spectators', 'type'=>'tinyint unsigned','default'=>'0'),
		'spectatorcanpost'=>array('name'=>'spectatorcanpost', 'type'=>'tinyint unsigned','default'=>'0'),
		'key-PRIMARY' => array('name'=>'PRIMARY', 'type'=>'primary key', 'unique'=>'1', 'columns'=>'battleid'),
		'key-one'=> array('name'=>'challenger', 'type'=>'key', 'unique'=>'0', 'columns'=>'challenger'),
		'key-two'=> array('name'=>'spectators', 'type'=>'key', 'unique'=>'0', 'columns'=>'spectators'),			
		'key-three'=> array('name'=>'opponent', 'type'=>'key', 'unique'=>'0', 'columns'=>'opponent'),
	);
	require_once("lib/tabledescriptor.php");
	synctable(db_prefix("fightingzones_battles"), $fb, true);
	return true;
}

function mailarchive_uninstall() {

	return true;
}

function invitationzones_uninstall(){
	if(db_table_exists(db_prefix("fightingzones_battles"))){
		db_query("DROP TABLE ".db_prefix("fightingzones_battles"));
	}
	return true;
}

function invitationzones_dohook($hookname,$args){
	global $session;
	$u=&$session['user'];
	$op=httpget('op');
	switch ($hookname) {
		case "fightingzones":
/* 			$allowed=array(7,19788,37231);
			if (!in_array($u['acctid'],$allowed)) break; */
			addnav("Invitational Battles");
			addnav("Invite for Battle","runmodule.php?module=invitationzones&op=invite");
			addnav("Browse fights","runmodule.php?module=invitationzones&op=spectate");
			
			break;
	}
	return $args;
}

function invitationzones_run(){
	global $session;
	$op=httpget('op');
	$u=&$session['user'];
	page_header("Invitational Battles");
	if ($op!='spectate') output("`b`i`c`!Fighting Zone`c`i`b`n`n");

	require_once("lib/commentary.php");
	$id=(int)httpget('battleid');
	if ($id>0) $link="runmodule.php?module=invitationzones&battleid=".$id."&";
		else $link="runmodule.php?module=invitationzones&";
	$prefb=db_prefix('fightingzones_battles');
	$preac=db_prefix('accounts');
	$name=get_module_setting('name');
	$goldcost=get_module_setting('goldcost')*$u['dragonkills'];


	addnav("Navigation");
	addnav("Back to the Main Zone Screen","runmodule.php?module=fightingzone");
	addnav("Back to the Main Invitational Battles",$link."op=");
	addnav("Actions");
	
	switch($op) {
	
		case "invite":
			$subop=httpget('subop');
			switch($subop) {
			
				case "pay":	
					if ($u['gold']<$goldcost) {
						output("%s`\$ arches a brow, \"`lYou should have proper funds to pay me. Come again when your pockets hold the right amount.`\$\"",$name);
						break;
					}
					$u['gold']-=$goldcost;
					$target=(int)httppost('target');
					$length=(int)httppost('length');
					$prefix=(int)httppost('prefix');
					$zonetype=(int)httppost('zonetype');
					$individualname=httppost('individualname');
					$spectators=(int)httppost('spectators');
					$spectatorpost=(int)httppost('spectatorpost');
					
					$individualtext=str_replace(array("`n","\"","`b","`c"),array("","","","`b","`c"),$individualtext);
					
					if ($prefix==0) {
						$arrive="";
					} else {
						if ($prefix==1) $arrive=sprintf_translate("in %s hours",$prefix);
							else $arrive=sprintf_translate("in %s hour",$prefix);					
					}
					$sql="SELECT name FROM $preac WHERE acctid=$target;";
					$result=db_query($sql);
					$row=db_fetch_assoc($result);
					$enemy=$row['name'];
					output("`kYou pay the amount of `^%s gold pieces`k and %s`k starts to call a messenger to send a letter of challenge to %s`k.`n`n",$goldcost,$name,$enemy);
					output("\"`lThank you for using the facility. You may enter the arena %s and wait for your challenger to arrive.`k\"",$arrive);
					$sql="INSERT INTO $prefb (battleid,challenger,opponent,date,length,zonetype,spectators,spectatorcanpost,individualname) VALUES (0,".$u['acctid'].",".$target.",'".date("Y-m-d H:i:s",strtotime("+$prefix days"))."',".$length.",".$zonetype.",".$spectators.",".$spectatorpost.",'".$individualname."');";
					db_query($sql);
					debug($sql);
					
					$subject = translate_mail(array("Letter of Challenge from %s",$u['name']),0);
					$text=translate_mail(array(
						"`\$Hello,`n`n`kI hereby notify you that %s`k has challenged you to a duel in the private fighting grounds. At the time this message was written, the challenge will begin in %s hour(s) and may last up to %s day(s).`n`n%s",$u['name'],$prefix,$length,$name
					),0);
					require_once("lib/systemmail.php");
					systemmail($target,$subject,$text);
					
					break;
				
				case "search":
					if ($u['gold']<$goldcost) {
						output("%s`\$ arches a brow, \"`lYou should have proper funds to pay me. Come again when your pockets hold the right amount.`\$\"",$name);
						break;
					}
					$target=httppost('target');
					$submit=translate_inline("Search");
					addnav("New Search",$link."op=invite&subop=search");
					if ($target=='') {
							rawoutput("<form action='".$link."op=invite&subop=search' method='POST'>");
							addnav("",$link."op=invite&subop=search");
							output("Look for enemy by name: ");
							rawoutput("<input name='target' value='$target'>");
							rawoutput("<input type='submit' class='button' value='$submit'></from><br><br>");
					} elseif (is_numeric($target)) {
							//found
							$sql="SELECT name FROM $preac WHERE acctid=$target;";
							$result=db_query($sql);
							$row=db_fetch_assoc($result);
							$enemy=$row['name'];							
							output("`\$So you want to challenge %s`\$ to a fight?`n`n`^This costs you %s gold.`n",$enemy,$goldcost);
							rawoutput("<form action='".$link."op=invite&subop=pay' method='POST'>");
							addnav("",$link."op=invite&subop=pay");
							output("`n`4The name for your fight (incl. colours, if empty, it stays 'Fighting Zone'):");
							rawoutput("<input type='input' name='individualname' length='150'>");
							output("`n`4Select the days you want to fight:");
							$timeout_days = get_module_setting('timeout_days');
							rawoutput("<input type='hidden' name='target' value='$target'><select name='length'>");
							for ($i=1;$i<$timeout_days;$i++) {
								rawoutput("<option value='".$i."'>".$i." ".translate_inline($i>1?"days":"day")."</option>");
							}
							rawoutput("</select>");
							output("`n`4The battle will commence: ");
							$now=translate_inline("Now");
							$yes=translate_inline("Yes");
							$no=translate_inline("No");
							$viewonly=translate_inline("Only watch");
							$noviewonly=translate_inline("Watch and comment");
							rawoutput("<select name='prefix'><option value='0'>$now</option>");
							for ($i=1;$i<49;$i++) {
								rawoutput("<option value='".$i."'>".$i." ".translate_inline($i>1?"days":"day")."</option>");
							}
							rawoutput("</select>");
							$zones=array('None','Desert','Wetlands','Lightning Plains','Fire Valley','Green Lands','Plains','Icelands','Death Valley');
							output("`n`4Which zone type do you want?: ");
							rawoutput("<select name='zonetype'>");
							foreach ($zones as $key=>$zone) {
								rawoutput("<option value='$key'>$zone</option>");
							}
							rawoutput("</select>");
							output("`n`4Do you want to allow spectators: ");
							rawoutput("<select name='spectators'><option value='0' selected>$no</option><option value='1'>$yes</option></select>");
							output("`n`4If yes, can they post or only watch silently: ");
							rawoutput("<select name='spectatorpost'><option value='0' selected>$viewonly</option><option value='1'>$noviewonly</option></select>");							
							$submit=translate_inline("Pay");
							rawoutput("<input type='submit' class='button' value='$submit'></from><br><br>");
					} else {
							require_once("lib/lookup_user.php");
							$names=lookup_user($target);
							if ($names[0]!==false) {
									rawoutput("<form action='".$link."op=invite&subop=search' method='POST'>");
									addnav("",$link."op=invite&subop=search");
									output("Look for enemy by name: ");
									rawoutput("<select name='target'>");
									while ($row=db_fetch_assoc($names[0])) {
											rawoutput("<option value='".$row['acctid']."'>".$row['login']."</option>");
									}
									rawoutput("</select>");
									rawoutput("<input type='submit' class='button' value='$submit'></from><br><br>");
							}
					}
					break;
					
				default:
				output("`kYou are greeted by %s`k, the caretaker of the local private fighting zone area.`n`nA sign notifies you that fights you start will cost you a `^fee of %s gold pieces`k for the repair afterwards, the local tax and more luxury for a very special girl.`n`n",$name,$goldcost);
				output("%s`k explains, \"`lThe arena you rent is not charged per day, but you should set up an amount of time you can do the fight. There are no refunds for fights where one or all fighters did not participate. The time limit is a hard limit, after that, the fight will be put to an end without another action.`n`nAdditionally, your fight is available a certain time in an archive where you can view it afterwards.`k\"",$name);
				addnav("Invite somebody",$link."op=invite&subop=search");
			}
			break;
			
		case "view":
			addnav("Refresh",$link."op=view");
			//specific fight chosen
			$sql="SELECT a.*,a.challenger as cacctid, a.opponent as oacctid,b.name as challenger,c.name as opponent,a.zonetype as zonetype from $prefb as a inner join $preac as b on a.challenger=b.acctid inner join $preac as c on a.opponent=c.acctid WHERE a.battleid=$id;";
			$result=db_query($sql);
			$row=db_fetch_assoc($result);
			if ($row==false) {
				output("Error 0x01, notify your admin about battle id %s!",$id);
				break;
			}

			output("`c`b`\$%s   VS    `\$%s`b`c`n`n",$row['challenger'],$row['opponent']);
			
			invitationzones_desc($row['zonetype']);
			
			$section="fzone-".$id;
			viewcommentary($section,$message,25,$talkline,"module-invitationzones",true);
			break;		
			
		case "spectate":

			if ($id==0) {
				output("`b`i`c`!Fighting Zone`c`i`b`n`n");
				$sql="SELECT a.battleid,b.name as challenger,c.name as opponent,DATE_FORMAT(a.date,'%W, %M %D %Y') as olddate, DATE_FORMAT(a.date,'%h:%i %p GMT+1') as time from $prefb as a inner join $preac as b on a.challenger=b.acctid inner join $preac as c on a.opponent=c.acctid WHERE (a.opponent=".$u['acctid']." OR a.challenger=".$u['acctid'].") AND DATE_ADD(date, INTERVAL length HOUR)>=NOW() ORDER BY a.date asc;";
				$result=db_query($sql);
				addnav("Private Active Fights");
				while ($row=db_fetch_assoc($result)){
					$date=$row['olddate'];
					if ($olddate!=$date) {
						addnav_notl($date);
						$date=$olddate;
					}
					addnav(array("%s`4 VS %s`4 (%s)",$row['challenger'],$row['opponent'],$row['time']),$link."op=spectate&battleid=".((int)$row['battleid']));
				}			
			
				$sql="SELECT a.battleid,b.name as challenger,c.name as opponent from $prefb as a inner join $preac as b on a.challenger=b.acctid inner join $preac as c on a.opponent=c.acctid WHERE a.spectators!=0 AND NOT (a.opponent=".$u['acctid']." OR a.challenger=".$u['acctid'].")AND DATE_ADD(date, INTERVAL length HOUR)>=NOW();";
				$result=db_query($sql);
				addnav("Active Fights");
				while ($row=db_fetch_assoc($result)){
					addnav(array("%s`4 VS %s`4",$row['challenger'],$row['opponent']),$link."op=spectate&battleid=".((int)$row['battleid']));
				}
				
				$sql="SELECT a.battleid,b.name as challenger,c.name as opponent,DATE_FORMAT(a.date,'%W, %M %D %Y') as olddate, DATE_FORMAT(a.date,'%h:%i %p GMT+1') as time from $prefb as a inner join $preac as b on a.challenger=b.acctid inner join $preac as c on a.opponent=c.acctid WHERE a.spectators=0 AND (a.opponent=".$u['acctid']." OR a.challenger=".$u['acctid'].") AND DATE_ADD(date, INTERVAL length HOUR)<NOW() ORDER BY a.date asc;";
				$result=db_query($sql);
				addnav("Private Archived Fights");
				addnav("---","");
				while ($row=db_fetch_assoc($result)){
					$date=$row['olddate'];
					if ($olddate!=$date) {
						addnav_notl($date);
						$date=$olddate;
					}
					addnav(array("%s`4 VS %s`4 (%s)",$row['challenger'],$row['opponent'],$row['time']),$link."op=view&battleid=".((int)$row['battleid']));
				}
				
				$sql="SELECT a.battleid,b.name as challenger,c.name as opponent,DATE_FORMAT(a.date,'%W, %M %D %Y') as olddate, DATE_FORMAT(a.date,'%h:%i %p GMT+1') as time from $prefb as a inner join $preac as b on a.challenger=b.acctid inner join $preac as c on a.opponent=c.acctid WHERE a.spectators!=0 AND DATE_ADD(date, INTERVAL length HOUR)<NOW() ORDER BY a.date asc;";
				$result=db_query($sql);
				addnav("Archived Fights");
				addnav("---","");
				while ($row=db_fetch_assoc($result)){
					$date=$row['olddate'];
					if ($olddate!=$date) {
						addnav_notl($date);
						$date=$olddate;
					}
					addnav(array("%s`4 VS %s`4 (%s)",$row['challenger'],$row['opponent'],$row['time']),$link."op=view&battleid=".((int)$row['battleid']));
				}
				
				break;
			}
			addnav("Refresh",$link."op=spectate");
			//specific fight chosen
			$sql="SELECT a.*,a.challenger as cacctid, a.opponent as oacctid,b.name as challenger,c.name as opponent,a.zonetype as zonetype from $prefb as a inner join $preac as b on a.challenger=b.acctid inner join $preac as c on a.opponent=c.acctid WHERE a.battleid=$id;";
			$result=db_query($sql);
			$row=db_fetch_assoc($result);
			if ($row==false) {
				output("Error 0x01, notify your admin about battle id %s!",$id);
				break;
			}

			if ($row['individualname']!='') page_header(array("%s",str_replace("%","%%",sanitize($row['individualname'])))); // prevent %s and such
			$match=date("Y-m-d H:i:s",strtotime("- ".$row['length']." days"));
			$timeout=strtotime($row['date'])-strtotime($match); //seconds
			$minutes=floor($timeout/60); //minutes
			$seconds=$timeout%60;

			$fighter=(($u['acctid']==$row['cacctid'] || $u['acctid']==$row['oacctid'])?true:false);

			if ($row['individualname']!='') output("`b`i`c`!%s`c`i`b`n`n",$row['individualname']);
				else output("`b`i`c`!Fighting Zone`c`i`b`n`n");
			output("`c`b`\$%s   VS    `\$%s`b`c`n`n",$row['challenger'],$row['opponent']);
			
			
			invitationzones_desc($row['zonetype']);
			
			$section="fzone-".$id;
			if ($fighter) {
				$message="Shout your actions:";
				$talkline="shouts";
			} else {
				$message="Comment on the fight:";
				$talkline="comments";				
			}
			$post=(($row['spectatorcanpost'] || $fighter)?true:false);

			addnav ("Settings");
			$yes=translate_inline("Yes");
			$no=translate_inline("No");
			addnav(array("Spectators allowed: %s",($row['spectators']?$yes:$no)),"");
			addnav(array("Spectators can post: %s",($row['spectatorcanpost']?$yes:$no)),"");
			
			
			if (date("Y-m-d H:i:s")<$row['date']) {
				$match=date("Y-m-d H:i:s");
				$timeout=strtotime($row['date'])-strtotime($match); //seconds
				$minutes=floor($timeout/60); //minutes			
				$hours=floor($minutes/60);
				$minutes-=$hours*60;
				$seconds=$timeout%60;
				viewcommentary($section,$message,25,$talkline,"module-invitationzones",true);
				output("`n`n`jWait until the scheduled time has arrived! Just %s hour(s), %s minute(s) and %s second(s).",$hours,$minutes,$seconds);
				break;
			} elseif ($timeout<0) {
				//fight over
				viewcommentary($section,$message,25,$talkline,"module-invitationzones",true);
				output("`n`n`jTime is up! The fight is closed.");
				break;
			} else {
				//fight due
				$roll=(int)httppost('roll');
				if ($roll>1 && httppost('rolldie')!='') {
					$throw=e_rand(1,$roll);
					$text=sprintf_translate("/me`l rolled a 1d%s: `\$%s",$roll,$throw);
					injectrawcomment($section,$u['acctid'],$text);
				}
				addcommentary();	
				viewcommentary($section,$message,25,$talkline,"module-invitationzones",!$post);
				//fight control
				$dice=array(2,3,4,6,8,10,12,20);
				rawoutput("<form action='".$link."op=spectate' method='POST'><table style=''><tr><td colspan='".(count($dice)+1)."'>");
				addnav("",$link."op=spectate");
				output("Roll a die");
				rawoutput("</td></tr>");
				foreach ($dice as $die) {
					rawoutput("<td><input type='radio' name='roll' value='".$die."' ".($die==$roll?'checked':'').">1d".$die."</td>");
				}
				$submit=translate_inline("Roll");
				rawoutput("<td><input type='submit' class='button' name='rolldie' value='$submit'></td></tr></table></form>");
			}
			$m=translate_inline($minutes==1?"minute":"minutes");
			$s=translate_inline($seconds==1?"second":"seconds");
			output("`n`n`jZone open for another `\$%s %s`j and `\$%s %s!",$minutes,$m,$seconds,$s);	
			break;
		default:
			output("`kYou see the private arena ground for the invitational battles before you.");
			addnav("Invitational Battles");
			addnav("Invite for Battle","runmodule.php?module=invitationzones&op=invite");
			addnav("Browse fights","runmodule.php?module=invitationzones&op=spectate");
	}
	page_footer();
}

function invitationzones_desc($zone) {
	require_once("modules/addimages/addimages_func.php");
		switch ($zone) {
			case 1:
				addimage("fightingzone/desert.gif");
				output("`2Desert - `^You walk in with your Shinobi tools and weapons ready and looking for a fight.  You look around and notice that this zone is completely barren and covered entirely in sand with large gusts of `4wind `^frequently passing through.");
				break;
			case 2:
				addimage("fightingzone/wet.gif");
				output("`2Wetlands - `^You walk in with your Shinobi tools and weapons ready and looking for a fight.  You walk into the zone and are immediately berated by gusts of `!rain `^and `4wind; `^you notice that the nearby lakes and ponds of the zone have flooded over causing puddles all over the zone.");
				break;
			case 3:
				addimage("fightingzone/lightning.gif");
				output("`2Lightning Plains - `^You walk in with your Shinobi tools and weapons ready and looking for a fight.  You walk in and look up at the `)cloudy `^sky. `nYou can see flashes of `t lightning `^high up in the clouds and you can hear the rumbling of thunder rather close by.");
				break;
			case 4:
				addimage("fightingzone/fire.gif");
				output("`2Fire Valley - `^You walk in with your Shinobi tools and weapons ready and looking for a fight.  In the distance you can see that there are several `4v`\$olcanoes `^a long distance away. `nSeveral of them are spewing out large amounts of ash and lava littering the zone with hot fire, and many tall trees are ablaze in this zone.");
				break;
			case 5:
				addimage("fightingzone/green.gif");
				output("`2Green Lands - `^You walk in with your Shinobi tools and weapons ready and looking for a fight.  You look around and notice you stand amidst a vast `@forest `^of large tall trees many of which block out the sun creating large areas of `~shade.`^");
				break;
			case 6:
				addimage("fightingzone/plains.gif");
				output("`2Plains - `^You walk in with your Shinobi tools and weapons ready, looking for a fight.`n You notice you're in an almost `)empty `^field, filled with tall `2grass `^where large gusts of wind blow through the zone. `n`nIt seems shifting through the grass and blowing past you.");
				break;
			case 7:
				addimage("fightingzone/ice.gif");
				output("`2Icelands - `^You walk in with your Shinobi tools and weapons ready and looking for a fight. You enter the zone and immediately a `Jchill `^is sent up your spine from the sheer `1cold `^of the room.  You notice that it is snowing heavily and a few inches of snow covers the zone's ground.");
				break;
			case 8:
				addimage("fightingzone/death.gif");
				output("`2Death Valley - `^You walk in with your Shinobi tools and weapons ready and looking for a fight.  Steep canyons and gigantic boulders adorn this zone, at the bottom of the canyons are vast amounts also in the zone is a `~dark `^looking cave that once within its depths is completely dark, many have `)lost `^their way and never returned with stalagmite rocks covering the cave floor.");
				break;
			
		}
		output_notl("`n`n");

}


?>
