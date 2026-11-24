<?php
use Lotgd\MySQL\Database;
use Lotgd\Security\Csrf;

function advertisingtracker_getmoduleinfo(){
	$info = array(
		"name"=>"Advertising Tracker (inGame)",
		"version"=>"1.1",
		"author"=>"`2Oliver Brendel",
		"category"=>"Administrative",
		"download"=>"",
		"settings"=>array(
					"Ad Tracker - Preferences, title",
					"who"=>"Enter here separated by comma the acctid of the admins who should be excluded (secure mail), text|1,7",
					"searches"=>"What other keywords to you want to search for in mails (separate by comma!),text|",
					),
	);
	return $info;
}

function advertisingtracker_install(){
	//table setup for 1.1.1 & 1.1.0, if you use a lower version, you will encounter errors -> remove some fields
	module_addhook("superuser");
	$abusemail=array(
		'messageid'=>array('name'=>'messageid','type'=>'int(11) unsigned','extra'=>'auto_increment'),
		'msgfrom'=>array('name'=>'msgfrom', 'type'=>'int(11) unsigned', 'default'=>'0'),
		'msgto'=>array('name'=>'msgto', 'type'=>'int(11) unsigned', 'default'=>'0'	),
		'subject'=>array('name'=>'subject', 'type'=>'varchar(255)'),
		'body'=>array('name'=>'body', 'type'=>'text'),
		'sent'=>array('name'=>'sent', 'type'=>'datetime', 'default'=>DATETIME_DATEMIN),
		'seen'=>array('name'=>'seen', 'type'=>'tinyint(1)', 'default'=>'0'),
		'key-PRIMARY'=>array('name'=>'PRIMARY','type'=>'primary key','unique'=>'1','columns'=>'messageid'),
		'key-msgto'=>array('name'=>'msgto', 'type'=>'key', 'columns'=>'msgto'),
		'key-seen'=>array('name'=>'seen', 'type'=>'key', 'columns'=>'seen')
		);
	$abuseuser=array(
		'acctid'=>array('name'=>'acctid', 'type'=>'int(11) unsigned', 'extra'=>'auto_increment'),
		'name'=>array('name'=>'name', 'type'=>'varchar(100)'),
		'sex'=>array('name'=>'sex', 'type'=>'tinyint(4) unsigned', 'default'=>'0'),
		'level'=>array('name'=>'level', 'type'=>'int(11) unsigned', 'default'=>'1'),
		'laston'=>array('name'=>'laston', 'type'=>'datetime', 'default'=>DATETIME_DATEMIN),
		'superuser'=>array('name'=>'superuser', 'type'=>'int(11) unsigned', 'default'=>'1'),
		'login'=>array('name'=>'login', 'type'=>'varchar(50)'),
		'lasthit'=>array('name'=>'lasthit', 'type'=>'datetime', 'default'=>DATETIME_DATEMIN),
		'dragonkills'=>array('name'=>'dragonkills', 'type'=>'int(11) unsigned', 'default'=>'0'),
		'locked'=>array('name'=>'locked', 'type'=>'tinyint(4) unsigned', 'default'=>'0'),
				'lastip'=>array('name'=>'lastip', 'type'=>'varchar(40)'),
		'uniqueid'=>array('name'=>'uniqueid', 'type'=>'varchar(32)', 'null'=>'1'),
		'emailaddress'=>array('name'=>'emailaddress', 'type'=>'varchar(128)'),
		'donation'=>array('name'=>'donation', 'type'=>'int(11) unsigned', 'default'=>'0'),
		'donationspent'=>array('name'=>'donationspent', 'type'=>'int(11) unsigned', 'default'=>'0'),
		'ctitle'=>array('name'=>'ctitle', 'type'=>'varchar(25)'),
		'regdate'=>array('name'=>'regdate','type'=>'datetime','default'=>DATETIME_DATEMIN),
		'clanjoindate'=>array('name'=>'clanjoindate','type'=>'datetime','default'=>DATETIME_DATEMIN),
		'key-PRIMARY'=>array('name'=>'PRIMARY','type'=>'primary key','unique'=>'1','columns'=>'acctid'),
		);
	require_once("lib/tabledescriptor.php");
	advertisingtracker_normalize_archive_dates(db_prefix("abusemail"), $abusemail);
	advertisingtracker_normalize_archive_dates(db_prefix("abuseuser"), $abuseuser);
	synctable(db_prefix("abusemail"), $abusemail, true);
	// Without nodrop, so an older archive loses the columns no longer kept (password, emailvalidation).
	synctable(db_prefix("abuseuser"), $abuseuser);
	return true;
}

/**
 * Upgrade legacy archive dates before a strict-mode ALTER TABLE validates them.
 * Compare as text: comparing a datetime to a zero-date literal can itself fail.
 */
function advertisingtracker_normalize_archive_dates(string $tablename, array $descriptor): void
{
    if (!db_table_exists($tablename)) {
        return;
    }

    $connection = Database::getDoctrineConnection();
    $table = $connection->quoteIdentifier($tablename);
    $columns = array_flip($connection->fetchFirstColumn("SHOW COLUMNS FROM $table"));
    $updates = array();
    $conditions = array();
    foreach ($descriptor as $name => $definition) {
        if (($definition['type'] ?? '') !== 'datetime' || !isset($columns[$name])) {
            continue;
        }
        $column = $connection->quoteIdentifier($name);
        $condition = "LEFT(CAST($column AS CHAR), 10) = :zeroDate";
        $updates[] = "$column = CASE WHEN $condition THEN :minimumDate ELSE $column END";
        $conditions[] = $condition;
    }

    if ($updates) {
        $connection->executeStatement(
            "UPDATE $table SET " . implode(', ', $updates) . " WHERE " . implode(' OR ', $conditions),
            ['zeroDate' => '0000-00-00', 'minimumDate' => DATETIME_DATEMIN]
        );
    }
}

function advertisingtracker_uninstall(){
	return true;
}

function advertisingtracker_dohook($hookname,$args){
	global $session;
	switch ($hookname) {
	case "superuser":
		if (($session['user']['superuser'] & SU_EDIT_COMMENTS)== SU_EDIT_COMMENTS) {
			addnav("Mechanics");
			addnav("Advertising Tracker","runmodule.php?module=advertisingtracker");
		}

	break;
	}
	return $args;
}

/**
 * Whether the request is the posted wipe confirmation with a valid token.
 * Lotgd\Security\Csrf came with the core of September 2026; an older core gets the POST check alone.
 */
function advertisingtracker_validpost(){
	if (!class_exists(Csrf::class)) {
		return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
	}
	return Csrf::validatePostRequest("module:advertisingtracker");
}

/**
 * Build the "who" exclusion list (account ids configured in the module setting) from the setting text.
 * Every entry is cast to int, so the list is safe to inline into an IN (...) clause.
 */
function advertisingtracker_wholist(){
	return implode(',',array_map('intval',explode(',',(string)get_module_setting('who'))));
}

/**
 * Build the mail filter shared by the mail tracker views.
 *
 * @param string $mail   prefixed mail table name
 * @param mixed  $track  search text from httpget() (slashed)
 * @param mixed  $length max body length from httpget()
 * @param array  $params receives the bound parameters
 */
function advertisingtracker_mailcond($mail,$track,$length,array &$params){
	if (get_module_setting('who')!='') {
		$who=advertisingtracker_wholist();
		$cond=" AND $mail.msgfrom NOT IN ($who) ";
		$cond.=" AND $mail.msgto NOT IN ($who) ";
	} else $cond='';
	// httpget() values are addslashes()d; the old query used the original text.
	if ($track!='') {
		$cond.=" AND body LIKE :track";
		$params['track']='%'.stripslashes((string)$track).'%';
	}
	if ($length!='') $cond.= " AND LENGTH(body)<=".(int)$length;
	return $cond;
}

function advertisingtracker_run(){
	global $session;
	$op=httpget('op');
	$conn=Database::getDoctrineConnection();
	// The navs below are shown by rank; require the same rank for the action itself.
	$needed=SU_EDIT_USERS;
	if ($op=="") $needed=SU_EDIT_COMMENTS;
	if ($op=="wipe" || $op=="presearchmail" || $op=="searchmail") $needed=SU_MEGAUSER;
	if (($session['user']['superuser'] & $needed)!=$needed) {
		redirect("superuser.php");
	}
	require_once("lib/superusernav.php");
	superusernav();
	page_header("Advertising Tracker");
	if (($session['user']['superuser'] & SU_EDIT_USERS)== SU_EDIT_USERS) {
		addnav("Mechanics");
		addnav("Commentary Tracker","runmodule.php?module=advertisingtracker&op=commentary");
		addnav("Mail Tracker","runmodule.php?module=advertisingtracker&op=mail");
		addnav("Mail Tracker (<50 chars)","runmodule.php?module=advertisingtracker&op=mail&length=50");
		addnav("Mail Tracker (<100 chars)","runmodule.php?module=advertisingtracker&op=mail&length=100");
		addnav("Mail Tracker (<150 chars)","runmodule.php?module=advertisingtracker&op=mail&length=150");
		addnav("Mail Tracker (<200 chars)","runmodule.php?module=advertisingtracker&op=mail&length=200");
		addnav("Mail Tracker (<250 chars)","runmodule.php?module=advertisingtracker&op=mail&length=250");
		addnav("Mail Tracker (Lotgd)","runmodule.php?module=advertisingtracker&op=mail&track=lotgd");
		addnav("Mail Tracker (Logd)","runmodule.php?module=advertisingtracker&op=mail&track=logd");
		$navadd=get_module_setting('searches');
		$navs=explode(",",$navadd);
		foreach ($navs as $item) {
			addnav(array("Mail Tracker (%s)",$item),"runmodule.php?module=advertisingtracker&op=mail&track=".$item);
		}
		addnav ("Automatic URL Tracks");
		addnav("Mail Tracker","runmodule.php?module=advertisingtracker&op=automail");
		addnav("Bio Tracker","runmodule.php?module=advertisingtracker&op=autobio");
		if (is_module_active("additionalbioinfos")) addnav("Additional Bioinfo Tracker","runmodule.php?module=advertisingtracker&op=automaddbioinfo");
		if (is_module_active("perws")) addnav("Personal Webpage Tracker","runmodule.php?module=advertisingtracker&op=autopersonalpage");
		if (($session['user']['superuser'] & SU_MEGAUSER)== SU_MEGAUSER) {
			addnav("User Actions");
			addnav("Wipe One Off","runmodule.php?module=advertisingtracker&op=wipe");
		}
		addnav("Commentary Searches");
		addnav("Search Comments by User","runmodule.php?module=advertisingtracker&op=presearch&track=user");
		addnav("Search Comments by String","runmodule.php?module=advertisingtracker&op=presearch&track=string");
		if (($session['user']['superuser'] & SU_MEGAUSER)== SU_MEGAUSER) addnav("Search Mail by User"
,"runmodule.php?module=advertisingtracker&op=presearchmail&track=user");

		//content search not made deliberately
	}
	switch ($op) {
		case "wipe":
			$action=httpget('action');
			// Wiping needs the confirmation form's POST and token; a bare link only shows the confirmation.
			if ($action=="reallywipe" && !advertisingtracker_validpost()) $action="confirm";
			output("`4You are now going to:`n`c`\$->wipe a user from the game`n->insert mails to&from him from the mail into the abusemail table`n->log his account info into the abuseuser table`n->if active, the email will be moved to the blocked list on char creation`n`c`n");
			output("`4Think carefully before you do this! The char gets deleted, so the char restorer (if installed) will work. Though, it's a bad thing. ONLY do this if you have an absolute jerk on your server you want to get off immediately and still have his actions logged.`n`n");
			switch ($action) {
				case "reallywipe":
					$target=(int)httpget('target');
					$account=$conn->fetchAssociative("SELECT * FROM ".db_prefix('accounts')." WHERE acctid = :target LIMIT 1",array('target'=>$target));
					$conn->executeStatement("INSERT INTO ".db_prefix('abusemail')." SELECT * FROM ".db_prefix('mail')." WHERE msgto = :target OR msgfrom = :target2",array('target'=>$target,'target2'=>$target));
					$number=$conn->executeStatement("DELETE FROM ".db_prefix('mail')." WHERE msgto = :target OR msgfrom = :target2",array('target'=>$target,'target2'=>$target));
					$result=$conn->fetchAllAssociative("DESCRIBE ".db_prefix('abuseuser'));
					$array=array();
					foreach ($result as $row) {
						$array[]=$row['Field'];
					}
					// The account's own fields (bio, title, ...) are player text, so they are bound, not pasted.
					$abuserow=array();
					foreach ($array as $field) {
						// Never archived. Should the install have failed, the old columns are still there and need a value.
						$abuserow[$field]=in_array($field, array('password', 'emailvalidation'), true) ? '' : ($account[$field] ?? '');
					}
					Database::getDoctrineConnection()->insert(db_prefix('abuseuser'), $abuserow); //insert infos
					require_once("lib/charcleanup.php");
					char_cleanup($target,"CHAR_DELETE_MANUAL"); //finish prefs
					$conn->executeStatement("DELETE FROM ".db_prefix('accounts')." WHERE acctid = :target LIMIT 1",array('target'=>$target)); //gone
					output("`4User %s`4 has been deleted, %s mails have been moved to the abusemail table.`n`n",$account['name'],$number);
					
					//automatically revoke him to make a new char by email if the blocker is installed
					
					if (is_module_active("blocker")) {
						$emails=get_module_setting('blockedmails',"blocker");
						if ($account['emailaddress']>"" && strpos($emails,$account['emailaddress'])===false) {
							if ($emails>"") {
								set_module_setting('blockedmails',$emails.",".$account['emailaddress'],"blocker");
							} else set_module_setting('blockedmails',$account['emailaddress'],"blocker");
							output("`4Added the email %s to the block list for char creation!`n`n",$account['emailaddress']);
						} else {
							output("`4The email %s was already on your blocker list.",$account['emailaddress']);
						}
					}
					
					break;
					
				case "confirm":
					$target=(int)httpget('target');
					$row=$conn->fetchAssociative("SELECT * FROM ".db_prefix('accounts')." WHERE acctid = :target LIMIT 1",array('target'=>$target));
					output("`\$Do you really want to delete the user %s`\$, record his mails and so on?`n(Note: Mails displayed below to/from him)`%`n`n",$row['name']);
					$wipeurl="runmodule.php?module=advertisingtracker&op=wipe&action=reallywipe&target=$target";
					$wipelabel=sprintf_translate("Wipe user %s",full_sanitize($row['name']));
					rawoutput("<form action='$wipeurl' method='post'>"
						.(class_exists(Csrf::class) ? Csrf::hiddenField("module:advertisingtracker") : "")
						."<input type='submit' class='button' value='".htmlspecialchars($wipelabel, ENT_QUOTES)."'></form><br>");
					addnav("",$wipeurl);
					
					$ac=db_prefix("accounts");
					$mail=db_prefix("mail");
					$sql="SELECT $ac.name AS name,$mail.body AS body,$mail.sent AS date FROM $mail INNER JOIN $ac ON $mail.msgfrom=$ac.acctid WHERE msgto = :target OR msgfrom = :target2 ORDER BY $mail.messageid DESC LIMIT 200"; //acctid 7 = neji
					$result = $conn->fetchAllAssociative($sql,array('target'=>$target,'target2'=>$target));
					$date=translate_inline("Postdate");
					$author=translate_inline("Author");
					$body=translate_inline("Message");
					rawoutput("<table border=0 cellpadding=2 cellspacing=1 bgcolor='#999999' align=center>");
					rawoutput("<tr class='trhead' height=30px><td><b>$date</b></td><td><b>$author</b></td><td><b>$body</b></td></tr>");
					$class="trlight";
					foreach ($result as $row) {
						$class=($class=='trlight'?'trdark':'trlight');
						rawoutput("<tr height=30px class='$class'>");
						rawoutput("<td>");
						output_notl($row['date']);
						rawoutput("</td><td>");
						output_notl($row['name']);
						rawoutput("</td><td>");
						output_notl($row['body']);
						rawoutput("</td></tr>");

					}
					rawoutput("</table>");
					break;
				case "search":
					output("`4Pick the user -click the name- (still confirmation to wipe him after the click on the following page):`n`n");
					$class="trlight";
					$target=httppost('target');
					// httppost() value is addslashes()d; the old query used the original text.
					$sql="SELECT name, login, acctid FROM ".db_prefix('accounts')." WHERE (name LIKE :target OR login like :target2)";
					$result=$conn->fetchAllAssociative($sql,array('target'=>stripslashes((string)$target),'target2'=>stripslashes((string)$target)));
					$acctid=translate_inline("Acctid");
					$name=translate_inline("Name");
					$login=translate_inline("Login");
					rawoutput("<table border=0 cellpadding=2 cellspacing=1 bgcolor='#999999' align=center>");
					rawoutput("<tr class='trhead' height=30px><td><b>$acctid</b></td><td><b>$name</b></td><td><b>$login</b></td></tr>");
					foreach ($result as $row) {
						$class=($class=='trlight'?'trdark':'trlight');
						rawoutput("<tr height=30px class='$class'>");
						rawoutput("<td>");
						output_notl($row['acctid']);
						rawoutput("</td><td>");
						rawoutput("<a href='runmodule.php?module=advertisingtracker&op=wipe&action=confirm&target={$row['acctid']}'>".appoencode($row['name'])."</a>");
						addnav("","runmodule.php?module=advertisingtracker&op=wipe&action=confirm&target={$row['acctid']}");
						rawoutput("</td><td>");
						output_notl($row['login']);
						rawoutput("</td></tr>");

					}
					rawoutput("</table>");
				break;
				default:
					$submit=translate_inline("Submit");
					output("`2Remember to use % as placeholders for any amount of symbols! Else it gets exact matches!`n`n");
					output("`2Please enter what you look for:`n`n");
					rawoutput("<form action='runmodule.php?module=advertisingtracker&op=wipe&action=search' method='post'>");
					addnav("","runmodule.php?module=advertisingtracker&op=wipe&action=search");
					switch($track) {
						case "user":
							output("Loginname:`n");
							break;
						case "string":
							output("Commentcontent:`n");
							break;
					}
					rawoutput("<input type='input' name='target'>");
					rawoutput("<br><br><input type='submit' value='$submit'>");
					rawoutput("</form>");
				break;
			}
			break;
		case "mail":
			$track=httpget('track');
			$length=httpget('length');
			addnav("Actions");
			addnav("Refresh","runmodule.php?module=advertisingtracker&op=mail&track=$track");
			$ac=db_prefix("accounts");
			$mail=db_prefix("mail");
			$params=array();
			$cond=advertisingtracker_mailcond($mail,$track,$length,$params);
			$sql="SELECT $ac.name AS name,$mail.body AS body,$mail.sent AS date FROM $mail INNER JOIN $ac ON $mail.msgfrom=$ac.acctid WHERE (body like '%http://%' OR body like '%www.%' OR body like '%.com%') $cond ORDER BY $mail.messageid DESC LIMIT 200"; //acctid 7 = neji
			$result = $conn->fetchAllAssociative($sql,$params);
			$date=translate_inline("Postdate");
			$author=translate_inline("Author");
			$body=translate_inline("Message");
			rawoutput("<table border=0 cellpadding=2 cellspacing=1 bgcolor='#999999' align=center>");
			rawoutput("<tr class='trhead' height=30px><td><b>$date</b></td><td><b>$author</b></td><td><b>$body</b></td></tr>");
			$class="trlight";
			$col="`&"; //not used
			output("Since bans here should be done either permanently or people normally only warned, no ban options are offered here`n`n");
			foreach ($result as $row) {
				$class=($class=='trlight'?'trdark':'trlight');
				rawoutput("<tr height=30px class='$class'>");
				rawoutput("<td>");
				output_notl($row['date']);
				rawoutput("</td><td>");
				output_notl($row['name']);
				rawoutput("</td><td>");
				output_notl($row['body']);
				rawoutput("</td></tr>");

			}
			rawoutput("</table>");
			break;
		case "autopersonalpage":
			$track=httpget('track');
			$length=httpget('length');
			$limit=1000;
			addnav("Actions");
			addnav("Refresh","runmodule.php?module=advertisingtracker&op=autobio&track=$track");
			$ac=db_prefix("accounts");
			$mp=db_prefix("module_userprefs");
			if (get_module_setting('who')!='') {
				$cond="AND acctid NOT IN (".advertisingtracker_wholist().") ";
			} else $cond='';
			$sql="SELECT $ac.name AS name,$mp.value AS bio FROM $ac INNER JOIN $mp ON $ac.acctid=$mp.userid WHERE (bio !='') AND modulename='perws' AND setting='user_link' $cond ORDER BY $ac.acctid DESC LIMIT $limit"; 
			$result = $conn->fetchAllAssociative($sql);debug($sql);
			$author=translate_inline("Author");
			$body=translate_inline("Message");
			rawoutput("<table border=0 cellpadding=2 cellspacing=1 bgcolor='#999999' align=center>");
			rawoutput("<tr class='trhead' height=30px><td></td><td><b>$author</b></td><td><b>$body</b></td></tr>");
			$class="trlight";
			$col="`&"; //not used
			output("Now checking if we have accessible lotgd games here...`n`n");
			foreach ($result as $row) {
				$content=advertisingtracker_geturl($row['bio']);
				$check=advertisingtracker_checklotgd($content);
				if ($check==0) continue;
				//debug("Lotgd found!");
				$class=($class=='trlight'?'trdark':'trlight');
				rawoutput("<tr height=30px class='$class'><td>");
				output_notl($row['name']);
				rawoutput("</td><td>");
				output_notl(str_replace($content,"`$`b$content`b`0",$row['bio']));
				rawoutput("</td></tr>");

			}
			rawoutput("</table>");
			break;				
		case "automaddbioinfo":
			$track=httpget('track');
			$length=httpget('length');
			$limit=1000;
			addnav("Actions");
			addnav("Refresh","runmodule.php?module=advertisingtracker&op=autobio&track=$track");
			$ac=db_prefix("accounts");
			$mp=db_prefix("module_userprefs");
			if (get_module_setting('who')!='') {
				$cond="AND acctid NOT IN (".advertisingtracker_wholist().") ";
			} else $cond='';
			$sql="SELECT $ac.name AS name,$mp.value AS bio FROM $ac INNER JOIN $mp ON $ac.acctid=$mp.userid WHERE (bio like '%http://%' OR bio like '%www.%' OR bio like '%.com%') AND modulename='additionalbioinfos' AND setting='user_additionalbioinfo' $cond ORDER BY $ac.acctid DESC LIMIT $limit"; 
			$result = $conn->fetchAllAssociative($sql);
			$author=translate_inline("Author");
			$body=translate_inline("Message");
			rawoutput("<table border=0 cellpadding=2 cellspacing=1 bgcolor='#999999' align=center>");
			rawoutput("<tr class='trhead' height=30px><td></td><td><b>$author</b></td><td><b>$body</b></td></tr>");
			$class="trlight";
			$col="`&"; //not used
			output("Now checking if we have accessible lotgd games here...`n`n");
			foreach ($result as $row) {
				$content=advertisingtracker_geturl($row['bio']);
				$check=advertisingtracker_checklotgd($content);
				if ($check==0) continue;
				//debug("Lotgd found!");
				$class=($class=='trlight'?'trdark':'trlight');
				rawoutput("<tr height=30px class='$class'><td>");
				output_notl($row['name']);
				rawoutput("</td><td>");
				output_notl(str_replace($content,"`$`b$content`b`0",$row['bio']));
				rawoutput("</td></tr>");

			}
			rawoutput("</table>");
			break;				
		case "autobio":
			$track=httpget('track');
			$length=httpget('length');
			$limit=1000;
			addnav("Actions");
			addnav("Refresh","runmodule.php?module=advertisingtracker&op=autobio&track=$track");
			$ac=db_prefix("accounts");
			
			if (get_module_setting('who')!='') {
				$cond="AND acctid NOT IN (".advertisingtracker_wholist().") ";
			} else $cond='';
			$sql="SELECT $ac.name AS name,$ac.bio AS bio FROM $ac WHERE (bio like '%http://%' OR bio like '%www.%' OR bio like '%.com%') $cond ORDER BY $ac.acctid DESC LIMIT $limit"; 
			$result = $conn->fetchAllAssociative($sql);
			$date=translate_inline("Postdate");
			$author=translate_inline("Author");
			$body=translate_inline("Message");
			rawoutput("<table border=0 cellpadding=2 cellspacing=1 bgcolor='#999999' align=center>");
			rawoutput("<tr class='trhead' height=30px><td></td><td><b>$author</b></td><td><b>$body</b></td></tr>");
			$class="trlight";
			$col="`&"; //not used
			output("Now checking if we have accessible lotgd games here...`n`n");
			foreach ($result as $row) {
				$content=advertisingtracker_geturl($row['bio']);
				$check=advertisingtracker_checklotgd($content);
				if ($check==0) continue;
				//debug("Lotgd found!");
				$class=($class=='trlight'?'trdark':'trlight');
				rawoutput("<tr height=30px class='$class'><td>");
				output_notl($row['name']);
				rawoutput("</td><td>");
				output_notl(str_replace($content,"`$`b$content`b`0",$row['bio']));
				rawoutput("</td></tr>");

			}
			rawoutput("</table>");
			break;					
		case "automail":
			$track=httpget('track');
			$length=httpget('length');
			addnav("Actions");
			addnav("Refresh","runmodule.php?module=advertisingtracker&op=automail&track=$track");
			$ac=db_prefix("accounts");
			$mail=db_prefix("mail");
			$params=array();
			$cond=advertisingtracker_mailcond($mail,$track,$length,$params);
			$sql="SELECT $ac.name AS name,$mail.body AS body,$mail.sent AS date FROM $mail INNER JOIN $ac ON $mail.msgfrom=$ac.acctid WHERE (body like '%http://%' OR body like '%www.%' OR body like '%.com%') $cond ORDER BY $mail.messageid DESC LIMIT 200"; //acctid 7 = neji
			$result = $conn->fetchAllAssociative($sql,$params);
			$date=translate_inline("Postdate");
			$author=translate_inline("Author");
			$body=translate_inline("Message");
			rawoutput("<table border=0 cellpadding=2 cellspacing=1 bgcolor='#999999' align=center>");
			rawoutput("<tr class='trhead' height=30px><td><b>$date</b></td><td><b>$author</b></td><td><b>$body</b></td></tr>");
			$class="trlight";
			$col="`&"; //not used
			output("Now checking if we have accessible lotgd games here...`n`n");
			foreach ($result as $row) {
				$content=advertisingtracker_geturl($row['body']);
				$check=advertisingtracker_checklotgd($content);
				if ($check==0) continue;
				//debug("Lotgd found!");
				$class=($class=='trlight'?'trdark':'trlight');
				rawoutput("<tr height=30px class='$class'><td>");
				output_notl($row['date']);
				rawoutput("</td><td>");
				output_notl($row['name']);
				rawoutput("</td><td>");
				output_notl(str_replace($content,"`$`b$content`b`0",$row['body']));
				rawoutput("</td></tr>");

			}
			rawoutput("</table>");
			break;			
		case "commentary":
			addnav("Actions");
			addnav("Refresh","runmodule.php?module=advertisingtracker&op=commentary");
			$ac=db_prefix("accounts");
			$cm=db_prefix("commentary");
			$sql="SELECT $ac.name AS name,$cm.section AS section, $cm.postdate AS postdate, $cm.comment AS comment FROM $cm INNER JOIN $ac ON $cm.author=$ac.acctid WHERE (comment like '%http://%' OR comment like '%www.%'  OR comment like '%.com%') AND section != 'AvatarVal' ORDER BY $cm.commentid DESC LIMIT 50";
			$result = $conn->fetchAllAssociative($sql);
			$date=translate_inline("Postdate");
			$author=translate_inline("Author");
			$section=translate_inline("Section");
			$comment=translate_inline("Comment");
			rawoutput("<table border=0 cellpadding=2 cellspacing=1 bgcolor='#999999' align=center>");
			rawoutput("<tr class='trhead' height=30px><td><b>$date</b></td><td><b>$author</b></td><td><b>$section</b></td><td><b>$comment</b></td></tr>");
			$class="trlight";
			$col="`&";
			output("Since bans here should be done either permanently or people normally only warned, no ban options are offered here`n`n");
			foreach ($result as $row) {
				$class=($class=='trlight'?'trdark':'trlight');
				rawoutput("<tr height=30px class='$class'>");
				rawoutput("<td>");
				output_notl($row['postdate']);
				rawoutput("</td><td>");
				output_notl($row['name']);
				rawoutput("</td><td>");
				output_notl($row['section']);
				rawoutput("</td><td>");
				output_notl($row['comment']);
				rawoutput("</td></tr>");

			}
			rawoutput("</table>");
			break;
		case "presearch":
			$track=httpget('track');
			$submit=translate_inline("Submit");
			output("`2Remember to use % as placeholders for any amount of symbols! Else it gets exact matches!`n`n");
			output("`2Please enter what you look for:`n`n");
			rawoutput("<form action='runmodule.php?module=advertisingtracker&op=search&track=$track' method='post'>");
			addnav("","runmodule.php?module=advertisingtracker&op=search&track=$track");
			switch($track) {
				case "user":
					output("Loginname:`n");
					break;
				case "string":
					output("Commentcontent:`n");
					break;
			}
			rawoutput("<input type='input' name='target'>");
			output("`n`nHow many results (limit clause):`n");
			rawoutput("<input type='input' name='limit' value='50'>");
			output("`n`nIn what commentary section (if left empty, all will be displayed):`n");
			rawoutput("<input type='input' name='section'>");
			rawoutput("<br><br><input type='submit' value='$submit'>");
			rawoutput("</form>");
			break;
		case "search":
			$track=httpget('track');
			$target=httppost('target');
			$limit=httppost('limit');
			$section=httppost('section');
			$ac=db_prefix("accounts");
			$cm=db_prefix("commentary");
			$params=array();
			if ($section=='') $searchsection='';
			else {
				$searchsection="AND $cm.section LIKE :section";
				// httppost() values are addslashes()d; the old query used the original text.
				$params['section']=stripslashes((string)$section);
			}
			if ($limit=='') $limit=50;
			$limit=(int)$limit;
			addnav("Actions");
			addnav("Clear Mask","runmodule.php?module=advertisingtracker&op=search&track=$track&target=$target");
			switch ($track) {
				case "user":
					$sql="SELECT $ac.name AS name,$cm.section AS section, $cm.postdate AS postdate, $cm.comment AS comment FROM $cm INNER JOIN $ac ON $cm.author=$ac.acctid WHERE $ac.login like :target $searchsection ORDER BY $cm.commentid DESC LIMIT $limit";
					$params['target']=stripslashes((string)$target);
					break;
				case "string":
					$sql="SELECT $ac.name AS name,$cm.section AS section, $cm.postdate AS postdate, $cm.comment AS comment FROM $cm INNER JOIN $ac ON $cm.author=$ac.acctid WHERE $cm.comment like :target $searchsection ORDER BY $cm.commentid DESC LIMIT $limit";
					$params['target']=stripslashes((string)$target);
					break;
			}

			// An unknown track left $sql undefined before (the query failed); show no rows instead.
			$result = isset($sql) ? $conn->fetchAllAssociative($sql,$params) : array();
			$date=translate_inline("Postdate");
			$author=translate_inline("Author");
			$section=translate_inline("Section");
			$comment=translate_inline("Comment");
			rawoutput("<table border=0 cellpadding=2 cellspacing=1 bgcolor='#999999' align=center>");
			rawoutput("<tr class='trhead' height=30px><td><b>$date</b></td><td><b>$author</b></td><td><b>$section</b></td><td><b>$comment</b></td></tr>");
			$class="trlight";
			$col="`&";
			output("Since bans here should be done either permanently or people normally only warned, no ban options are offered here`n`n");
			foreach ($result as $row) {
				$class=($class=='trlight'?'trdark':'trlight');
				rawoutput("<tr height=30px class='$class'>");
				rawoutput("<td>");
				output_notl($row['postdate']);
				rawoutput("</td><td>");
				output_notl($row['name']);
				rawoutput("</td><td>");
				output_notl($row['section']);
				rawoutput("</td><td>");
				output_notl($row['comment']);
				rawoutput("</td></tr>");

			}
			rawoutput("</table>");
		case "presearchmail":
			$track=httpget('track');
			$submit=translate_inline("Submit");
			output("`2Remember to use % as placeholders for any amount of symbols! Else it gets exact matches!`n`n");
			output("`2Please enter what you look for:`n`n");
			rawoutput("<form action='runmodule.php?module=advertisingtracker&op=searchmail&track=$track' method='post'>");
			addnav("","runmodule.php?module=advertisingtracker&op=searchmail&track=$track");
			switch($track) {
				case "user":
					output("Loginname:`n");
					break;
				case "string":
					output("Commentcontent:`n");
					break;
			}
			rawoutput("<input type='input' name='target'>");
			output("`nMailpartner (leave empty if not wanted:`n");
			rawoutput("<input type='input' name='target2'>");
			output("`n`nHow many results (limit clause):`n");
			rawoutput("<input type='input' name='limit' value='50'>");
			if (is_module_active('outbox')) {
				output("`n`nSearch the outbox instead of the mailbox?:`n");
				rawoutput("<input type='checkbox' name='boxcheck'>");
			}
			rawoutput("<br><br><input type='submit' value='$submit'>");
			rawoutput("</form>");
			break;
		case "searchmail":
			$track=httpget('track');
			$target=httppost('target');
			$target2=httppost('target2');
			$boxcheck=httppost('boxcheck');
			$limit=httppost('limit');
			$ac=db_prefix("accounts");
			if ($boxcheck) {
				$mail=db_prefix("mailoutbox");
			} else {
				$mail=db_prefix("mail");
			}
			$params=array();
			if (!isset($section) || $section=='') $searchsection='';
			else {
				$searchsection="AND $mail.section LIKE :section";
				$params['section']=stripslashes((string)$section);
			}
			if ($limit=='') $limit=50;
			$limit=(int)$limit;
			addnav("Actions");
			addnav("Clear Mask","runmodule.php?module=advertisingtracker&op=search&track=$track&target=$target");
			// httppost() values are addslashes()d; the old queries used the original text.
			$row=$conn->fetchAssociative("SELECT acctid,name FROM ".db_prefix('accounts')." WHERE login LIKE :target",array('target'=>stripslashes((string)$target)));
			if (((int)($row['acctid'] ?? 0)) == 0) {
				output("`2`cNo users found!`c");
				break;
			} else {
				$acctid=(int) $row['acctid'];
				output("`2You are now viewing the outgoing mails of %s`2:`n`n",$row['name']);
			}
			
			switch ($track) {
				case "user":
					if ($target2) {
						$row=$conn->fetchAssociative("SELECT acctid FROM ".db_prefix('accounts')." WHERE login LIKE :target2",array('target2'=>stripslashes((string)$target2)));
						$targetadd=" AND $mail.msgto = :msgto ";
						$params['msgto']=(int)($row['acctid'] ?? 0);
					} else $targetadd='';
					$sql="SELECT $ac.name AS name, $mail.* FROM $mail INNER JOIN $ac ON $mail.msgto=$ac.acctid WHERE $mail.msgfrom = :msgfrom $searchsection $targetadd ORDER BY $mail.messageid DESC LIMIT $limit";
					$params['msgfrom']=$acctid;
					break;
				case "string":
					$sql="SELECT $ac.name AS name, $mail.* FROM $mail INNER JOIN $ac ON $mail.msgto=$ac.acctid WHERE $mail.body like :search $searchsection ORDER BY $mail.messageid DESC LIMIT $limit";
					// $search was never defined in the old code (empty pattern); kept as is.
					$params['search']='';
					break;
			}
debug($sql);
			// An unknown track left $sql undefined before (the query failed); show no rows instead.
			$result = isset($sql) ? $conn->fetchAllAssociative($sql,$params) : array();
			$date=translate_inline("Postdate");
			$author=translate_inline("Recipient");
			$subject=translate_inline("Subject");
			$body=translate_inline("Body");
			rawoutput("<table border=0 cellpadding=2 cellspacing=1 bgcolor='#999999' align=center width='100%'>");
			rawoutput("<tr class='trhead' height=30px><td><b>$date</b></td><td><b>$author</b></td><td><b>$subject</b></td><td><b>$body</b></td></tr>");
			$class="trlight";
			$col="`&";
			output("Since bans here should be done either permanently or people normally only warned, no ban options are offered here`n`n");
			foreach ($result as $row) {
				$class=($class=='trlight'?'trdark':'trlight');
				rawoutput("<tr height=30px class='$class'>");
				rawoutput("<td>");
				output_notl($row['sent']);
				rawoutput("</td><td>");
				output_notl($row['name']);
				rawoutput("</td><td>");
				output_notl($row['subject']);
				rawoutput("</td><td>");
				$out=unserialize($row['body']);
				if (is_array($out)) 
					output_notl(sprintf($out));
					else
					output_notl($row['body']);
				rawoutput("</td></tr>");

			}
			rawoutput("</table>");
		default:
			
	}
	/*debug(advertisingtracker_checklotgd('http://google.de'));
	debug(advertisingtracker_checklotgd('http://lotgd.de'));
	debug(advertisingtracker_checklotgd(advertisingtracker_geturl("I Marsha, visit http://lotgd.de ^^")));*/
	page_footer();
}

function advertisingtracker_checklotgd($url) {
	$content = @file_get_contents($url);
	//if (preg_match("/.*This work is licensed under a.*Creative Commons License.*Game Design and Code: Copyright.*Eric Stevens.*JT Traub.*/",$content)>0) return 1;
	if (preg_match("/Game Design and Code.*Eric Stevens.*JT.*Traub/",$content)>0) return 1;
	return 0;
}

function advertisingtracker_geturl($text) {
	$matches=array();
	//preg_match('/[^.]+\.[^.]+$/', $text, $matches);
	@preg_match('/(http(s?)|ftp).*[^.]+\.[^.].[^ |^'.chr(20).']/', $text, $matches);
	$do=str_replace(" ","",$matches[0]);
	$len=strlen($do);
	if ($do[$len-1]=="/") $do=substr($do,0,$len-2);
	return $do;
}

?>
