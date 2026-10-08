<?php

use Doctrine\DBAL\ParameterType;
use Lotgd\MySQL\Database;

/*
1.1 added the last commentary lines to the submit, also you can enter a comment to the request
*/

function callformod_getmoduleinfo() {
	$info = array(
	    "name"=>"Call for Moderator",
		"description"=>"This module YOMs for help in a comment section to all online moderators",
		"override_forced_nav"=>true,
		"version"=>"1.1",
		"author"=>"`2Oliver Brendel, idea by XChrisX",
		"category"=>"Commentary",
		"download"=>"http://lotgd-downloads.com",
		);
    return $info;
}

function callformod_install() {
	module_addhook_priority("insertcomment",25);
	return true;
}

function callformod_uninstall() {
	return true;
}


function callformod_dohook($hookname, $args){
	global $session;
	switch ($hookname)
	{
	case "insertcomment":
		$text=appoencode(translate_inline("`\$Call for moderator"));
		rawoutput("<table border=0 cellpadding=2 cellspacing=1 align=right><tr><td align=right>");
		rawoutput("<a href='runmodule.php?module=callformod&op=call&section={$args['section']}' target='_blank' onClick=\"".popup("runmodule.php?module=callformod&op=call&section={$args['section']}").";return false;\">$text</a>");
		rawoutput("</td></tr></table>");
 //function popup comes from lib/pageparts.php
	break;
	}
	return $args;
}

function callformod_run(){
	global $session;
	popup_header("Call for moderator help");
	$op=httpget('op');
	$limit=15; //for now here, no extra setting
	$section=httpget('section');
	switch ($op) {
		case "call":
			$mods=getmods();
			if (count($mods)<1) {
				output("`^Sorry, no moderator is online, please fill out this petition to let the staff know what has happened..");
				output("`n`n");
			}
			$defaulttext="I want to report abusive behaviour that is against the server rules. Please come to the section mentioned in the post.";
			$defaulttext=rep(color_sanitize(translate_inline($defaulttext)));
			$signature=translate_inline("`n`nRegards, %s`0");
			$signature=rep(color_sanitize(sprintf($signature,$session['user']['name'])));
			$value=translate_inline("Submit");
			//now going on
			if (count($mods)>=1) output("`^The last commentary lines will be mailed to %s`^, as well as the following comment.`n",$mods[0]['name']);
				else
				output("`^The last commentary lines will be added to your petition.`n");
			output("Please describe as precisely as possible why exactly a moderator is needed right now.");
			output("`n`c`b`\$Remember: a misuse of this function might also mean consequences for your person.`b`c`n`n");
			rawoutput("<form action='runmodule.php?module=callformod&op=submit&section=$section' method='POST'>");
			addnav("","runmodule.php?module=callformod&op=submit&section=$section");
			rawoutput("<textarea name='reason' class='input' cols='60' rows='5'>".$defaulttext.$signature."</textarea><br>");
			rawoutput("<br><input type='submit' value='$value'>");
			if (count($mods)>=1) rawoutput("<input type='hidden' name='moderator' value='".$mods[0]['acctid']."'>");
				else
				rawoutput("<input type='hidden' name='moderator' value='-1'>");
			rawoutput("</form>");
			break;
                case "submit":
                        require_once("lib/systemmail.php");
                        $moderator=httppost('moderator');
                        $conn = Database::getDoctrineConnection();
                        $accountsTable = Database::prefix('accounts');
                        $commentaryTable = Database::prefix('commentary');
                        $sql = "SELECT a.name, b.comment FROM {$accountsTable} AS a INNER JOIN {$commentaryTable} AS b ON a.acctid = b.author WHERE b.section = :section ORDER BY b.commentid DESC LIMIT :limit";
                        $result = $conn->executeQuery(
                                $sql,
                                [
                                        'section' => $section,
                                        'limit' => $limit,
                                ],
                                [
                                        'section' => ParameterType::STRING,
                                        'limit' => ParameterType::INTEGER,
                                ]
                        );
                        $lastlines='';
                        while ($row=$result->fetchAssociative()) {
                                $lastlines="`q".$row['name']."`q says, \"`2".$row['comment']."`q\"`n".$lastlines;
                        }
                        $reason=httppost('reason');
			$msg=array("`7A moderator has been requested for the area '`\$%s`7' from user `4%s`7 (login %s). Please go there and check up things, or notify another moderator/the user if you cannot go there now.`nThe last commentary lines spoken were:`n`n`@%s`n`n`7The reason entered by the user who called was:`n`n`q%s`n",$section,$session['user']['name'],$session['user']['login'],str_replace("`%","`4",$lastlines),str_replace("`%","`4",$reason));
			if ($moderator==-1) {
				$sendmsg=sprintf_translate("`7A moderator has been requested for the area '`\$%s`7' from user `4%s`7 (login %s). Please go there and check up things, or notify another moderator/the user if you cannot go there now.`nThe last commentary lines spoken were:`n`n`@%s`n`n`7The reason entered by the user who called was:`n`n`q%s`n",$section,$session['user']['name'],$session['user']['login'],str_replace("`%","`4",$lastlines),str_replace("`%","`4",$reason));
				callformod_sendpetition($reason,$sendmsg);
				output("Your petition has been sent. Please be patient until the staff can take care of that problem.");
			} elseif (!$moderator) {
				output("Error...moderator invalid.");
				break;
			} else {
				output("`^You have requested help for this section, the moderator has been notified.");
				output("`n`nMisuse of this function will be punished.");
				systemmail($moderator,array("`\$Urgent Help Request"),$msg);
			}
			
			break;
	}
	popup_footer();
}

function getmods($online=true) {
        $conn = Database::getDoctrineConnection();
        $accountsTable = Database::prefix('accounts');
        $laston = date("Y-m-d G:i:s", strtotime("-".getsetting("LOGINTIMEOUT", 900)." sec"));
        $seed = e_rand();
        $sql = "SELECT acctid, name, login, superuser FROM {$accountsTable} WHERE laston > :laston AND (superuser & :superuser) = :superuser ORDER BY RAND($seed)";
        $result = $conn->executeQuery(
                $sql,
                [
                        'laston' => $laston,
                        'superuser' => SU_EDIT_COMMENTS,
                ],
                [
                        'laston' => ParameterType::STRING,
                        'superuser' => ParameterType::INTEGER,
                ]
        );
        $mods=array();
        while ($row=$result->fetchAssociative()) {
                $mods[]=$row;
        }
        return $mods;
}

function rep($in) {
	$out=str_replace("`n","\n",$in);
	return $out;
}

function callformod_sendpetition($reason,$msg) {
        global $session;
        require_once("lib/output_array.php");
        $msg=str_replace("`n","\n",$msg);
        $msg=full_sanitize($msg);
        //extracted from petition.php
        $ip = explode(".",$_SERVER['REMOTE_ADDR']);
        array_pop($ip);
        $ip = implode(".",$ip).".";
        $date = date("Y-m-d H:i:s");
        $conn = Database::getDoctrineConnection();
        $petitionTable = Database::prefix('petitions');
        $conn->executeStatement(
                "INSERT INTO {$petitionTable} (author, date, body, pageinfo, ip, id) VALUES (:author, :date, :body, :pageinfo, :ip, :id)",
                [
                        'author' => (int)$session['user']['acctid'],
                        'date' => $date,
                        'body' => $msg,
                        'pageinfo' => output_array($session,"Session:"),
                        'ip' => $_SERVER['REMOTE_ADDR'],
                        'id' => $_COOKIE['lgi'] ?? '',
                ],
                [
                        'author' => ParameterType::INTEGER,
                        'date' => ParameterType::STRING,
                        'body' => ParameterType::STRING,
                        'pageinfo' => ParameterType::STRING,
                        'ip' => ParameterType::STRING,
                        'id' => ParameterType::STRING,
                ]
        );
                // If the admin wants it, email the petitions to them.
        if (getsetting("emailpetitions", 0)) {
		// Yeah, the format of this is ugly.
		require_once("lib/sanitize.php");
		$name = color_sanitize($session['user']['name']);
		$url = getsetting("serverurl",
				"http://".$_SERVER['SERVER_NAME'] .
				($_SERVER['SERVER_PORT']==80?"":":".$_SERVER['SERVER_PORT']) .
				dirname($_SERVER['REQUEST_URI']));
		if (!preg_match("/\/$/", $url)) {
			$url = $url . "/";
			savesetting("serverurl", $url);
		}
			$msg  = "Server: $url\n";
		$msg .= "Author: $name\n";
		$msg .= "Date  : $date\n";
		$msg .= "Given Reason of the Mod-Call: $reason\n";
		$msg .= "Body  :\n".$msg."\n";
		mail(getsetting("gameadminemail","postmaster@localhost.com"),"New LoGD Petition at " . $url, $msg);
	}
}

?>

