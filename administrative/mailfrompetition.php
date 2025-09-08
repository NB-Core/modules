<?php

function mailfrompetition_getmoduleinfo() {
	$info = array(
			"name"=>"Email from petitions",
			"version"=>"1.0",
			"author"=>"`2Oliver Brendel",
			"category"=>"Administrative",
			"settings" => array(
				"Settings for Email From Petitions,title",
				"adminmail"=>"Admin email,text",
				"adminname"=>"Admin Name (Sender),text",
				"ccmail"=>"CC Emails,text",
				"CC mails only and separated by comma (no leading comma),note",
				"imaphostname"=>"IMAP Hostname,text|localhost",
				"imapport"=>"IMAP Port,int|993",
				"imapusername"=>"IMAP Username,text|USERNAME",
				"imappassword"=>"IMAP Password,text|PASSOWRD",
				"imapprotocol"=>"IMAP Protocol,enum,ssl,SSL,tls,TLS|ssl",
				),
		     );
	return $info;
}

function mailfrompetition_install() {
	module_addhook("footer-viewpetition");
	module_addhook("petition-status");
	return true;
}

function mailfrompetition_uninstall() {
	return true;
}


function mailfrompetition_dohook($hookname, $args){
	global $session;
	switch ($hookname) {

		case "footer-viewpetition":
			$op=httpget('op');
			$setstat=(int)httpget('setstat');
			if ($setstat!=0) {
				//inject a commentary about the move
				$statuses = modulehook("petition-status", array());
				// attention: do not have ANY module that modifies the petitions only in here...
				$text=sprintf_translate("/me`0 moved this petition to category '%s`0'",$statuses[$setstat]);
				emailfrompetitions_insert($text);
			}
			if ($op!='view') return $args;
			$id=httpget('id');
			addnav("Mail-Actions");
			addnav("Email this user","runmodule.php?module=mailfrompetition&op=mail&petition=$id");
			addnav("Check IMAP","runmodule.php?module=mailfrompetition&op=imap&petition=$id");
			addnav("Actions");

			break;
	}
	return $args;
}

function mailfrompetition_run(){
	global $session;
	page_header("Fixed Navs");
	$id=httpget('petition');
	$op=httpget('op');
	require_once("lib/superusernav.php");
	superusernav();
	addnav("Actions");
	addnav("Return to the petition","viewpetition.php?op=view&id=$id");
	$adminmail=get_module_setting('adminmail');
	$adminname=get_module_setting('adminname');
	$ccmail=get_module_setting('ccmail');
	switch ($op) {
		case "mail":
			$sql="SELECT * FROM ".db_prefix('petitions')." WHERE petitionid='$id'";
			$result=db_query($sql);
			$row=db_fetch_assoc($result);
			$author=(int)$row['author'];
			$text=$row['body'];
			if ($author==0) {
				//email from outside, check for an email address
				$body=stripslashes($row['body']);
				debug($body);
				preg_match("'([[:alnum:]_.-]+[@][[:alnum:]_.-]{2,}([.][[:alnum:]_.-]{2,})+)'i",$body,$matches);
				debug($matches);
				if (count($matches)<1) {
					output("There is no email to be found in the petition... ");
				}
				$email=$matches[0];
				debug($email);
				$emailname=$email;
			} else {
				$sql_p="SELECT name,emailaddress FROM ".db_prefix('accounts')." WHERE acctid='$author'";
				$result_p=db_query($sql_p);
				$row_p=db_fetch_assoc($result_p);
				$email=$row_p['emailaddress'];
				$emailname=sanitize($row_p['name']);
				$body=stripslashes($row['body']);
				debug($body);
				preg_match("'([[:alnum:]_.-]+[@][[:alnum:]_.-]{2,}([.][[:alnum:]_.-]{2,})+)'i",$body,$matches);
				$email_match=$matches[0];
				if ($email!=$email_match) {
					output("`\$<h2>Warning: petition-given email differs from the account holders email! Account: $email <-> Petition: $email_match!!</h2>`n`n`tAssuming Accountmail $email.`n`n",true);
				}
			}
			rawoutput("<form action='runmodule.php?module=mailfrompetition&op=send&petition=$id' method='POST'>");
			addnav("","runmodule.php?module=mailfrompetition&op=send&petition=$id");
			output("`2From: %s (%s)`0`n",$adminname,$adminmail);
			output("`2To: %s(%s)`n",$emailname,$email);
			output("CC: %s %s`n`n",$adminmail,($ccmail?"(+$ccmail)":''));
			output("`2Subject:`0");
			$submit=translate_inline("Send Email");
			$pretext=sprintf_translate("(TEXT)`n`nSincerely, your %s",sanitize($session['user']['name']));
			$pretext.=(translate_inline("`n`n---------------------`nOriginal Petition:`n`n"));
			$pretext=str_replace("`n","\n",$pretext);
			$pretext.=$body;
			rawoutput(sprintf("<input type='input' length='30' name='subject' value='%s'/>",translate_inline("Your petition")));
			rawoutput("<br/><textarea name='body' cols='80' rows='10'>");
			//			rawoutput(htmlentities($text));
			rawoutput("$pretext</textarea><input type='submit' class='button' value='$submit'/>");
			rawoutput("<input type='hidden' name='email' value='$email'></form>");
			rawoutput("<input type='hidden' name='emailname' value='$emailname'></form>");
			output("`n`n`\$Note: All email who are sent from here go CC to %s!",$adminmail);
			break;
		case "send":
			$to=httppost('email');
			$toname=httppost('emailname');
			$subject=stripslashes(httppost('subject'));
			$body=stripslashes(htmlentities(httppost('body'),ENT_COMPAT,getsetting('charset','ISO-8859-1')));
			$body.="\n[petition-id]=".$id;
			$body=str_replace("\n","<br/>",$body);
			output("`4Sent to: %s (%s)`n",$toname,$to);
			output("CC: %s %s`n",$adminmail,($ccmail?"(+$ccmail)":''));
			output("Subject: %s`n`n",$subject);
			output("Body:`n%s",$body,true);
			mailfrompetition_sendmail($to,$toname,$body,$subject,$adminmail,$adminname,$ccmail);
			emailfrompetitions_insert(translate_inline("/me mailed concerning this petition"));
			invalidatedatacache("petition_counts");			
			break;
		case "imap":
			$subop=httpget('subop');
			$see=httpget('see');
			addnav("Actions");
			addnav("Check IMAP (unseen)","runmodule.php?module=mailfrompetition&op=imap&petition=$id");
			addnav("Check IMAP (all)","runmodule.php?module=mailfrompetition&op=imap&see=all&petition=$id");
			// Create PhpImap\Mailbox instance for all further actions
			$mailbox = new PhpImap\Mailbox(
					'{'.get_module_setting('imaphostname').':'.get_module_setting('imapport').'/imap/'.get_module_setting('imapprotocol').'}INBOX', // IMAP server and mailbox folder
					get_module_setting('imapusername'), // Username for the before configured mailbox
					get_module_setting('imappassword'), // Password for the before configured username
					'', // Directory, where attachments will be saved (optional)
					'UTF-8', // Server encoding (optional)
					true, // Trim leading/ending whitespaces of IMAP path (optional)
					false // Attachment filename mode (optional; false = random filename; true = original filename)
					);

			// set some connection arguments (if appropriate)
			$mailbox->setConnectionArgs(
					CL_EXPUNGE // expunge deleted mails upon mailbox close
					//					| OP_SECURE // don't do non-secure authentication
					);



			try {
				// Get all emails (messages) or unseen
				// PHP.net imap_search criteria: http://php.net/manual/en/function.imap-search.php
				if ($see=="all")
					$mailsIds = $mailbox->searchMailbox('ALL');
					else
					$mailsIds = $mailbox->searchMailbox('UNSEEN');
			} catch(PhpImap\Exceptions\ConnectionException $ex) {
				debug("IMAP connection failed: " . implode(",", $ex->getErrors('all')));
				page_footer();
			}

			// If $mailsIds is empty, no emails could be found
			if(!$mailsIds) {
				output('Mailbox is empty or no mails found to display with present filter.');
				page_footer();
			}

			//mark as read before reading
			if($subop=="markseen") {
				$mailbox->markMailAsRead(httpget('mailid'));
				$mailsIds = array_diff($mailsIds,array(httpget('mailid')));
			}
			if($subop=="markunseen") {
				$mailbox->markMailAsUnRead(httpget('mailid'));
				$mailsIds[]=httpget('mailid');
			}

			rsort($mailsIds);
			$textseen=translate_inline("[Mark seen]");
			$textunseen=translate_inline("[Mark unseen]");
			output("------------------------------------------------------------------------------------------------`n`n");	
			foreach ($mailsIds as $mailid) {
				// Get the first message
				// If '__DIR__' was defined in the first line, it will automatically
				// save all attachments to the specified directory
				$mail = $mailbox->getMail($mailid);

				// Show, if $mail has one or more attachments
				output("\nMail has attachments? ");
				if($mail->hasAttachments()) {
					output("Yes");
				} else {
					output("No");
				}
				output("`n");	
				$header = $mailbox->getMailHeader($mailid);

				output("`\$Date: %s`0`n",$header->date);
				output("`2From: %s`0",$header->fromName);
				if ($header->isSeen == false) {
				rawoutput("<a href='runmodule.php?module=mailfrompetition&op=imap&subop=markseen&petition=$id&mailid=".$mailid."'>".$textseen."</a><br/>");
				addnav("","runmodule.php?module=mailfrompetition&op=imap&subop=markseen&petition=$id&mailid=".$mailid);
				} else {
				rawoutput("<a href='runmodule.php?module=mailfrompetition&op=imap&subop=markunseen&see=$see&petition=$id&mailid=".$mailid."'>".$textunseen."</a><br/>");
				addnav("","runmodule.php?module=mailfrompetition&op=imap&subop=markunseen&see=$see&petition=$id&mailid=".$mailid);
				}
				output("`2To: %s`0`n",implode($header->to,","));
				output("`2Subject: %s`0`n",$header->subject);
				$mail = $mailbox->getMail($mailid,false);

				if ($mail->textHtml) {
					$body = html_entity_decode($mail->textHtml, ENT_COMPAT, getsetting("charset", "ISO-8859-1"));
					$body = mailfrompetition_plainText($body);
					$body = color_sanitize($body);
				} else {
					$body = $mail->textPlain;
				}

				output("`2Message: `0`n`4%s`0`n",$body);
				output("------------------------------------------------------------------------------------------------`n`n");	

				// Print all attachements of $mail
				//debug("\n\nAttachments:\n".print_r($mail->getAttachments(),true));
			}
			if (count($mailsIds)==0) {
				output("Sorry chief, no unseen mail in that mailbox!");
			}
			$mailbox->disconnect();

	}
	page_footer();
}

function emailfrompetitions_insert($text) {
	$id=httpget('petition');
	require_once("lib/commentary.php");
	injectcommentary("pet-$id","",$text);
	return;
}

function mailfrompetition_sendmail($to, $toname, $body, $subject, $fromaddress, $fromname, $ccmail, $attachments=false)
{
	if ($ccmail!='') {
		$ccmails=",$ccmail";
	} else $ccmails='';

	require_once("lib/sendmail.php");
	$to_array=array($to=>$toname);
	$from_array=array($fromaddress=>$fromname);
	$cc_array=array($fromaddress=>$fromname);
	if (isset($ccmail) && $ccmail!="") $cc_array[$ccmail]=$ccmail;
	$mail_sent = send_email($to_array,$body,$subject,$from_array,$cc_array,"text/html");
	return $mail_sent;
}
function mailfrompetition_plainText($text)
{
	$text = str_replace("<br/>","`n",$text);
	$text = strip_tags($text, '<br><p><li>');
	$text = preg_replace ('/<[^>]*>/', PHP_EOL, $text);

	return $text;
}