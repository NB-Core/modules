<?php

function maillimiter_getmoduleinfo(){
$info = array(
	"name"=>"Maillimiter",
	"version"=>"1.0",
	"author"=>"`2Oliver Brendel",
	"override_forced_nav"=>true,
	"category"=>"Mail",
	"download"=>"",
	"settings"=>array(
		"Mail Limiter Settings,title",
		"maxmails"=>"Maximum amount of mails you can have (read+unread),int|200",
		"After that you will not receive any more emails,note",
		"su_sent"=>"Is a superuser excluded from that limit when trying to send mail to somebody?,bool|1",
		),
	);
	return $info;
}

function maillimiter_install(){
	module_addhook_priority("mailfunctions",50);
	return true;
}

function maillimiter_uninstall(){
	return true;
}

function maillimiter_dohook($hookname, $args){
	global $session;
	switch ($hookname) {
		case "mailfunctions":
			if (httpget('op')=='send') {
				$to = httppost('to');
				$sql = "SELECT count(m.messageid) AS count FROM " . db_prefix("mail") . " as m LEFT JOIN ".db_prefix('accounts')." as a ON m.msgto=a.acctid WHERE a.login='$to'";
				$result = db_query($sql);
				$row=db_fetch_assoc($result);
				if ($row['count']>=get_module_setting('maxmails')) {
					if (get_module_setting('su_sent')&& ($session['user']['superuser']>0)) {
						output("`\$Normally you would not be able to send this user a mail, but since you have special privileges, your message has been delivered.`n`n");
					} else {
						$page='mail.php';
						$name=appoencode(translate_inline("Back to the inbox"));
						rawoutput("<a href='$page' class='motd'> $name </a>");
						output_notl("`n`n");
						output("`v`cThis user cannot receive any more mail, he/she has `btoo many mails`b in his/her inbox. Please wait until he/she has freed some space.`c`0");
						popup_footer();
					}
				}
			} elseif (httpget('op')=='') {
				$sql = "SELECT count(m.messageid) AS count FROM " . db_prefix("mail") . " as m WHERE m.msgto=".$session['user']['acctid'].";";
				$result = db_query($sql);
				$row=db_fetch_assoc($result);
				$left=get_module_setting('maxmails')-$row['count'];
				if ($row['count']>=get_module_setting('maxmails')) {
					output("`\$`c`bYou cannot receive any more mails! Delete some old mails, you have to delete `v%s %s `\$until you can receive new mails!`b`c`0`n",abs($left)+1,translate_inline((abs($left)>1?"mails":"mail")));
				} elseif ($row['count']>=get_module_setting('maxmails')*.9) {
					output("`v`cYou can only receive `b%s %s`b until you won't get new ones!.`c`0`n",$left,translate_inline(($left>1?"mails":"mail")));
				}
			}
		break;
	}
	return $args;
}

function maillimiter_run(){
}

?>