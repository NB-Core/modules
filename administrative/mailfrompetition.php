<?php
/*

 */

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
			addnav("Actions");
			addnav("Email this user","runmodule.php?module=mailfrompetition&op=mail&petition=$id");

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
			} else {
				$sql_p="SELECT emailaddress FROM ".db_prefix('accounts')." WHERE acctid='$author'";
				$result_p=db_query($sql_p);
				$row_p=db_fetch_assoc($result_p);
				$email=$row_p['emailaddress'];
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
			output("`qFrom: %s (%s)`n",$adminname,$adminmail);
			output("To: %s`n",$email);
			output("CC: %s %s`n`n",$adminmail,($ccmail?"(+$ccmail)":''));
			output("Subject:");
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
			output("`n`n`\$Note: All email who are sent from here go CC to %s!",$adminmail);
			break;
		case "send":
			$to=httppost('email');
			$subject=stripslashes(httppost('subject'));
			$body=stripslashes(htmlentities(httppost('body'),ENT_COMPAT,getsetting('charset','ISO-8859-1')));
			$body=str_replace("\n","<br/>",$body);
			output("`4Sent to: %s`n",$to);
			output("CC: %s %s`n",$adminmail,($ccmail?"(+$ccmail)":''));
			output("Subject: %s`n`n",$subject);
			output("Body:`n%s",$body,true);
			mailfrompetition_sendmail($to,$body,$subject,$adminmail,$adminname,$ccmail);
			emailfrompetitions_insert(translate_inline("/me mailed concerning this petition"));
			invalidatedatacache("petition_counts");			
			break;

	}
	page_footer();
}

function emailfrompetitions_insert($text) {
	$id=httpget('petition');
	require_once("lib/commentary.php");
	injectcommentary("pet-$id","",$text);
	return;
}

function mailfrompetition_sendmail($to, $body, $subject, $fromaddress, $fromname, $ccmail, $attachments=false)
{
	if ($ccmail!='') {
		$ccmails=",$ccmail";
	} else $ccmails='';

	require_once("lib/sendmail.php");
	$to_array=array($to=>$to);
	$from_array=array($fromaddress=>$fromname);
	$cc_array=array($fromaddress=>$fromname);
	if (isset($ccmail) && $ccmail!="") $cc_array[$ccmail]=$ccmail;
	$mail_sent = send_email($to_array,$body,$subject,$from_array,$cc_array,"text/html");
	return $mail_sent;
}
?>
