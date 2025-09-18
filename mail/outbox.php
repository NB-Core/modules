<?php

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Lotgd\MySQL\Database;

//this is mainly a copy of mail.php
//took a good look at cortalux friendlist to cope with the forced navs...
//define("OVERRIDE_FORCED_NAV",true);


function outbox_getmoduleinfo(){
	$info = array(
			"name"=>"Outbox",
			"override_forced_nav"=>true,
			"version"=>"1.03",
			"author"=>"`2Oliver Brendel`0 who used mainly mail.php",
			"category"=>"Mail",
			"download"=>"http://lotgd-downloads.com",
			"description"=>"Adds an outbox to the users YOM. Yet this does not change any mails. You can only view mails that are not already deleted by the recipient.",
			"settings"=>array(
				"Outbox - Preferences,title",
				"Note that this is no real outbox yet a -view from the recipient-,note",
				"if the recipient deleted the message... its gone. Also true if the sender deletes it,note",
				"allowdelete"=>"Allow users to delete sent mails (undo their sent in a way),bool|1",
				"this stores messages additionally in an extra table,note",
				"if active then the setting above will not delete the message from the recipients inbox,note",
				"realoutbox"=>"Use a real separate outbox (uses space + cpu time),bool|0",
				"daystomove"=>"If used as a real outbox how many days after a mail get moved there from the current archive to the old archive?,int|3",
				"Note: This should be smaller than your mail expiration. If not it will be assumed so.,note",
				),
		     );
	return $info;
}

function outbox_install(){
	module_addhook("mailfunctions");
	module_addhook("newday-runonce");
	$archive=array(
			'messageid'=>array('name'=>'messageid', 'type'=>'int(11) unsigned', 'extra'=>'auto_increment'), 
			'msgfrom'=>array('name'=>'msgfrom', 'type'=>'int(11) unsigned'),
			'msgto'=>array('name'=>'msgto', 'type'=>'int(11) unsigned'),
			'subject'=>array('name'=>'subject', 'type'=>'varchar(255)'),
			'body'=>array('name'=>'body', 'type'=>'text'),
			'sent'=>array('name'=>'sent', 'type'=>'datetime', 'default'=>DATETIME_DATEMIN),
			'seen'=>array('name'=>'seen', 'type'=>'tinyint(1)', 'default'=>'0'),
			'key-PRIMARY' => array('name'=>'PRIMARY', 'type'=>'primary key', 'unique'=>'1', 'columns'=>'messageid'),
			'key-one'=> array('name'=>'msgto', 'type'=>'key', 'unique'=>'0', 'columns'=>'msgto'),
			'key-two'=> array('name'=>'msgfrom', 'type'=>'key', 'unique'=>'0', 'columns'=>'msgfrom'),
			'key-three'=> array('name'=>'seen', 'type'=>'key', 'unique'=>'0', 'columns'=>'seen'),
		      );
	require_once("lib/tabledescriptor.php");
	synctable(db_prefix("mailoutbox"), $archive, true);
	synctable(db_prefix("mailoutbox_archive"), $archive, true);
	return true;
}

function outbox_uninstall() {

	if(db_table_exists(db_prefix("mailoutbox"))){
		db_query("DROP TABLE ".db_prefix("mailoutbox"));
	}
	return true;
}


function outbox_dohook($hookname, $args) {
	global $session;
	switch ($hookname) {
                case "mailfunctions":
                        $outbox = translate_inline("Outbox");
                        array_push($args, array("runmodule.php?module=outbox", $outbox));
                        addnav ("","runmodule.php?module=outbox");
                        $op = httpget('op');
                        $atable = db_prefix('accounts');
                        $mailTable = db_prefix('mail');
                        $mailoutboxTable = db_prefix('mailoutbox');
                        $conn = Database::getDoctrineConnection();
                        if (get_module_setting('realoutbox')) {
                                if ($op=="send") {
                                        $to = httppost('to');
                                        $result = $conn->executeQuery(
                                                "SELECT acctid FROM {$atable} WHERE login = :login",
                                                [
                                                        'login' => $to,
                                                ],
                                                [
                                                        'login' => ParameterType::STRING,
                                                ]
                                        );
                                        $row1 = $result->fetchAssociative();
                                        if ($row1){
                                                $acctid = (int) $row1['acctid'];
                                                $inboxCount = (int) $conn->executeQuery(
                                                        "SELECT count(messageid) AS count FROM {$mailTable} WHERE msgto = :msgto AND seen = 0",
                                                        [
                                                                'msgto' => $acctid,
                                                        ],
                                                        [
                                                                'msgto' => ParameterType::INTEGER,
                                                        ]
                                                )->fetchOne();
                                                $outboxCount = (int) $conn->executeQuery(
                                                        "SELECT count(messageid) AS count FROM {$mailoutboxTable} WHERE msgfrom = :msgfrom AND seen = 0",
                                                        [
                                                                'msgfrom' => $session['user']['acctid'],
                                                        ],
                                                        [
                                                                'msgfrom' => ParameterType::INTEGER,
                                                        ]
                                                )->fetchOne();
                                                if ($inboxCount>=getsetting("inboxlimit",50)) {
                                                        //do nothing in this module
                                                        output("Sorry, this mail won't be saved in your outbox. You have to delete mails there.");
                                                } else {
                                                        $subject =  str_replace("`n","",httppost('subject'));
                                                        $body = str_replace("`n","\n",httppost('body'));
                                                        $body = str_replace("\r\n","\n",$body);
                                                        $body = str_replace("\r","\n",$body);
                                                        $body = mb_substr(stripslashes($body),0,(int)getsetting("mailsizelimit",1024));
                                                        $conn->executeStatement(
                                                                "INSERT INTO {$mailoutboxTable} (msgfrom,msgto,subject,body,sent) VALUES (:msgfrom, :msgto, :subject, :body, :sent)",
                                                                [
                                                                        'msgfrom' => $session['user']['acctid'],
                                                                        'msgto' => $acctid,
                                                                        'subject' => $subject,
                                                                        'body' => $body,
                                                                        'sent' => date("Y-m-d H:i:s"),
                                                                ],
                                                                [
                                                                        'msgfrom' => ParameterType::INTEGER,
                                                                        'msgto' => ParameterType::INTEGER,
                                                                        'subject' => ParameterType::STRING,
                                                                        'body' => ParameterType::STRING,
                                                                        'sent' => ParameterType::STRING,
                                                                ]
                                                        );
                                                }
                                        }
                                }
                        }
                        if ($op=="read") {
                                $id=(int)httpget('id');
                                $result = $conn->executeQuery(
                                        "SELECT {$mailTable}.*, {$atable}.name FROM {$mailTable} LEFT JOIN {$atable} ON {$atable}.acctid = {$mailTable}.msgfrom WHERE msgto = :msgto AND messageid = :messageid",
                                        [
                                                'msgto' => $session['user']['acctid'],
                                                'messageid' => $id,
                                        ],
                                        [
                                                'msgto' => ParameterType::INTEGER,
                                                'messageid' => ParameterType::INTEGER,
                                        ]
                                );
                                $row=$result->fetchAssociative();
                                if ($row){
                                        $result = $conn->executeQuery(
                                                "SELECT {$mailoutboxTable}.*, {$atable}.name FROM {$mailoutboxTable} LEFT JOIN {$atable} ON {$atable}.acctid = {$mailoutboxTable}.msgfrom WHERE msgto = :msgto AND subject = :subject AND body = :body",
                                                [
                                                        'msgto' => $session['user']['acctid'],
                                                        'subject' => $row['subject'],
                                                        'body' => $row['body'],
                                                ],
                                                [
                                                        'msgto' => ParameterType::INTEGER,
                                                        'subject' => ParameterType::STRING,
                                                        'body' => ParameterType::STRING,
                                                ]
                                        );
                                        $row = $result->fetchAssociative();
                                        if ($row){
                                                if (!$row['seen']) {
                                                        $conn->executeStatement(
                                                                "UPDATE {$mailoutboxTable} SET seen = 1 WHERE  msgto = :msgto AND messageid = :messageid",
                                                                [
                                                                        'msgto' => $session['user']['acctid'],
                                                                        'messageid' => $row['messageid'],
                                                                ],
                                                                [
                                                                        'msgto' => ParameterType::INTEGER,
                                                                        'messageid' => ParameterType::INTEGER,
                                                                ]
                                                        );
                                                }
                                        }
                                }
                        }
                        break;

                case "newday-runonce":
                        $days=(int)get_module_setting('daystomove');
                        $oldmail=(int)getsetting("oldmail",14);
                        if ($days>$oldmail) $days=$oldmail; //else we won't delete anything
                        $conn = Database::getDoctrineConnection();
                        $mailoutboxTable = db_prefix('mailoutbox');
                        $mailoutboxArchiveTable = db_prefix('mailoutbox_archive');
                        $moveBefore = date("Y-m-d H:i:s",strtotime("-".$days."days"));
                        $deleteBefore = date("Y-m-d H:i:s",strtotime("-".$oldmail."days"));
                        $conn->executeStatement("LOCK TABLES {$mailoutboxTable} WRITE, {$mailoutboxArchiveTable} WRITE");
                        try {
                                $conn->executeStatement(
                                        "INSERT INTO {$mailoutboxArchiveTable} SELECT * FROM {$mailoutboxTable} WHERE sent < :moveBefore",
                                        [
                                                'moveBefore' => $moveBefore,
                                        ],
                                        [
                                                'moveBefore' => ParameterType::STRING,
                                        ]
                                );
                                $conn->executeStatement(
                                        "DELETE FROM {$mailoutboxTable} WHERE sent < :moveBefore",
                                        [
                                                'moveBefore' => $moveBefore,
                                        ],
                                        [
                                                'moveBefore' => ParameterType::STRING,
                                        ]
                                );
                        } finally {
                                $conn->executeStatement("UNLOCK TABLES");
                        }
                        $conn->executeStatement(
                                "DELETE FROM {$mailoutboxArchiveTable} WHERE sent < :deleteBefore",
                                [
                                        'deleteBefore' => $deleteBefore,
                                ],
                                [
                                        'deleteBefore' => ParameterType::STRING,
                                ]
                        );
                        break;
		default:

			break;
	}
	return $args;
}

function outbox_run(){
        global $session;
        $op=httpget('op');
        $id = (int) httpget('id');
        $conn = Database::getDoctrineConnection();
        require_once("lib/http.php");
	popup_header("Ye Olde Poste Office");
	rawoutput("<table width='50%' border='0' cellpadding='0' cellspacing='2'>");
	rawoutput("<tr><td>");
	$t = translate_inline("Back to the Ye Olde Poste Office");
	$o = translate_inline("Back to the Outbox");
	rawoutput("<a href='mail.php'>$t</a></td><td>");
	rawoutput("<a href='runmodule.php?module=outbox'>$o</a>");
	addnav("","runmodule.php?module=outbox");
	rawoutput("</td></tr></table>");
	output_notl("`n`n");
	$realoutbox=(int)get_module_setting('realoutbox');
	$allowdelete=(int)get_module_setting('allowdelete');
	$table=($realoutbox?"mailoutbox":"mail"); //set the table
        $ptable= db_prefix($table);
        $archivetable= db_prefix("mailoutbox_archive");
        $mailoutboxBase = db_prefix("mailoutbox");
	$atable= db_prefix("accounts");
	switch ($op) {
                case "delown":
                        $conn->executeStatement(
                                "DELETE FROM {$ptable} WHERE msgfrom = :msgfrom AND messageid = :messageid",
                                [
                                        'msgfrom' => $session['user']['acctid'],
                                        'messageid' => $id,
                                ],
                                [
                                        'msgfrom' => ParameterType::INTEGER,
                                        'messageid' => ParameterType::INTEGER,
                                ]
                        );
                        invalidatedatacache("mail-".httpget('rec'));
                        header("Location: mail.php");
                        exit();
                        break;
                case "readown":
                        $params = [
                                'msgfrom' => $session['user']['acctid'],
                                'messageid' => $id,
                        ];
                        $types = [
                                'msgfrom' => ParameterType::INTEGER,
                                'messageid' => ParameterType::INTEGER,
                        ];
                        $result = $conn->executeQuery(
                                "SELECT {$ptable}.*, {$atable}.name, {$atable}.acctid FROM {$ptable} LEFT JOIN {$atable} ON {$atable}.acctid = {$ptable}.msgto WHERE msgfrom = :msgfrom AND messageid = :messageid",
                                $params,
                                $types
                        );
                        $message = $result->fetchAssociative();
                        $archive=0;
                        if (!$message) {
                                $result = $conn->executeQuery(
                                        "SELECT {$archivetable}.*, {$atable}.name, {$atable}.acctid FROM {$archivetable} LEFT JOIN {$atable} ON {$atable}.acctid = {$archivetable}.msgto WHERE msgfrom = :msgfrom AND messageid = :messageid",
                                        $params,
                                        $types
                                );
                                $message = $result->fetchAssociative();
                                if ($message) {
                                        $archive=1;
                                }
                        }
                        if ($message){
                                $mail=$message;
                                if (!$message['seen']) output("`b`#Not yet read by the recipient`b`n");
                                else output_notl("`n");
                                $tot=translate_inline("To: ");
                                output_notl("`b`2$tot`b `^%s`n",$message['name']);
                                output("`b`2Subject:`b `^%s`n",$message['subject']);
                                output("`b`2Sent:`b `^%s`n",$message['sent']);


                                //prev next del start
                                $del = translate_inline("Delete");
                                if ($allowdelete && !$realoutbox) output("`i`0Note: If you delete this message, the recipient won't see it anymore.`i");
                                rawoutput("<table width='50%' border='0' cellpadding='0' cellspacing='5'><tr>");
                                if ($allowdelete) rawoutput("<td><a href='runmodule.php?module=outbox&op=delown&archive=$archive&id={$message['messageid']}&rec={$message['acctid']}' class='motd'>$del</a></td>");
                                rawoutput("</tr><tr>");
                                addnav("","runmodule.php?module=outbox&op=delown&archive=$archive&id={$message['messageid']}&rec={$message['acctid']}");
                                if (!$archive) {
                                        $prevResult = $conn->executeQuery(
                                                "SELECT messageid FROM {$ptable} WHERE msgfrom = :msgfrom AND messageid < :messageid ORDER BY messageid DESC LIMIT 1",
                                                $params,
                                                $types
                                        );
                                        $prevId = $prevResult->fetchOne();
                                        if ($prevId === false) {
                                                $prevResult = $conn->executeQuery(
                                                        "SELECT messageid FROM {$archivetable} WHERE msgfrom = :msgfrom AND messageid < :messageid ORDER BY messageid DESC LIMIT 1",
                                                        $params,
                                                        $types
                                                );
                                                $prevId = $prevResult->fetchOne();
                                        }
                                } else {
                                        $prevResult = $conn->executeQuery(
                                                "SELECT messageid FROM {$archivetable} WHERE msgfrom = :msgfrom AND messageid < :messageid ORDER BY messageid DESC LIMIT 1",
                                                $params,
                                                $types
                                        );
                                        $prevId = $prevResult->fetchOne();
                                }
                                $pid = ($prevId === false ? 0 : (int) $prevId);

                                if (!$archive) {
                                        $nextResult = $conn->executeQuery(
                                                "SELECT messageid FROM {$ptable} WHERE msgfrom = :msgfrom AND messageid > :messageid ORDER BY messageid LIMIT 1",
                                                $params,
                                                $types
                                        );
                                        $nextId = $nextResult->fetchOne();
                                } else {
                                        $nextResult = $conn->executeQuery(
                                                "SELECT messageid FROM {$archivetable} WHERE msgfrom = :msgfrom AND messageid > :messageid ORDER BY messageid LIMIT 1",
                                                $params,
                                                $types
                                        );
                                        $nextId = $nextResult->fetchOne();
                                        if ($nextId === false) {
                                                $nextResult = $conn->executeQuery(
                                                        "SELECT messageid FROM {$ptable} WHERE msgfrom = :msgfrom AND messageid > :messageid ORDER BY messageid LIMIT 1",
                                                        $params,
                                                        $types
                                                );
                                                $nextId = $nextResult->fetchOne();
                                        }
                                }
                                $nid = ($nextId === false ? 0 : (int) $nextId);
                                $prev = translate_inline("< Previous");
                                $next = translate_inline("Next >");
                                rawoutput("<td nowrap='true'>");
                                if ($pid > 0) {
                                        rawoutput("<a href='runmodule.php?module=outbox&op=readown&id=$pid' class='motd'>".htmlentities($prev)."</a>");
                                        addnav("","runmodule.php?module=outbox&op=readown&id=$pid");
                                }
                                else rawoutput(htmlentities($prev));
                                rawoutput("</td><td nowrap='true'>");
                                if ($nid > 0) {
                                        rawoutput("<a href='runmodule.php?module=outbox&op=readown&id=$nid' class='motd'>".htmlentities($next)."</a>");
                                        addnav("","runmodule.php?module=outbox&op=readown&id=$nid");
                                }
                                else rawoutput(htmlentities($next));
                                rawoutput("</td>");
                                rawoutput("</tr></table>");
                                //end prev next del
                                output_notl("<img src='images/uscroll.GIF' width='182' height='11' alt='' align='center'>`n",true);
                                output_notl(str_replace("\n","`n",sanitize_mb($mail['body'])));
                                output_notl("`n<img src='images/lscroll.GIF' width='182' height='11' alt='' align='center'>`n",true);

                        }else{
                                output("Eek, no such message was found!");
                        }
                        break;
                case "process":
                        $msg = httppost('msg');
                        if (!is_array($msg) || count($msg)<1){
                                $session['message'] = "`\$`bYou cannot delete zero messages!  What does this mean?  You pressed \"Delete Checked\" but there are no messages checked!  What sort of world is this that people press buttons that have no meaning?!?`b`0";
                                header("Location: mail.php");
                        }else{
                                $messageIds = array_map('intval', $msg);
                                if (count($messageIds) > 0) {
                                        $conn->executeStatement(
                                                "DELETE FROM {$mailoutboxBase} WHERE msgfrom = :msgfrom AND messageid IN (:messageIds)",
                                                [
                                                        'msgfrom' => $session['user']['acctid'],
                                                        'messageIds' => $messageIds,
                                                ],
                                                [
                                                        'msgfrom' => ParameterType::INTEGER,
                                                        'messageIds' => Connection::PARAM_INT_ARRAY,
                                                ]
                                        );
                                        $conn->executeStatement(
                                                "DELETE FROM {$archivetable} WHERE msgfrom = :msgfrom AND messageid IN (:messageIds)",
                                                [
                                                        'msgfrom' => $session['user']['acctid'],
                                                        'messageIds' => $messageIds,
                                                ],
                                                [
                                                        'msgfrom' => ParameterType::INTEGER,
                                                        'messageIds' => Connection::PARAM_INT_ARRAY,
                                                ]
                                        );
                                }
                                header("Location: mail.php");
                                exit();
                        }
                        break;
		default:
			output("`b`iMail Box`i`b");

			if (isset($session['message'])) {
				output($session['message']);
			}
			$session['message']="";

			$sortorder=httpget('sortorder');
			if ($sortorder=='') $sortorder='date';
			switch ($sortorder) {
				case "subject":
					$order="subject";
					break;
				case "name":
					$order="name";
					break;
				default: //date
					$order="sent";
			}
			$sorting_direction=(int)httpget('direction');
			if ($sorting_direction==0) $direction="DESC";
			else $direction="ASC";
			$newdirection=(int)!$sorting_direction;

                        if (!$realoutbox) {
                                $sql = "SELECT subject,messageid,{$atable}.name,msgto,msgfrom,seen,sent FROM {$ptable} LEFT JOIN {$atable} ON {$atable}.acctid = {$ptable}.msgto WHERE msgfrom = :msgfrom ORDER BY {$order} {$direction}";
                        } else {
                                $sql = "SELECT subject,messageid,{$atable}.name,msgto,msgfrom,seen,sent FROM {$ptable} LEFT JOIN {$atable} ON {$atable}.acctid = {$ptable}.msgto WHERE msgfrom = :msgfrom
                                UNION
                                        SELECT subject,messageid,{$atable}.name,msgto,msgfrom,seen,sent FROM {$archivetable} LEFT JOIN {$atable} ON {$atable}.acctid = {$archivetable}.msgto WHERE msgfrom = :msgfrom
                                        ORDER BY {$order} {$direction}";
                        }

                        $result = $conn->executeQuery(
                                $sql,
                                [
                                        'msgfrom' => $session['user']['acctid'],
                                ],
                                [
                                        'msgfrom' => ParameterType::INTEGER,
                                ]
                        );
                        $rows = $result->fetchAllAssociative();
                        $rowCount = count($rows);
                        if ($rowCount>0){
                                $i=-1;
                                $subject = translate_inline("Subject");
                                $from = translate_inline("Sender");
                                $date = translate_inline("SendDate");
                                $arrow = ($sorting_direction?"arrow_down.png":"arrow_up.png");
                                rawoutput("<form action='runmodule.php?module=outbox&op=process' method='POST'><table>");
                                rawoutput("<tr class='trhead'><td></td>");
                                rawoutput("<td>".($sortorder=='subject'?"<img src='images/shapes/$arrow' alt='$arrow'":"")."<a href='runmodule.php?module=outbox&sortorder=subject&direction=".($sortorder=='subject'?$newdirection:$sorting_direction)."'>$subject</a></td>");
                                rawoutput("<td>".($sortorder=='name'?"<img src='images/shapes/$arrow' alt='$arrow'":"")."<a href='runmodule.php?module=outbox&sortorder=name&direction=".($sortorder=='name'?$newdirection:$sorting_direction)."'>$from</a></td>");
                                rawoutput("<td>".($sortorder=='date'?"<img src='images/shapes/$arrow' alt='$arrow'":"")."<a href='runmodule.php?module=outbox&sortorder=date&direction=".($sortorder=='date'?$newdirection:$sorting_direction)."'>$date</a></td>");
                                rawoutput("</tr>");
                                addnav("","runmodule.php?module=outbox&op=process");
                                foreach ($rows as $row) {
                                        $i++;
                                        output_notl("<tr>",true);
                                        output_notl("<td nowrap><input id='checkbox$i' type='checkbox' name='msg[]' value='{$row['messageid']}'><img src='images/".($row['seen']?"old":"new")."scroll.GIF' width='16' height='16' alt='".($row['seen']?"Old":"New")."'></td>",true);
                                        output_notl("<td><a href='runmodule.php?module=outbox&op=readown&id={$row['messageid']}'>",true);
                                        if (trim($row['subject'])=="")
                                                output("`i(No Subject)`i");
                                        else
                                                output_notl($row['subject']);
					output_notl("</a></td><td><a href='runmodule.php?module=outbox&op=readown&id={$row['messageid']}'>",true);
					addnav("","runmodule.php?module=outbox&op=readown&id={$row['messageid']}");
					output_notl("%s",$row['name']);
					output_notl("</a></td><td><a href='runmodule.php?module=outbox&op=readown&id={$row['messageid']}'>".date("M d, h:i a",strtotime($row['sent']))."</a></td>",true);
					addnav("","runmodule.php?module=outbox&op=readown&id={$row['messageid']}");
					output_notl("</tr>",true);
				}
				output_notl("</table>",true);
				$checkall = htmlentities(translate_inline("Check All"));
				$out="<input type='button' value=\"$checkall\" class='button' onClick='";
				for ($i=$i;$i>=0;$i--){
					$out.="document.getElementById(\"checkbox$i\").checked=true;";
				}
				$out.="'>";
				output_notl($out,true);
				$delchecked = htmlentities(translate_inline("Delete Checked"));
				if ($allowdelete) output_notl("<input type='submit' class='button' value=\"$delchecked\">",true);
                                output_notl("</form>",true);

                        }else{
                                output("`iAww, you have sent no mail, how sad.`i");
                        }
                        if ($realoutbox) {
                                output ("`n`n`iYou currently have %s messages in your %s outbox.",$rowCount,translate_inline("real"));
                        } else {
                                output ("`n`n`iYou currently have %s messages in your %s outbox.",$rowCount,translate_inline("virtual"));
                                output("`nMessages who are deleted by the recipients can no longer be shown by the system.");
                        }
			output("`nMessages are automatically deleted (read or unread) after %s days.",getsetting("oldmail",14));
			break;
	}
	popup_footer();
}

?>
