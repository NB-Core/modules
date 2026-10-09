<?php
switch($op) {
	case "write":
		$subject=httppost('subject');
		$body=httppost('body');
		$limit=(int)getsetting('mailsizelimit',1024);
		$sent=false;
		// Only the posted form with its token sends; anything else is a preview.
		if (httppost('submit') && clanmail_validpost()) {
			//send
			$subject=str_replace("`n","",httppost('subject'));
			$body=str_replace("`n","\n",httppost('body'));
			$body=str_replace("\r\n","\n",$body);
			$body=str_replace("\r","\n",$body);
			$body=addslashes(substr(stripslashes($body),0,$limit));
			require_once("lib/systemmail.php");
			$sql="SELECT acctid FROM ".db_prefix('accounts')." WHERE clanid=".$session['user']['clanid']." AND clanrank>".CLAN_APPLICANT.";";
			$result=db_query($sql);
			$recipients=array();
			while ($row=db_fetch_assoc($result)) {
				$recipients[]=$row['acctid'];
			}
			$cost=count($recipients)*get_module_setting('mailcostgold');
			if ($cost>$session['user']['gold']) {
				output("`\$You cannot afford the delivery: it costs `^%s gold`\$.`n`n",$cost);
			} else {
				foreach ($recipients as $acctid) {
					systemmail($acctid,$subject,$body,$session['user']['acctid']);
				}
				$session['user']['gold']-=$cost;
				$sent=true;
				output("`\$Mail has been sent! Paid `^%s gold`\$ for the delivery.`n`n",$cost);
				debuglog("paid $cost gold for sending a clan mail");
			}
		}
		addnav("Refresh main form","runmodule.php?module=clanmail&op=write");

		
		if ($body) {
			rawoutput("<hr noshade width='100%' size='3'>");
			if (httppost('submit') && $sent) output("`iSent Mail`i:`n`n");
				else output("`i`4Preview`i:`n`n");
			output("`4From:`# %s`0`n`n",$session['user']['name']);			
			if ($subject=='') $sub=translate_inline("`i(No Subject)`i","mail");
				else $sub=$subject;
			if (strlen($body)>$limit) {
				$body=substr($body,0,$limit-1);
			}
			output("`4Subject:`# `n%s`n`n",$sub);
			output("`4Body:`# `n%s`n`n",$body);
			rawoutput("<hr noshade width='100%' size='3'>");

		}
		
		output("`7Write down the subject and the body (You may not exceed %s chars)!`n`n",$limit);
		rawoutput("<form action='runmodule.php?module=clanmail&op=write' method='post'>".clanmail_tokenfield());
		addnav("","runmodule.php?module=clanmail&op=write");
		output("`7From: %s`0`n`n",$session['user']['name']);
		output("`7Subject:`n");
		rawoutput("<input type='input' class='input' length=255 name='subject' value='$subject'><br>");
		output("`7Body:`n");
		//taken from mail.php for laziness and convenience and coherence
		rawoutput("<textarea name='body' id='textarea' class='input' cols='60' rows='9' onKeyUp='sizeCount(this);'>".HTMLEntities(stripslashes($body), ENT_COMPAT, getsetting("charset", "ISO-8859-1"))."</textarea><br>");
		$send = translate_inline("Send");
		$preview=translate_inline("Preview");
		rawoutput("<table border='0' cellpadding='0' cellspacing='0' width='100%'><tr><td><input type='submit' class='button' name='submit' value='$send'></td><td><input type='submit' class='button' name='preview' value='$preview'><td align='right'><div id='sizemsg'></div></td></tr></table>");
		output_notl("</form>",true);
		$sizemsg = "`#Max message size is `@%s`#, you have `^XX`# characters left.";
		$sizemsg = translate_inline($sizemsg);
		$sizemsg = sprintf($sizemsg,$limit);
		$sizemsgover = "`\$Max message size is `@%s`\$, you are over by `^XX`\$ characters!";
		$sizemsgover = translate_inline($sizemsgover);
		$sizemsgover = sprintf($sizemsgover,$limit);
		$sizemsg = explode("XX",$sizemsg);
		$sizemsgover = explode("XX",$sizemsgover);
		$usize1 = addslashes("<span>".appoencode($sizemsg[0])."</span>");
		$usize2 = addslashes("<span>".appoencode($sizemsg[1])."</span>");
		$osize1 = addslashes("<span>".appoencode($sizemsgover[0])."</span>");
		$osize2 = addslashes("<span>".appoencode($sizemsgover[1])."</span>");
				rawoutput("
		<script language='JavaScript'>
				var maxlen = ".$limit.";
				function sizeCount(box){
						var len = box.value.length;
						var msg = '';
						if (len <= maxlen){
								msg = '$usize1'+(maxlen-len)+'$usize2';
						}else{
								msg = '$osize1'+(len-maxlen)+'$osize2';
						}
						document.getElementById('sizemsg').innerHTML = msg;
				}
				sizeCount(document.getElementById('textarea'));
				</script>");
		//end take
		output("`n`7Costs will be deducted after you hit 'Submit'`n");
		output("`n`iNotes:`n`\$State clearly what you want by this mass-mail. The user won't have the impression it is an automated mail, for your convenience. So state 'Clan Mail: ' or something in the subject`i");
		break;		
	default:
		output("`c`b`^Clan Mail`c`b");
		output_notl("`n`n");
		output("`7If you want to send a message to all your clanmembers, this is the right place. The message will be delivered by the mail nin immediately at little cost. You will receive a copy of the mail.`n`n");
		// Same recipients and price as the send branch: every accepted member, the sender's copy included.
		$sql="SELECT count(acctid) as membercount FROM ".db_prefix('accounts')." WHERE clanid=".$session['user']['clanid']." AND clanrank>".CLAN_APPLICANT;
		$result=db_query_cached($sql,"clanmail_members_".$session['user']['clanid'],600);
		$row=db_fetch_assoc($result);
		$costs=get_module_setting('mailcostgold')*abs($row['membercount']);
		output("The mail to all your comrades costs `^%s gold`7 due to more work for the mail nin. It is free if you send it one by one via the shinobi mail.",$costs);
		if ($session['user']['gold']>=$costs) {
			addnav("Write");
			addnav("Write a new mail to your comrades","runmodule.php?module=clanmail&op=write");
		} else {
			output("`n`n`\$Sadly, you don't have the sufficient funds to pay for the delivery...`n`n");
		}
		break;
		
}
?>