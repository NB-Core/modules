<?php	
$user=$session['user']['acctid'];

$subop=httpget('subop');
page_header('Shadowy Meadows');
output("`c`b`)S`%hadowy `xMeadows`b`c`n`n");
addnav("Navigation");
addnav("Back to the Gardens","gardens.php");
addnav("Back to the Meadows","runmodule.php?module=marriage&op=meadows");
if ($subop!='') {
	addnav("Flirting");
	addnav("Back to Flirting","runmodule.php?module=marriage&op=flirt");
}
switch ($subop) {
	
	case "responseto":

		if (httppost('send')) {
			$response=httppost('response');
			$id=(int)httppost('id');
			$successful=(int)httppost('successful');
			$response=$this->mystrip($response);
			$sql="SELECT initiator FROM ".db_prefix('marriage_actions')." WHERE id=$id;";
			$result=db_query($sql);
			$row=db_fetch_assoc($result);
			
			modulehook("marriage_flirtsend",array("sender"=>$user,"target"=>$row['initiator'],"message"=>$response));
			require_once("lib/systemmail.php");
			$re=$session['user']['name'].sprintf_translate(" answered to your flirt... `n`4Here the reponse:`0`n`n").$response;

			systemmail($row['initiator'],array("`xSomebody answered your flirt..."),$re);
			$sql="UPDATE ".db_prefix('marriage_actions')." SET response='".addslashes($response)."', successful=$successful,responsedate='".date("Y-m-d H:i:s")."' WHERE id=$id LIMIT 1;";
			db_query($sql);debug($sql);
			invalidatedatacache("marriage_flirtpartnerlist_".$user);
			invalidatedatacache("marriage_flirtpartnerlist_".$row['initiator']);
			output("`4Reponse delivered... once you both have enough positive flirts, you may even get married someday...`n`n");
			break;
		}
		
		$text='';
		
		if (httppost('preview')) {
			$text=httppost('response');
			$message=stripslashes($text);
			output("Preview:`n`n");
			output_notl("`c".$this->mystrip($message)."`c`n`n");
		}
		$id=(int)httpget('id');
		$sql="SELECT a.name,a.acctid FROM ".db_prefix('accounts')." AS a INNER JOIN ".db_prefix('marriage_actions')." AS b ON a.acctid=b.initiator WHERE b.id=$id LIMIT 1;";
		$result=db_query($sql);
		$row=db_fetch_assoc($result);
		rawoutput("<form action='runmodule.php?module=marriage&op=flirt&subop=responseto' method='POST'><center>");
		addnav("","runmodule.php?module=marriage&op=flirt&subop=responseto");
		output("`RPlease enter your response to %s`R...`n`n",$row['name']);
		output("How do you feel about this flirt? ");
		rawoutput("<select name='successful'>");
		$reactions=$this->flirt_reaction(0,$session['user']['sex'],true);
		$length=count($reactions);
		for ($i=0;$i<$length;$i++) {
			rawoutput("<option value='$i'>".$reactions[$i]."</option>");
		}
		rawoutput("</select>");
		require_once("modules/marriage/forms.php");
		$box='response';
		marriage_charsleft($box);
		$l=3000;
		rawoutput("<br><table><tr><td colspan='2'><textarea name='$box' id='input$box' onKeyUp='previewtext$box(document.getElementById(\"input$box\").value,$l);' cols='50' rows='20' size='3000' wrap='soft'>$message</textarea></td></tr>");
		rawoutput("<input type='hidden' name='id' value='$id'><br>");
		$submit=translate_inline("Flirt!");
		$preview=translate_inline("Preview!");
		rawoutput("<tr><td><input type='submit' class='button' name='preview' value='$preview'></td><td align='right'><input type='submit' class='button' name='send' value='$submit'></td></tr></table>");
		rawoutput("</center></form>");
		output("`i`\$Note: No center tags, no bold tags, no italic tags, those will be removed.`n`xAlso mind the timeout!`i");
		break;
		
	case "response":
		output("`RHere are the people who tried flirting with you (click to answer):`n`n");
		$sql="SELECT a.name as name,b.id as id,b.message as message FROM ".db_prefix('marriage_actions')." AS b INNER JOIN ".db_prefix('accounts')." AS a ON a.acctid=b.initiator WHERE responsedate='0000-00-00 00:00:00' AND receiver=".$user." ORDER BY initiator ASC;";
		$result=db_query($sql);debug($sql);
		$name=translate_inline("Name");
		$message=translate_inline("Message");
		rawoutput("<center><table cellpadding='3' cellspacing='0' border='0' ><tr class='trhead'><td>$name</td><td>$message</td></tr>");
		while ($row=db_fetch_assoc($result)) {
			$class=($class=='trlight'?'trdark':'trlight');
			rawoutput("<tr class='$class'><td>");
			output_notl("<a href='runmodule.php?module=marriage&op=flirt&subop=responseto&id=".$row['id']."'>".$row['name']."</a>",true);
			addnav("","runmodule.php?module=marriage&op=flirt&subop=responseto&id=".$row['id']);
			rawoutput("</td><td>");
			output_notl($row['message']);
			rawoutput("</td></tr>");
		}
		rawoutput("</table></center>");
		break;

	case "flirt":
		$target=(int)httppost('target');
		if ($target==0) $target=(int)httpget('target');

		if ($target==0) {
			output("`xSorry, cannot find the person you are looking for...`n`n");
			break;
		}
		
		if (httppost('send')) {
			$message=httppost('message');
			$target=(int)httppost('target');
			$sql="SELECT initiator FROM ".db_prefix('marriage_actions')." WHERE initiator=".$user." AND receiver=$target AND responsedate='0000-00-00 00:00:00';";
			$result=db_query($sql);debug($sql);
			if (db_num_rows($result)>0) {
				output("`xYou must be some kind of flirt maniac... wait for a response first...`n`n");
				break;
			}
			
			modulehook("marriage_flirtsend",array("sender"=>$user,"target"=>$target,"message"=>$message));
			
			$message=$this->mystrip($message);
			require_once("lib/systemmail.php");
			$me=$session['user']['name'].translate_inline(" approached you... please respond to this in the meadows flirt section. `n`4Now the message:`0`n`n").$message;
			systemmail($target,array("`RSomebody approached you..."),$me);
			$sql="INSERT INTO ".db_prefix('marriage_actions')." (initiator,receiver,message,date) VALUES (".$user.",$target,'".addslashes($message)."','".date("Y-m-d H:i:s")."');";
			db_query($sql);
			output("`4Message delivered... you can only hope for a positive response... if you want to send flowers or a card, remember Tsubaki...`n`n");
			increment_module_pref('flirts_today',1);
			break;
		}
		
		$text='';
		
		if (httppost('preview')) {
			$text=httppost('message');
			$message=stripslashes($text);
			output("Preview:`n`n");
			output_notl("`c".$message=$this->mystrip($message)."`c`n`n");
		}
		
		if (get_module_pref('flirts_today')>=get_module_setting('maxflirts')) {
			output("`xYou must be some kind of flirt maniac... you feel exhausted for today, going around and trying to socialize... come again the next game day. `\$=)");
			break;
		}
		//check if he/she is ignoring the users tries
		$sql="SELECT * FROM ".db_prefix('marriage_ignorelist')." WHERE target='".$user."' AND player='$target';";
		$result=db_query($sql);
		$rows=db_num_rows($result);
		if ($rows>0) {
			output("`5Sorry, but your attempt is denied on the first try.`n`n`\$That person ignores all flirting initated by you.`n`n");
			break;
		}

		//check if our flirter is ignoring that users tries
		$sql="SELECT * FROM ".db_prefix('marriage_ignorelist')." WHERE player='".$user."' AND target='$target';";
		$result=db_query($sql);
		$rows=db_num_rows($result);
		if ($rows>0) {
			output("`5Sorry, but you do ignore that player. Please un-ignore first before trying to flirt!`n`n");
			break;
		}				
		
		//check if already a flirt is awaiting an answer
		$sql="SELECT initiator FROM ".db_prefix('marriage_actions')." WHERE initiator=".$user." AND receiver=$target AND responsedate='0000-00-00 00:00:00';";
		$result=db_query($sql);
		if (db_num_rows($result)>0) {
			output("`xYou must be some kind of flirt maniac... wait for a response first...`n`n");
			break;
		}				
		
		$sql="SELECT name,acctid FROM ".db_prefix('accounts')." WHERE acctid=$target LIMIT 1;";
		$result=db_query($sql);
		$row=db_fetch_assoc($result);
		rawoutput("<form action='runmodule.php?module=marriage&op=flirt&subop=flirt' method='POST'><center>");
		addnav("","runmodule.php?module=marriage&op=flirt&subop=flirt");
		output("`RYou want to flirt with %s`R... please write something that would maybe even you find interesting as a flirt... if this is the 'first contact', keep in mind that the other person does not know you yet!`n`n`\$Flirting does not mean 'using vulgar language'. Stay romantic and talk nicely, if you abuse the flirting, you will be punished.`n`n",$row['name']);
		require_once("modules/marriage/forms.php");
		$box='message';
		marriage_charsleft($box);
		$l=3000;
		rawoutput("<br><table><tr><td colspan='2'><textarea name='$box' id='input$box' onKeyUp='previewtext$box(document.getElementById(\"input$box\").value,$l);' cols='50' rows='20' size='3000' wrap='soft'>$message</textarea></td></tr>");
		rawoutput("<input type='hidden' name='target' value='$target'>");
		$submit=translate_inline("Flirt!");
		$preview=translate_inline("Preview!");
		rawoutput("<tr><td><input type='submit' class='button' name='preview' value='$preview'></td><td align='right'><input type='submit' class='button' name='send' value='$submit'></td></tr></table>");
		rawoutput("</center></form>");
		output("`n`n`i`\$Note: No center tags, no bold tags, no italic tags, those will be removed.`n`xAlso mind the timeout!`i");
		break;
	case "search":
		$gender=httpget('gender');
		output("`xAs you roam through the meadows, you catch some sentences from people who might be interesting...if you want more, just visit the meadows yourself...`n`n");
		$sql="SELECT DISTINCT a.name as name,a.acctid as acctid,b.comment as comment,c.acctid as optout FROM ".db_prefix('commentary')." AS b INNER JOIN ".db_prefix('accounts')." AS a ON a.acctid=b.author LEFT JOIN ".db_prefix('marriage_optout')." AS c ON b.author=c.acctid WHERE a.sex=$gender AND a.acctid!=".$user." AND b.section LIKE 'meadow-%' ORDER BY commentid DESC LIMIT $limit;";
		//$result=db_query_cached($sql,"meadows-search-$gender",60);
		$result=db_query($sql);
		while ($row=db_fetch_assoc($result)) {
			if (((int)$row['optout'])>0) continue; //target not interested in flirting
			$link="<a href='runmodule.php?module=marriage&op=flirt&subop=flirt&target=".$row['acctid']."'>".$row['name']."</a>";
			addnav("","runmodule.php?module=marriage&op=flirt&subop=flirt&target=".$row['acctid']);
			if (substr($row['comment'],0,3)=='/me') {
				$add=$link.' '.substr($row['comment'],3).'`n';
			} elseif (substr($row['comment'],0,2)==='::') {
				$add=$link.' '.substr($row['comment'],2).'`n';
			} elseif (strpos($row['comment'],':')!==false) {
				$add=$link.' '.substr($row['comment'],1).'`n';
			} else {
				$add="`q".$link."`q says, \"`2".$row['comment']."`q\"`n";
			}
			$lastlines.=$add;
		}
		output_notl($lastlines,true);
		break;
	case "selectprecisely":
		$target=httppost('target');
		$ta=addslashes($target);
		output("`c`b`tFind a flirt`0`b`c`n`n");
		if ($target!='') {
			$sql="SELECT a.name as name,a.acctid FROM ".db_prefix('accounts')." AS a WHERE (name LIKE '%$ta%' OR login LIKE '%$ta') AND a.acctid!=$user limit $limit;";
			$result=db_query($sql);
			if (db_num_rows($result)<1) {
				$end=strlen($target);
				$search='%';
				for ($x=0;$x<$end;$x++){
					$search .= substr($target,$x,1)."%";
				}
				$sql="SELECT a.name as name,a.acctid FROM ".db_prefix('accounts')." AS a WHERE name LIKE '$search' OR LOGIN LIKE '$search' LIMIT $limit;";
				$result=db_query($sql);
			}
			if (db_num_rows($result)>0) {
				$name=translate_inline("Name");
				$message=translate_inline("Message");
				rawoutput("<center><table cellpadding='3' cellspacing='0' border='0' ><tr class='trhead'><td>$name</td></tr>");//<td>$message</td></tr>");
				while ($row=db_fetch_assoc($result)) {
					$class=($class=='trlight'?'trdark':'trlight');
					rawoutput("<tr class='$class'><td>");
					$link="<a href='runmodule.php?module=marriage&op=flirt&subop=flirt&target=".$row['acctid']."'>".$row['name']."</a>";
					addnav("","runmodule.php?module=marriage&op=flirt&subop=flirt&target=".$row['acctid']);
					output_notl($link,true);
					// rawoutput("</td><td>");
					// output_notl($row['message']);
					rawoutput("</td></tr>");
				}
				rawoutput("</table></center>");
			} else {
				output("`\$Sorry, I was unable to find anybody with that supplied name!`n`n");
			}
		}
		output("`xWhom are you looking for?`n`4Try to enter the name without the title, or completely to narrow down the search (results limited to %s hits).`2`n`n",$limit);
		rawoutput("<form action='runmodule.php?module=marriage&op=flirt&subop=selectprecisely' method='POST'>");
		addnav("","runmodule.php?module=marriage&op=flirt&subop=selectprecisely");
		rawoutput("<input type='input' length='50' name='target' value='".addslashes($target)."'><br>");
		$submit=translate_inline("Search!");
		rawoutput("<input type='submit' class='button' value='$submit'>");
		rawoutput("</form>");
		break;				
	case "select":
		output("`xDo you look for a male or female?");
		addnav("Look for...");
		addnav("A male","runmodule.php?module=marriage&op=flirt&subop=search&gender=".SEX_MALE);
		addnav("A female","runmodule.php?module=marriage&op=flirt&subop=search&gender=".SEX_FEMALE);
		break;
	case "ignore":
		output("`4So you want to block off somebody who constantly tries to approach you... well, please enter the name of the person you want to ignore:`n`n");
		rawoutput("<form action='runmodule.php?module=marriage&op=flirt&subop=ignoreprecisely' method='POST'>");
		addnav("","runmodule.php?module=marriage&op=flirt&subop=ignoreprecisely");
		rawoutput("<input type='input' length='50' name='target' value='".addslashes($target)."'><br>");
		$submit=translate_inline("Search!");
		rawoutput("<input type='submit' class='button' value='$submit'>");
		rawoutput("</form>");
		break;
	case "ignoreprecisely":
		$target=httppost('target');
		$ta=addslashes($target);
		output("`c`b`tIgnore somebody`0`b`c`n`n");
		if ($target!='') {
			$sql="SELECT a.name as name,a.acctid FROM ".db_prefix('accounts')." AS a WHERE name LIKE '%$ta%' OR LOGIN LIKE '%$ta' limit $limit;";
			$result=db_query($sql);
			if (db_num_rows($result)<1) {
				$end=strlen($target);
				$search='%';
				for ($x=0;$x<$end;$x++){
					$search .= substr($target,$x,1)."%";
				}
				$sql="SELECT a.name as name,a.acctid FROM ".db_prefix('accounts')." AS a WHERE name LIKE '$search' OR LOGIN LIKE '$search' LIMIT $limit;";
				$result=db_query($sql);
			}
			if (db_num_rows($result)>0) {
				$name=translate_inline("Name");
				$message=translate_inline("Message");
				$op=translate_inline("Ops");
				rawoutput("<center><table cellpadding='3' cellspacing='0' border='0' ><tr class='trhead'><td>$name</td><td>$message</td><td>$op</td></tr>");
				while ($row=db_fetch_assoc($result)) {
					$class=($class=='trlight'?'trdark':'trlight');
					rawoutput("<tr class='$class'><td>");
					output_notl($row['name']);
					rawoutput("</td><td>");
					rawoutput("<form action='runmodule.php?module=marriage&op=flirt&subop=ignore_now' method='POST'>");
					addnav("","runmodule.php?module=marriage&op=flirt&subop=ignore_now");
					rawoutput("<input type='hidden' name='target' value='".$row['acctid']."'><textarea name='reason' cols='20' rows='2' size='1000'></textarea> ");
					rawoutput("</td><td>");
					$submit=translate_inline("Ignore!");
					rawoutput("<input type='submit' class='button' value='$submit'>");
					rawoutput("</form>");
					rawoutput("</td></tr>");
				}
				rawoutput("</table></center>");
			} else {
				output("`\$Sorry, I was unable to find anybody with that supplied name!");
			}
		}
		output("`xWhom are you looking for?`n`4Try to enter the name without the title, or completely to narrow down the search (results limited to %s hits).`2`n`n",$limit);
		rawoutput("<form action='runmodule.php?module=marriage&op=flirt&subop=ignoreprecisely' method='POST'>");
		addnav("","runmodule.php?module=marriage&op=flirt&subop=ignoreprecisely");
		rawoutput("<input type='input' length='50' name='target' value='".addslashes($target)."'><br>");
		$submit=translate_inline("Search!");
		rawoutput("<input type='submit' class='button' value='$submit'>");
		rawoutput("</form>");
		break;
	case "ignore_now":
		$date=date("Y-m-d H:i:s");
		$target=(int) httppost('target');
		$reason=addslashes($this->mystrip(httppost('reason')));
		$sql="SELECT * FROM ".db_prefix('marriage_ignorelist')." WHERE player='".$user."' AND target='$target';";
		$result=db_query($sql);
		$row=db_fetch_assoc($result);
		output("`c`b`tIgnore somebody`0`b`c`n`n");
		if (db_num_rows($result)>0) {
			$none=translate_inline("None given");
			output("`xWell, you already ignored that person... your reason was: `n`n`5%s`n`n",($row['reason']!=''?$row['reason']:$none));
		} else {
			$sql="INSERT INTO ".db_prefix('marriage_ignorelist')." (player,target,reason,date) VALUES ('$user','$target','$reason','$date');";debug($sql);
			db_query($sql);
			output("`xOkay, you ignored that person with the reason:`n`n`5%s`n`n",$reason);					
		}
		break;
	case "unignore":
		$target=(int)httppost('target');
		$sql="DELETE FROM ".db_prefix('marriage_ignorelist')." WHERE player='$user' AND target='$target';";
		$result=db_query($sql); debug($sql);
		if ($result) output("`5Player unignored.");
			else output("Error! Please notify the staff with precise 'what did you do' information");
		break;
	case "ignorelist":
		$sql="SELECT a.name AS name,a.acctid AS acctid,b.reason AS reason,b.date AS date FROM ".db_prefix('marriage_ignorelist')." AS b INNER JOIN ".db_prefix('accounts')." AS a ON b.target=a.acctid WHERE b.player='$user' ORDER BY a.login ASC";
		$result=db_query($sql);
		$name=translate_inline("Name");
		$message=translate_inline("Message");
		$date=translate_inline("Date");
		$op=translate_inline("Ops");
		rawoutput("<center><table cellpadding='3' cellspacing='0' border='0' ><tr class='trhead'><td>$name</td><td>$message</td><td>$date</td><td>$op</td></tr>");				
		$class='';
		while ($row=db_fetch_assoc($result)) {
			$class=($class=='trlight'?'trdark':'trlight');
			rawoutput("<tr class='$class'><td>");
			output_notl($row['name']);
			rawoutput("</td><td>");
			output_notl($row['reason']);
			rawoutput("</td><td>");
			rawoutput("<form action='runmodule.php?module=marriage&op=flirt&subop=unignore' method='POST'>");
			addnav("","runmodule.php?module=marriage&op=flirt&subop=unignore");
			rawoutput("<input type='hidden' name='target' value='".$row['acctid']."'>");
			rawoutput("</td><td>");
			$submit=translate_inline("Unignore!");
			$confirmation=sprintf_translate("Do you really want to un-ignore %s?",sanitize($row['name']));
			rawoutput("<input type='submit' class='button' onClick='return confirm(\"$confirmation\");' value='$submit'>");
			rawoutput("</form>");
			rawoutput("</td></tr>");
		}
		if (db_num_rows($result)==0) {
			rawoutput("<tr><td colspan=4>");
			output("`\$You have not ignored anybody.`0");
			rawoutput("</td></tr>");
		}
		rawoutput("</table></center>");
		break;
	case "flirtpartnerlist":
/*				$sql="SELECT a.name AS name,a.acctid AS acctid FROM ".db_prefix('marriage_actions')." AS b LEFT JOIN ".db_prefix('accounts')." AS a ON b.receiver=a.acctid WHERE b.initiator='$user' 
			UNION 
			SELECT a.name AS name,a.acctid AS acctid FROM ".db_prefix('marriage_actions')." AS b LEFT JOIN ".db_prefix('accounts')." AS a ON b.initiator=a.acctid WHERE b.receiver='$user' ORDER BY name ASC";
*/	
		$sql="SELECT name,acctid,sum(med) as medi,sum(counter) as counteri FROM (SELECT a.name AS name,a.acctid AS acctid,avg(b.successful) as med, count(b.date) as counter FROM ".db_prefix('marriage_actions')." AS b LEFT JOIN ".db_prefix('accounts')." AS a ON b.receiver=a.acctid WHERE b.initiator='$user' AND responsedate!='0000-00-00 00:00:00' GROUP BY b.receiver
			UNION ALL
			SELECT a.name AS name,a.acctid AS acctid,avg(b.successful) as med, count(b.date) as counter FROM ".db_prefix('marriage_actions')." AS b LEFT JOIN ".db_prefix('accounts')." AS a ON b.initiator=a.acctid WHERE b.receiver='$user' AND responsedate!='0000-00-00 00:00:00' GROUP BY b.initiator ORDER BY name ASC) AS maintable GROUP BY acctid";
		$result=db_query_cached($sql,"marriage_flirtpartnerlist_$user");debug($sql);
		$name=translate_inline("Name");
		$view=translate_inline("View Flirt");
		$flirt=translate_inline("Flirt!");
		$bar=translate_inline("Status");
		$ops=translate_inline("Ops");
		$check=translate_inline("Check Marriage Status");
		rawoutput("<center><table cellpadding='3' cellspacing='0' border='0' ><tr class='trhead'><td>$name</td><td>$bar</td><td>$ops</td><td></td><td></td></tr>");				
		$class='';
		$minflirts=get_module_setting('m_minflirts');
		$averagerating=get_module_setting('m_pointsneeded');
		$old=0;
		while ($row=db_fetch_assoc($result)) {
			$class=($class=='trlight'?'trdark':'trlight');
			rawoutput("<tr class='$class'><td>");
			output_notl($row['name']);
			rawoutput("</td><td>");
			if ($row['counteri']<2) {
				output("`\$Only beginning...");
			} else {
				rawoutput($this->flirtbar($row['medi']/2));debug($row['counteri']);
			}
			rawoutput("</td><td><form action='runmodule.php?module=marriage&op=flirt&subop=flirtview' method='POST'>");
			addnav("","runmodule.php?module=marriage&op=flirt&subop=flirtview");
			rawoutput("<input type='hidden' name='target' value='".$row['acctid']."'>
					<input type='submit' class='button' value='$view'>
					</form></td><td>");
			rawoutput("<form action='runmodule.php?module=marriage&op=flirt&subop=flirt' method='POST'>");
			addnav("","runmodule.php?module=marriage&op=flirt&subop=flirt");					
			rawoutput("<input type='hidden' name='target' value='".$row['acctid']."'>
					<input type='submit' class='button' value='$flirt'>		
					</form></td>");
			if ($row['counteri']>=$minflirts && $row['medi']>=$averagerating && !$this->ismarried()) {
				rawoutput("<td><form action='runmodule.php?module=marriage&op=flirt&subop=marriagecheck' method='POST'>");
				addnav("","runmodule.php?module=marriage&op=flirt&subop=marriagecheck");
				rawoutput("<input type='hidden' name='target' value='".$row['acctid']."'>
						<input type='submit' class='button' value='$check'>
						</form></td>");					
			} else rawoutput("<td></td>");
			rawoutput("</tr>");
		}
		if (db_num_rows($result)==0) {
			rawoutput("<tr><td colspan=5>");
			output("`\$You have not flirted with anybody yet.`0");
			rawoutput("</td></tr>");
		}
		rawoutput("</table></center>");
		break;				
	case "flirtlist":
		$sql="SELECT a.name AS name,a.acctid AS acctid, b.date AS date,b.responsedate AS responsedate,b.successful AS successful,b.id AS id FROM ".db_prefix('marriage_actions')." AS b LEFT JOIN ".db_prefix('accounts')." AS a ON b.initiator=a.acctid WHERE b.receiver='$user' AND b.responsedate!='0000-00-00 00:00:00' 
		UNION 
		SELECT a.name AS name,a.acctid AS acctid, b.date AS date,b.responsedate AS responsedate,b.successful AS successful,b.id AS id FROM ".db_prefix('marriage_actions')." AS b LEFT JOIN ".db_prefix('accounts')." AS a ON b.receiver=a.acctid WHERE b.initiator='$user' AND b.responsedate!='0000-00-00 00:00:00' ORDER BY name,date,responsedate ASC";
		$result=db_query($sql);debug($sql);
		$name=translate_inline("Name");
		$date=translate_inline("Date");
		$resdate=translate_inline("Date Of Response");
		$view=translate_inline("View Flirt");
		$successful=translate_inline("Outcome");
		$view=translate_inline("View Flirt");				
		$ops=translate_inline("Ops");
		rawoutput("<center><table cellpadding='3' cellspacing='0' border='0' ><tr class='trhead'><td>$name</td><td>$date</td><td>$resdate</td><td>$successful</td><td>$ops</td></tr>");				
		$class='';
		$old=0;
		while ($row=db_fetch_assoc($result)) {
			$class=($class=='trlight'?'trdark':'trlight');
			rawoutput("<tr class='$class'><td>");
			if ($old!=$row['acctid']) output_notl($row['name']);
			$old=$row['acctid'];
			rawoutput("</td><td>");
			output_notl($row['date']);
			rawoutput("</td><td>");
			output_notl($row['responsedate']);
			rawoutput("</td><td align='center'>");
			rawoutput("<img src='modules/marriage/smileys/smiley-".$row['successful'].".gif' alt='reaction'>");
			rawoutput("</td><td>");
			rawoutput("<form action='runmodule.php?module=marriage&op=flirt&subop=flirtviewsingle' method='POST'>");
			addnav("","runmodule.php?module=marriage&op=flirt&subop=flirtviewsingle");
			rawoutput("<input type='hidden' name='target' value='".($row['acctid'])."'>");
			rawoutput("<input type='hidden' name='id' value='".$row['id']."'>");
			rawoutput("<input type='submit' class='button' value='$view'>");
			rawoutput("</form>");
			rawoutput("</td></tr>");
		}
		if (db_num_rows($result)==0) {
			rawoutput("<tr><td colspan=5>");
			output("`\$You have not flirted with anybody yet.`0");
			rawoutput("</td></tr>");
		}
		rawoutput("</table></center>");
		break;
	case "flirtview":
		output("`xHere are the details:`n`n");
		$target=(int)httppost('target');
		$active=httppost('active');
		$sql="SELECT a.name AS name,a.acctid AS acctid,c.name AS receivername,c.acctid AS receiveracctid, b.date AS date,b.responsedate AS responsedate,b.successful AS successful,b.id AS id,b.message AS message, b.response AS response FROM ".db_prefix('accounts')." AS c RIGHT JOIN ".db_prefix('marriage_actions')." AS b ON c.acctid=b.receiver LEFT JOIN ".db_prefix('accounts')." AS a ON b.initiator=a.acctid WHERE ((b.receiver='$user' AND b.initiator='$target') OR (b.initiator='$user' AND b.receiver='$target')) AND responsedate!='0000-00-00 00:00:00' ORDER BY b.date,b.responsedate ASC";
		$result=db_query($sql);debug($sql);
		$none=translate_inline("No words spoken...");
		while ($row=db_fetch_assoc($result)) {
			output("`\$%s `4(%s)`2 flirted with `1%s `4(%s)`2 ... the flirt (",$row['name'],$row['date'],$row['receivername'],$row['responsedate']);
			rawoutput("<img src='modules/marriage/smileys/smiley-".$row['successful'].".gif' alt='reaction'>");
			output(") started with:`n");
			
			if ($row['message']=='') $row['message']=$none;
			if ($row['response']=='') $row['response']=$none;
			output_notl("`c`^".stripslashes($row['message'])."`0`c`n`n");
			output("... and the response was:`n");
			output_notl("`c`^".stripslashes($row['response'])."`0`c`n`n");
			$from=$row['acctid'];
			$to=$row['receiveracctid'];
		}
		$flirt=translate_inline("Flirt back!");
		if ($from==$user) $targetted=$to;
			else $targetted=$from;
		rawoutput("<form action='runmodule.php?module=marriage&op=flirt&subop=flirt' method='POST'>");
		addnav("","runmodule.php?module=marriage&op=flirt&subop=flirt");
		rawoutput("<input type='hidden' name='target' value='".$targetted."'>
				<input type='submit' class='button' value='$flirt'>		
				</form></td></tr>");				
		break;
	case "unrespondedstatus":
		$sql="SELECT a.name AS name,a.acctid AS acctid, b.date AS date,b.responsedate AS responsedate,b.successful AS successful,b.id AS id FROM ".db_prefix('marriage_actions')." AS b LEFT JOIN ".db_prefix('accounts')." AS a ON b.initiator=a.acctid WHERE b.receiver='$user' AND b.responsedate='0000-00-00 00:00:00' 
		UNION 
		SELECT a.name AS name,a.acctid AS acctid, b.date AS date,b.responsedate AS responsedate,b.successful AS successful,b.id AS id FROM ".db_prefix('marriage_actions')." AS b LEFT JOIN ".db_prefix('accounts')." AS a ON b.receiver=a.acctid WHERE b.initiator='$user' AND b.responsedate='0000-00-00 00:00:00' ORDER BY name,date,responsedate ASC";
		$result=db_query($sql);
		$name=translate_inline("Name");
		$date=translate_inline("Date");
		$resdate=translate_inline("Date Of Response");
		$view=translate_inline("View Flirt");
		$ops=translate_inline("Ops");
		$close=translate_inline("Close Flirt!");				
		rawoutput("<center><table cellpadding='3' cellspacing='0' border='0' ><tr class='trhead'><td>$name</td><td>$date</td><td>$resdate</td><td>$ops</td></tr>");				
		$class='';
		$old=0;
		while ($row=db_fetch_assoc($result)) {
			$class=($class=='trlight'?'trdark':'trlight');
			rawoutput("<tr class='$class'><td>");
			if ($old!=$row['acctid']) output_notl($row['name']);
			$old=$row['acctid'];
			rawoutput("</td><td>");
			output_notl($row['date']);
			rawoutput("</td><td>");
			output_notl($row['responsedate']);
			rawoutput("</td><td>");
			rawoutput("<form action='runmodule.php?module=marriage&op=flirt&subop=flirtviewsingle&unresponded=1' method='POST'>");
			addnav("","runmodule.php?module=marriage&op=flirt&subop=flirtviewsingle&unresponded=1");
			rawoutput("<input type='hidden' name='target' value='".($row['acctid'])."'>");
			rawoutput("<input type='hidden' name='id' value='".$row['id']."'>");
			rawoutput("<input type='submit' class='button' value='$view'>");
			rawoutput("<input type='submit' name='close' class='button' value='$close'>");
			rawoutput("</form>");
			rawoutput("</td></tr>");
		}
		if (db_num_rows($result)==0) {
			rawoutput("<tr><td colspan=5>");
			output("`\$You have not flirted with anybody yet.`0");
			rawoutput("</td></tr>");
		}
		rawoutput("</table></center>");
		output("`jYou may close a flirt if somebody does not reply to you... the flirt attempt will be stored with a negative rating. `\$No further warning after hitting the button!");
		break;				
	case "flirtviewsingle":

		$id=(int)httppost('id');
		$unresponded=(int)httpget('unresponded');
		$close=httppost('close');
		
		if ($close) {
			//close + view
			$text=translate_inline('Closed by the Flirter');
			$sql="UPDATE ".db_prefix('marriage_actions')." SET responsedate='".date('Y-m-d H:i:s')."',successful=0,response='".addslashes($text)."' WHERE id='".$id."';";
			//debug($sql);
			db_query($sql);
			output("`c`\$`bThis flirt has now been closed, this is what you sent.`b`0`c");
		}
		output("`xHere are the details:`n`n");
		$sql="SELECT a.name AS name,a.acctid AS acctid,c.name AS receivername,c.acctid AS receiveracctid, b.date AS date,b.responsedate AS responsedate,b.successful AS successful,b.id AS id,b.message AS message, b.response AS response FROM ".db_prefix('accounts')." AS c RIGHT JOIN ".db_prefix('marriage_actions')." AS b ON c.acctid=b.receiver LEFT JOIN ".db_prefix('accounts')." AS a ON b.initiator=a.acctid WHERE b.id='$id'";
		$result=db_query($sql);
		$none=translate_inline("No words spoken...");
		$targetted=0;
		$deleted="Deleted User";
		while ($row=db_fetch_assoc($result)) {
			if ($row['receivername']=='') $row['receivername']=$deleted;
			output("`\$%s `4(%s)`2 flirted with `1%s `4(%s)`2 ... the flirt (",$row['name'],$row['date'],$row['receivername'],($unresponded&&!$close)?translate_inline("no response yet"):$row['responsedate']);
			if ($unresponded && !$close) {
				rawoutput("<img src='modules/marriage/smileys/smiley-dunno.gif' alt='reaction'>");
			} else rawoutput("<img src='modules/marriage/smileys/smiley-".$row['successful'].".gif' alt='reaction'>");
			
			output(") started with:`n");
			
			if ($row['message']=='') $row['message']=$none;
			if ($row['response']=='') $row['response']=$none;
			output_notl("`c`^".stripslashes($row['message'])."`0`c`n`n");
			if (!$unresponded || $close) {
				output("... and the response was:`n");
				output_notl("`c`^".stripslashes($row['response'])."`0`c`n`n");
			}
			$from=$row['acctid'];
			$to=$row['receiveracctid'];
		}
		$flirt=translate_inline("Flirt back!");
		if ($from==$user) $targetted=$to;
			else $targetted=$from;
		if ($targetted!=0 && !$close) {
			$flirt=translate_inline("Flirt back!");
			rawoutput("<form action='runmodule.php?module=marriage&op=flirt&subop=flirt' method='POST'>");
			addnav("","runmodule.php?module=marriage&op=flirt&subop=flirt");
			rawoutput("<input type='hidden' name='target' value='".$targetted."'>
					<input type='submit' class='button' value='$flirt'>		
					</form></td></tr>");
		}
		break;
	case "marriagecheck":
		$target=(int) httppost('target');
		if ($target===0) break;
		debug($this->ismarried($target));
		if ($this->ismarried($target)) {
			output("`RAlright, if you try to propose to a person who is married, it will most likely end with you getting beat up ... so back off.");
			break;
		}
		$sql="SELECT SUM(CHAR_LENGTH(b.message)) as messagecount,SUM(CHAR_LENGTH(b.response)) as responsecount FROM ".db_prefix('marriage_actions')." AS b LEFT JOIN ".db_prefix('accounts')." AS a ON b.initiator=a.acctid WHERE (b.receiver='$user' AND b.initiator='$target') OR (b.receiver='$target' AND b.initiator='$user') ";
		$result=db_query($sql);debug($sql);
		$needed=get_module_setting('m_wordcount');
		$mc_needed=get_module_setting('mc_wordcount');
		
		while ($row=db_fetch_assoc($result)) {
			$sum=$row['messagecount']+$row['responsecount'];

			if ($sum>$mc_needed && $this->isengaged($target)==$session['user']['acctid'] && $this->isengaged($session['user']['acctid'])!= $session['user']['acctid']) {
				output("`RAlright!`x You have had enough conversation, you both seem to like each other and you might want to step down a more serious path....`n`n");
				addnav("Become married...");
				addnav("Visit the chapel","runmodule.php?module=marriage&op=chapel&subop=marry&target=".$target);
			} elseif ($sum>$needed ) {
				if ($this->isengaged()) {
					output("`RYou are already engaged, but sadly it seems your relationship is not deep enough to ask for a wedding... you need to work harder!");
				} else {
					output("`RAlright!`x You have had enough conversation, you both seem to like each other and you think there might be a chance for a proposal....`n`n");
					addnav("Proposal...");
					addnav("Get a ring","runmodule.php?module=marriage&op=max&subop=ring&target=".$target);
				}
			} else {
				output("`)Darn`x, it seems you have not done or flirted enough with your to achieve your goal of desire...`n`n");
			}
		} 
		break;
	case "checkproposals":
		$sql="SELECT a.name AS name,a.acctid AS acctid, b.date AS date,b.responsedate AS responsedate FROM ".db_prefix('marriage_proposals')." AS b LEFT JOIN ".db_prefix('accounts')." AS a ON b.initiator=a.acctid WHERE b.proposed_to='$user' ORDER BY date DESC";
		$result=db_query($sql);debug($sql);
		$name=translate_inline("Name");
		$date=translate_inline("Date");
		$resdate=translate_inline("Date Of Response");
		$view=translate_inline("View Proposal");
		$ops=translate_inline("Ops");
		rawoutput("<center><table cellpadding='3' cellspacing='0' border='0' ><tr class='trhead'><td>$name</td><td>$date</td><td>$resdate</td><td>$ops</td></tr>");				
		$class='';
		$old=0;
		$respond=translate_inline("Respond");				
		$view=translate_inline("View");
		while ($row=db_fetch_assoc($result)) {
			$class=($class=='trlight'?'trdark':'trlight');
			rawoutput("<tr class='$class'><td>");
			if ($old!=$row['acctid']) output_notl($row['name']);
			$old=$row['acctid'];
			rawoutput("</td><td>");
			output_notl($row['date']);
			rawoutput("</td><td>");
			output_notl($row['responsedate']);
			rawoutput("</td><td>");
			if ($row['responsedate']=='0000-00-00 00:00:00') {
				rawoutput("<form action='runmodule.php?module=marriage&op=flirt&subop=proposalrespond' method='POST'>");
				addnav("","runmodule.php?module=marriage&op=flirt&subop=proposalrespond");
				rawoutput("<input type='hidden' name='target' value='".($row['acctid'])."'>");
				rawoutput("<input type='hidden' name='date' value='".($row['date'])."'>");					
				rawoutput("<input type='submit' class='button' value='$respond'>");
				rawoutput("</form>");
			} else {
				rawoutput("<form action='runmodule.php?module=marriage&op=flirt&subop=proposalview' method='POST'>");
				addnav("","runmodule.php?module=marriage&op=flirt&subop=proposalview");
				rawoutput("<input type='hidden' name='target' value='".($row['acctid'])."'>");
				rawoutput("<input type='hidden' name='date' value='".($row['date'])."'>");
				rawoutput("<input type='submit' class='button' value='$view'>");
				rawoutput("</form>");						
			}
			rawoutput("</td></tr>");
		}
		if (db_num_rows($result)==0) {
			rawoutput("<tr><td colspan=4>");
			output("`\$You have no incoming proposals.`0");
			rawoutput("</td></tr>");
		}
		rawoutput("</table></center>");
		break;
	case "proposalview":
		output("`xHere are the details:`n`n");
		$target=(int)httppost('target');
		$date=httppost('date');
		$sql="SELECT a.name AS name,a.acctid AS acctid,b.date AS date,b.responsedate AS responsedate, b.propose AS propose, b.response AS response FROM ".db_prefix('accounts')." AS a RIGHT JOIN ".db_prefix('marriage_proposals')." AS b ON a.acctid=b.initiator WHERE b.proposed_to=$user AND b.date='$date'";
		$result=db_query($sql);debug($sql);
		$none=translate_inline("No words spoken...");
		$targetted=0;
		while ($row=db_fetch_assoc($result)) {
			output("`\$%s `4(%s)`2 proposed to `1%s `2 ... `n`n",$row['name'],$row['date'],$user);
			output_notl("`c`^".stripslashes($row['propose'])."`0`c`n`n");
			output("`x... and the response was:`n");
			output_notl("`c`^".stripslashes($row['response'])."`0`c`n`n");
		}
		break;
	case "proposalrespond":
		//working

		if (httppost('accept') || httppost('reject')) {
			$response=httppost('response');
			$date=httppost('date');
			$target=(int)httppost('target');
			$response=$this->mystrip($response);
			if (httppost('accept')) $accepted=1;
				else $accepted=0;
			require_once("lib/systemmail.php");
			$re=$session['user']['name'].sprintf_translate(" answered to your proposal... `n`4Here the reponse:`0`n`n").$response;
			systemmail($target,array("`gA proposal has been answered..."),$re);
			$sql="UPDATE ".db_prefix('marriage_proposals')." SET response='".addslashes($response)."', accepted=$accepted,responsedate='".date("Y-m-d H:i:s")."' WHERE date='$date' AND proposed_to=$user AND initiator=$target LIMIT 1;";
			$result=db_query($sql);debug($sql);
			if (db_affected_rows($result)<1) {
				output("`4Error! This proposal could not be found. Most likely an error, report to admin!`n");
			} else {
				output("`4Answer delivered!`n`n");
				if ($accepted==1) {
					//set engaged 
					$engaged=(int)get_module_pref('fiancee','marriage',$target);
					$userengaged=(int)get_module_pref('fiancee','marriage');
					if ($engaged>0) {
						output("`4Sadly, you are told by some friendly old people that the one who proposed to you is already engaged... and this proposal was for nothing...");
					} elseif ($userengaged>0) {
						output("`4Sadly, you are already engaged... and this proposal was for nothing...");						
					} else {
						output("`\$You are now engaged ... ");
						set_module_pref('fiancee',$target);
						set_module_pref('fiancee',$user,'marriage',$target);
					}
				}
			}
			break;
		}
		
		$text='';
		
		if (httppost('preview')) {
			$text=httppost('response');
			$message=stripslashes($text);
			output("Preview:`n`n");
			output_notl("`c".$message."`c`n`n");
		}

		output("`xHere are the details:`n`n");
		$target=(int)httppost('target');
		$date=httppost('date');
		$sql="SELECT a.name AS name,a.acctid AS acctid,b.date AS date,b.responsedate AS responsedate, b.propose AS propose, b.response AS response FROM ".db_prefix('accounts')." AS a RIGHT JOIN ".db_prefix('marriage_proposals')." AS b ON a.acctid=b.initiator WHERE b.proposed_to=$user AND b.date='$date'";
		$result=db_query($sql);debug($sql);
		$targetted=0;
		while ($row=db_fetch_assoc($result)) {
			output("`\$%s `4(%s)`2 proposed to `1%s `2 ... `n`n",$row['name'],$row['date'],$session['user']['name']);
			output_notl("`c`^".stripslashes($row['propose'])."`c`0`n`n");
		}
		rawoutput("<form action='runmodule.php?module=marriage&op=flirt&subop=proposalrespond' method='POST'><center>");
		addnav("","runmodule.php?module=marriage&op=flirt&subop=proposalrespond");
		require_once("modules/marriage/forms.php");
		$box='response';
		marriage_charsleft($box);
		$l=3000;
		rawoutput("<br><table><tr><td colspan='3'><textarea name='$box' id='input$box' onKeyUp='previewtext$box(document.getElementById(\"input$box\").value,$l);' cols='50' rows='20' size='3000' wrap='soft'>$message</textarea></td></tr>");
		rawoutput("<input type='hidden' name='target' value='$target'>");
		rawoutput("<input type='hidden' name='date' value='$date'><br>");
		rawoutput("<input type='hidden' name='target' value='$target'><br>");
		$submit=translate_inline("Accept!");
		$reject=translate_inline("Reject!");
		$preview=translate_inline("Preview!");
		if ($this->ismarried() || $this->isengaged()) $value="disabled";
		rawoutput("<tr><td><input type='submit' class='button' name='accept' $value value='$submit'></td><td align='center'><input type='submit' class='button' name='preview' value='$preview'></td><td align='right'><input type='submit' class='button' name='reject' value='$reject'></td></tr></table>");
		rawoutput("</center></form>");
		output("`i`\$Note: No center tags, no bold tags, no italic tags, those will be removed.`n`xAlso mind the timeout!`i");
		break;
			
	default:
		output("`xSo, you are looking for a `Rflirt`x, aren't you... well, there might be some interesting folks around, you just have to make a good impression.`n`n");
		output("Flirting is not about money, or at least it should not be... so if you ponder about sending the target of your ambition a gift... it is appreciated, but don't expect people to be buyable with gold or gems. `4Tsubaki's Gift Shop`x in Water Country might help you out.`n`n");
		output("The best approach is to make a good impression when you talk to somebody... so, if you find somebody interesting, either in general or from the talks here in the meadows... give it your best shot `\$=)");
		addnav("Flirting");
		addnav("Approach the one you have in mind","runmodule.php?module=marriage&op=flirt&subop=selectprecisely");
		addnav("Look around the meadows","runmodule.php?module=marriage&op=flirt&subop=select");
		addnav("Display Recent Flirts","runmodule.php?module=marriage&op=flirt&subop=flirtlist");
		addnav("Display Flirtpartners","runmodule.php?module=marriage&op=flirt&subop=flirtpartnerlist");				
		
		//fetch incoming and not responded flirts
		$sql="SELECT count(initiator) as counter FROM ".db_prefix('marriage_actions')." WHERE responsedate='0000-00-00 00:00:00' AND receiver=".$user.";";
		$result=db_query($sql);
		$row=db_fetch_assoc($result);
		if ($row['counter']>0) {
			addnav(array("Response to flirts (`4%s incoming`0)",$row['counter']),"runmodule.php?module=marriage&op=flirt&subop=response");					
		}
		$sql="SELECT count(receiver) as counter FROM ".db_prefix('marriage_actions')." WHERE responsedate='0000-00-00 00:00:00' AND initiator=".$user.";";
		$result=db_query($sql);
		$row=db_fetch_assoc($result);
		if ($row['counter']>0) {
			addnav(array("Check unresponded flirts (`4%s outgoing`0)",$row['counter']),"runmodule.php?module=marriage&op=flirt&subop=unrespondedstatus");					
		}
		//fetch incoming and not responded proposals
		addnav("More Serious...");
		$sql="SELECT count(initiator) as counter FROM ".db_prefix('marriage_proposals')." WHERE responsedate='0000-00-00 00:00:00' AND proposed_to=".$user.";";
		$result=db_query($sql);
		$row=db_fetch_assoc($result);
		addnav(array("Proposal Overview (`4%s unresponded`0)",(int)$row['counter']),"runmodule.php?module=marriage&op=flirt&subop=checkproposals");					
		if ($this->isengaged()) {
			addnav("Cancel Engagement...","runmodule.php?module=marriage&op=chapel&subop=endengagement");
		}
		addnav("Ignore");
		addnav("Ignore somebody","runmodule.php?module=marriage&op=flirt&subop=ignore");
		addnav("Ignore List","runmodule.php?module=marriage&op=flirt&subop=ignorelist");
	break;

}
page_footer();
?>
