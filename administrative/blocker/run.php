<?php
	global $session;
	require_once("lib/showform.php");
	$table_emails=db_prefix('blocker_emails');
	page_header("Blocker");
	$op=httpget('op');
	$subop=httpget('subop');
	require_once("lib/superusernav.php");
	
	superusernav();
	addnav("Actions");
	addnav("Home","runmodule.php?module=blocker");
	addnav("Legacyfill","runmodule.php?module=blocker&subop=legacyfill");
	addnav("Convert Email to Hash","runmodule.php?module=blocker&op=hashconvert");
	addnav("Add new email","runmodule.php?module=blocker&op=add");
	
	
	switch ($subop) {


		case "legacyfill":
			$emails=blocker_legacy_filltable();
			if ($emails===false) {
				output("`\$ERROR, nothing inserted probably and the setting was not deleted.");
			} elseif (count($emails)>0) {
				output("`\$All done, old settings put into the new table with default reason.`n`nEmails: %s`n`n",implode(", ",$emails));
			} else {
				output("`4No new email addresses were found.`n`n");
			}
			break;
	
		case "addsave":
		
			$email=rawurldecode(httppost('email'));
			$email=hash('sha512',$email.get_module_setting('email_hash_salt','charrestore')); //salt and hash
			$reason=addslashes(httppost('reason'));
			$sql="INSERT INTO ".$table_emails." (emailaddress,reason) VALUES ('$email','$reason')";
			$result=db_query($sql);
			if (db_affected_rows($result)>0) {
				output("%s inserted...`n`n",$email);
			} else {
				output("`\$Error for %s, no row affected!",$email);
			}
			break;
	
		case "delsave":
			$email=rawurldecode(httpget('email'));
			$email=hash('sha512',$email.get_module_setting('email_hash_salt','charrestore')); //salt and hash
			$reason=addslashes(httppost('reason'));

			if (httppost('delete')) {
				$sql="DELETE FROM ".$table_emails." WHERE emailaddress='$email'";
				$result=db_query($sql);
				if (db_affected_rows($result)>0) {
					output("%s removed...`n`n",$email);
				} else {
					output("`\$Error for %s, no row affected!",$email);
				}
				break;
			}
				
		
			$sql="UPDATE ".$table_emails." SET reason='$reason' WHERE emailaddress='$email'";
			$result=db_query($sql);
			if (db_affected_rows($result)>0) {
				output("%s has a new reason now...`n`n",$email);
			} else {
				output("`\$Error changing reason for %s, no row affected!",$email);
			}
			break;
		default:
	}
	
	switch ($op) {

		case "hashconvert":
			
			$convert = (int)httpget('convert'); // == 1 if we want to convert
			$count = 0;
			//fetch them to sort the directory 
			$sql = "SELECT * FROM ".$table_emails;
			$result=db_query($sql);
			$totalcount = db_num_rows($result);
			while ($row=db_fetch_assoc($result)){
				$email_acc=$row['emailaddress'];
				if (strlen($email_acc)==strlen(hash('sha512','test'.get_module_setting('email_hash_salt','charrestore'))) && strpos($email_acc,'@')===false) {
					continue; //already hashed and salted or superlong email
				} else {
					//found one hit, now count up and convert if necessary
					if ($convert==1) {
						//convert this one
						$hashed_mail=hash('sha512',$email_acc.get_module_setting('email_hash_salt','charrestore'));
						$sql2 = "UPDATE ".$table_emails." SET emailaddress='".$hashed_mail."' WHERE emailaddress='".$email_acc."';";
						$result2 = db_query($sql2);
					}
					$count++;
				}
			}

			if ($convert==1) {
				output("`q%s Chars saved in total. `n`x%s Chars have been converted.`n`n",$totalcount,$count);
			} else {
				output("`q%s Chars saved in total. `n`2%s Chars have `\$NO SALTED PASSWORD HASH`2 and should be converted now.`n`n",$totalcount,$count);
			}
			addnav("Convert");
			if ($count>0) {
				// we need to convert
				output("`\$In case you choose to convert, we advise to backup your data first in case something goes awry during this!!!");
				addnav("Convert now","runmodule.php?module=blocker&op=hashconvert&convert=1");
			} else {
				output("`xNo conversion necessary. All emails are salted and hashed.");
				addnav("Convert now","");
			}	

			break;

		case "add":	
			output("Add a new email to the blocklist:`n`n");

			rawoutput("<form action='runmodule.php?module=blocker&subop=addsave' method='POST'>");
			addnav("","runmodule.php?module=blocker&subop=addsave");
			$data = array(
				"email"=>"Emailaddress to block,text",
				"reason"=>"Reason,textarea"
				);
			$current = array(
				"email"=>"",
				"reason"=>"Pissed me off",

				);
				
			$layout = showform($data,$current);
			$save = translate_inline("Save");
			rawoutput("</form>");
			break;	
	
		default:
		
		output("`c`tOverview of blocked user mails including notes");
		$sql="SELECT * FROM $table_emails ORDER BY emailaddress ASC";
		$result=db_query($sql);
		
		$emailaddress=translate_inline("Email Address");
		$reason=translate_inline("Reason");
		$submit=translate_inline("Submit");
		$delete=translate_inline("Delete");
		
		rawoutput("<table>");
		rawoutput("<tr class='trhead'><td>$emailaddress</td><td>$reason</td></tr>");
		$i=0;
		while ($row=db_fetch_assoc($result)) {
			$i=!$i;
			$class=($i?"trlight":"trdark");
			rawoutput("<tr class='$class'><td>");
			rawoutput($row['emailaddress']);
			rawoutput("</td><td>");
			rawoutput("<form action='runmodule.php?module=blocker&subop=delsave&email=".rawurlencode($row['emailaddress'])."' method='POST'>");
			addnav("runmodule.php?module=blocker&subop=delsave&email=".rawurlencode($row['emailaddress']));
			rawoutput("<textarea name='reason' cols='40' rows='2'>");
			rawoutput($row['reason']);
			rawoutput("</textarea><br/><input type='submit' name='delete' class='button' value='$delete'>&nbsp;&nbsp;&nbsp;<input type='submit' class='button' value='$submit'></form>");
			rawoutput("</td></tr>");
		}
		rawoutput("</table>");
		output_notl("`c");
	}
	
	page_footer();
?>
