<?php

function donationreceipt_getmoduleinfo(){
    $info = array(
        "name"=>"Donation Receipt + Thanks",
        "version"=>"1.0",
        "author"=>"`2Oliver Brendel",
        "category"=>"Donation",
        "download"=>"",
        "settings"=>array(
            "Donation Receipt Settings,title",
            "servername,Game name in the mail (empty: server URL)|",
            "fantype,Players are fans of ...|LoGD",
            "adminname,Signature at the end of the mail|The Staff",
            "adminmail,Sender and reply address (empty: game admin email)|",
            "sitename,Site address named in the mail (empty: server URL)|",
            "subject,Mail subject (empty: thanks for your donation to the game name)|",
        ),
    );
    return $info;
}

function donationreceipt_install(){
	module_addhook("donation-processed");
	module_addhook("donation-error");
    return true;
}

function donationreceipt_uninstall(){
    return true;
}

function donationreceipt_dohook($hookname,$args){
    global $session;
    switch($hookname){
		case "donation-processed": case "donation-error":
		$amount = httppost('mc_gross');
		$currency = httppost('mc_currency');
		$payer_email = httppost('payer_email');
		$sent=httppost('payment_date');
		$paymentstatus=httppost('payment_status');
		$received=date("H:i:s M j, Y T");
		$firstname=httppost('first_name');
		$lastname=httppost('last_name');
		$tid=httppost('txn_id');
		$type=httppost('txn_type');
		if ($type!='web_accept') return $args; // no receipt for other stuff
		if ($payer_email=='') {
			require_once("lib/gamelog.php");
			gamelog("could not process payment due to empty payer email $firstname|$lastname");
			break;
		}
		
		$serverurl = getsetting('serverurl', '');
		$server = trim((string) get_module_setting('servername'));
		if ($server === '') $server = $serverurl;
		$fantype = (string) get_module_setting('fantype');
		$adminname = (string) get_module_setting('adminname');
		$adminmail = trim((string) get_module_setting('adminmail'));
		if ($adminmail === '') $adminmail = getsetting('gameadminemail', 'postmaster@localhost.com');
		$sitename = trim((string) get_module_setting('sitename'));
		if ($sitename === '') $sitename = $serverurl;
		$subject = trim((string) get_module_setting('subject'));
		if ($subject === '') $subject = "Thank you for your donation to $server";
		// The payer controls the name fields; keep them from injecting markup into the HTML mail.
		$firstname = htmlspecialchars((string) $firstname, ENT_QUOTES);
		$lastname = htmlspecialchars((string) $lastname, ENT_QUOTES);
		$text = array (
				"<span style='font-family: Verdana, Arial; font-size: 12'> Dear %s %s, `n`nthank you for your donation to %s.`n`nWith your donation we can keep up an ad-free server for all %s fans who want to enjoy Roleplay with the characters they like.`n`nThis game would not be possible without people like you and their support for this game.`n`n",
							$firstname,
							$lastname,
							$server,
							$fantype,
				);
		$mailbody=sprintf_translate($text);
		$text = array(" 
				We confirm the following details:`n`nPayment sent: <span style='font-weight:bolder;'>%s</span>`nPayment received: <span style='font-weight:bolder;'>%s</span>`nTransaction ID: <span style='font-weight:bolder;'>%s</span>`nPayment Status: <span style='font-weight:bolder;'>%s</span>`nAmount: <span style='font-weight:bolder;'>%s %s</span>`n`n",
							$sent,
							$received,
							$tid,
							$paymentstatus,
							$currency,
							$amount,
				);
		$mailbody.=sprintf_translate($text);
		$text = array ("
				If there are any problems or if you have questions, don't hesitate to petition at %s or contact me via email as <a href='mailto:%s'> site admin </a>.`n`nSincerely,`n<span style='font-weight:bold;'>%s</span>`n`n", 
				$sitename,
				$adminmail,
				$adminname,
				);
		$mailbody.=sprintf_translate($text);
		if (strtolower($paymentstatus)=="pending") {
			$mailbody.="<span style='1.5em'>Note: Your echeck payment is status 'pending' until it gets verified by paypal. This takes up to 5 working days. Once we have it clear, points are given out automatically and another one of these mails will be sent.</span>";
		}
		$mailbody = str_replace("`n","<br>",$mailbody);
		require_once("lib/sendmail.php");
		send_email(array($payer_email=>"$firstname $lastname"),$mailbody,$subject,array($adminmail=>$server),array($adminmail=>$server),"text/html");
		break;
		}
    return $args;
}

function donationreceipt_run () {

}

?>
