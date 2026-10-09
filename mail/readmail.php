<?php

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
use Lotgd\Forms;
use Lotgd\MySQL\Database;

function readmail_getmoduleinfo() {
	$info = array
		(
		 "name"=>"Delete read mails and mark as read",
		 "version"=>"1.1",
		 "author"=>"Oliver Brendel",
		 "category"=>"Mail",
		 "download"=>"",
		);
	return $info;
}

function readmail_install() {
	module_addhook("header-mail");
	module_addhook("mailform");
	return true;
}

function readmail_uninstall() {
	return true;
}

function readmail_dohook($hookname,$args) {
	global $session;
	switch ($hookname) {
                case "header-mail":
                        // The buttons sit in the inbox form, which carries mail.php's form token.
                        // mail.php checks that token only after this hook, so check it here first.
                        // A core without Forms::validateCsrf() (before September 2026) has no form token to check.
                        if ((httppost('delete_readmails') || httppost('mark_as_read'))
                                && method_exists(Forms::class, 'validateCsrf') && !Forms::validateCsrf()) {
                                break;
                        }
                        if (httppost('delete_readmails')) {
                                $connection = Database::getDoctrineConnection();
                                $mailTable = Database::prefix('mail');

                                $connection->executeStatement(
                                        "DELETE FROM {$mailTable} WHERE msgto = :acctid AND seen = 1",
                                        [
                                                'acctid' => (int) $session['user']['acctid'],
                                        ],
                                        [
                                                'acctid' => ParameterType::INTEGER,
                                        ]
                                );
                                output("`RRead messages deleted successfully!`n`n");
                                $args['done']=1;
                                invalidatedatacache("mail-{$session['user']['acctid']}");
                        } elseif (httppost('mark_as_read')) {
                                $msg=httppost('msg');
                                if (!is_array($msg) || count($msg)<1)  {
                                        $session['message'] = translate_inline("`\$`bYou cannot mark zero messages! What does this mean? You pressed \"Mark Checked As Seen\" but there are no messages checked!  What sort of world is this that people press buttons that have no meaning?!?`b`0");
                                        break;
                                }
                                $connection = Database::getDoctrineConnection();
                                $mailTable = Database::prefix('mail');
                                $messageIds = array_map('intval', $msg);

                                $connection->executeStatement(
                                        "UPDATE {$mailTable} SET seen = 1 WHERE msgto = :acctid AND messageid IN (:ids)",
                                        [
                                                'acctid' => (int) $session['user']['acctid'],
                                                'ids' => $messageIds,
                                        ],
                                        [
                                                'acctid' => ParameterType::INTEGER,
                                                'ids' => ArrayParameterType::INTEGER,
                                        ]
                                );
                                output("`y%s message(s) marked successfully!`n`n",count($messageIds));
                                $args['done']=1;
                                invalidatedatacache("mail-{$session['user']['acctid']}");
                        }
                        break;
		case "mailform":
			$checkread=translate_inline("Mark Checked As Seen");
			rawoutput("<input type='submit' name='mark_as_read' class='button' value='$checkread'>");
			$read=translate_inline("Delete All Read");
			rawoutput("<input type='submit' name='delete_readmails' class='button' value='$read'>");
			break;
	}
	return $args;
}

function readmail_run() {
}


?>
