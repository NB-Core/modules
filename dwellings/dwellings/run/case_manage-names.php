<?php
//Added Statistics + Tables
//Added Name Change
//Added Desc Change
//Added Windowpeer Change
//Compressed the code for all three changes into one function, removed the sanatize
//Added talkline. Why isn't this in the dwellings table???
//Added "- none -" values to stuff == ""

//Added missing modulehook
//translation readied everthing


	$dwid = httpget("dwid");
	$subop = httpget('subop');

	page_header("Dwelling Management - Names and Descriptions");
	require_once("modules/dwellings/lib.php");
	switch ($subop){
		case "rename":
			$newname = httppost('newname');
			$change = httpget('change');
			$newname = str_replace("`n", "", $newname);
			if (dwellings_teststring($newname) == 0) {
				output("Well, that is not any good.  Please select something else.");
			} else {
				$newname2 = stripslashes($newname);
				switch($change){
					case "name": 
						$gemcost = get_module_setting("namegemcost");
						$goldcost = get_module_setting("namegoldcost");
						output("`2Your dwelling is now called %s`2.",$newname2);
						break;
					case "description":
						$gemcost = get_module_setting("descgemcost");
						$goldcost = get_module_setting("descgoldcost");
						output("`2The internal description has been changed to:`n%s",$newname2);
						break;
					case "windowpeer":
						$gemcost = get_module_setting("windgemcost");
						$goldcost = get_module_setting("windgoldcost");
						output("`2The public description has been changed to:`n%s",$newname2);
						break;
				}
				$newname_esc = addslashes($newname);
				$sql2 = "UPDATE ".db_prefix("dwellings")." SET $change = '$newname_esc' WHERE dwid = $dwid";
				db_query($sql2);
				$session['user']['gems']-=$gemcost;
				$session['user']['gold']-=$goldcost;
				debuglog("spent $gemcost gems and $goldcost gold for changing the $change of their dwelling $dwid.");
			}
			break;
		case "talkline":
			$talkline = httppost('talkline');
			$newtalkline = str_replace("`n", "", $talkline);
			if (dwellings_teststring($newtalkline) == 0) {
				output("Well, that is not any good.  Please select something else.");
			} else {
				set_module_objpref("dwellings",$dwid,"dwidtalkline",$newtalkline);
				$newtalkline = stripslashes($newtalkline);
				output("`2Your talk-line is now %s`2.",$newtalkline);
			}
			break;
		}

	$sql = "SELECT *  FROM " . db_prefix("dwellings") . " WHERE dwid='$dwid'";
	$result = db_query($sql);
	$row = db_fetch_assoc($result);

	if($subop!="") output("`n`n");
	output("`2Here you can change the name and descriptions of your dwelling... as long as you have the right amount of cash ready.`n`n");

	$name=appoencode($row['name']);
	if($name=="") $name = translate_inline("- None -"); 
	$privdesc = $row['description'];
	if($privdesc=="") $privdesc = translate_inline("- None -"); 
	$windowpeer = $row['windowpeer'];
	if($windowpeer=="") $windowpeer = translate_inline("- None -"); 
	$talkline = get_module_objpref("dwellings",$dwid,"dwidtalkline");
	if($talkline=="") $talkline = translate_inline("- None -"); 
	$submit=translate_inline("Submit");
	
	rawoutput("<table border=0 cellpadding=2 cellspacing=1 bgcolor='#999999' width=95% align=center>");
	rawoutput("<tr class='trhead' height=30px><td width=40%><b>Setting</b></td><td><b>Value</b></td></tr>");
	$dwellingname = translate_inline("Dwelling name");
	rawoutput("<tr height=30px class='trlight'><td>$dwellingname</td><td>$name</td></tr>");
	$changename = translate_inline("Change the dwelling's name");
	rawoutput("<tr height=50px class='trdark'><td>$changename</td><td align=top>");
	if (($session['user']['gems'] < get_module_setting("namegemcost")) 
		|| ($session['user']['gold'] < get_module_setting("namegoldcost"))){
				rawoutput("&nbsp;</td></tr>");
				rawoutput("<tr height=30px class='trlight'><td colspan=2>");
				output("`\$You do not have the %s gold or %s gems required to rename your dwelling!",get_module_setting("namegoldcost"),get_module_setting("namegemcost"));
	}else{
		rawoutput("<script language='JavaScript'>
				function previewtext(t){
					var out = \"<span class=\'colLtWhite\'>\";
					var end = '</span>';
					var x=0;
					var y='';
					var z='';
					if (t.substr(0,2)=='::'){
						x=2;
						out += '</span><span class=\'colLtWhite\'>';
					}else if (t.substr(0,1)==':'){
						x=1;
						out += '</span><span class=\'colLtWhite\'>';
					}else if (t.substr(0,3)=='/me'){
						x=3;
						out += '</span><span class=\'colLtWhite\'>';
					}else{
						out += '</span><span class=\'colDkWhite\'>';
						end += '</span>';
					}
					for (; x < t.length; x++){
						y = t.substr(x,1);
						if (y=='<'){
							out += '&lt;';
							continue;
						}else if(y=='>'){
							out += '&gt;';
							continue;
						}else if (y=='`'){;
							if (x < t.length-1){
								z = t.substr(x+1,1);");
                $colors=\Lotgd\Output::getInstance()->getColors();
			$switchscript="switch (z) {
								case \"0\": out+='</span>';break;\n";
			foreach ($colors as $key=>$colorcode) {
				$switchscript.="case \"".$key."\": out+='</span><span class=\'".$colorcode."\'>';break;\n";
			}
			$switchscript.="}								
						x++;
						}
					}else{
						out += y;
					}
				}	
					document.getElementById(\"previewtext\").innerHTML=out+end+'<br/>';
				}
				</script>";
			rawoutput($switchscript);
				rawoutput("<form action='runmodule.php?module=dwellings&op=manage-names&subop=rename&change=name&dwid=$dwid' method='POST' autocomplete='false'>");
				addnav("","runmodule.php?module=dwellings&op=manage-names&subop=rename&change=name&dwid=$dwid");
				rawoutput("<input name='newname' id='newname' onKeyUp='previewtext(document.getElementById(\"newname\").value);'; size='40' maxlength='255'>");
				rawoutput("<input type='submit' class='button' value='$submit'><br>");
				rawoutput("<div id='previewtext'></div></form>");
				rawoutput("</td></tr><tr height=30px class='trlight'><td colspan=2>");
				output("`2Renaming costs %s gold and %s gems`2. (max. 75 characters including color codes)",get_module_setting("namegoldcost"),get_module_setting("namegemcost"));
		}
		rawoutput("</td></tr>");		
		rawoutput("<tr><td colspan=2>&nbsp;</td></tr>");	
		
		$privatedesc = translate_inline("Private description");
		rawoutput("<tr height=30px class='trlight'><td>$privatedesc</td><td>".appoencode($privdesc)."</td></tr>");
		$changedesc = translate_inline("Change the dwelling's private description");
		rawoutput("<tr height=50px class='trdark'><td>$changedesc</td><td align=top>");
		if (($session['user']['gold'] < get_module_setting("descgoldcost")) 
			|| $session['user']['gems'] < get_module_setting("descgemcost")) {
			rawoutput("&nbsp;</td></tr>");
			rawoutput("<tr height=30px class='trlight'><td colspan=2>");
			output("`\$You do not have the %s gold or %s gems required to change the description!",get_module_setting("descgoldcost"),get_module_setting("descgemcost"));
		} else {
			rawoutput("<form action='runmodule.php?module=dwellings&op=manage-names&subop=rename&change=description&dwid=$dwid' method='POST' autocomplete='false'>");
			addnav("","runmodule.php?module=dwellings&op=manage-names&subop=rename&change=description&dwid=$dwid");
			rawoutput("<textarea name='newname' cols=50 rows=5>".htmlentities($row['description'], ENT_COMPAT, getsetting("charset", "ISO-8859-1"))."</textarea><br>");
			rawoutput("<input type='submit' class='button' value='$submit'></form>");
			rawoutput("</td></tr><tr height=30px class='trlight'><td colspan=2>");
			output("`2Changing the description costs %s gold and %s gems`2. (max. 255 characters including color codes)",get_module_setting("descgoldcost"),get_module_setting("descgemcost"));
		}
		rawoutput("</td></tr>");		
		rawoutput("<tr><td colspan=2>&nbsp;</td></tr>");

		$publicdesc = translate_inline("Public description");
		rawoutput("<tr height=30px class='trlight'><td>$publicdesc</td><td>".appoencode($windowpeer)."</td></tr>");
		$changepublic = translate_inline("Change the dwelling's public description");
		rawoutput("<tr height=50px class='trdark'><td>$changepublic</td><td align=top>");
				if (($session['user']['gold'] < get_module_setting("windgoldcost")) 
					|| $session['user']['gems'] < get_module_setting("windgemcost")) {
			rawoutput("&nbsp;</td></tr>");
			rawoutput("<tr height=30px class='trlight'><td colspan=2>");
			output("`\$You do not have the %s gold or %s gems required to change the public description!",get_module_setting("windgoldcost"),get_module_setting("windgemcost"));
		} else {
			rawoutput("<form action='runmodule.php?module=dwellings&op=manage-names&subop=rename&change=windowpeer&dwid=$dwid' method='POST' autocomplete='false'>");
			addnav("","runmodule.php?module=dwellings&op=manage-names&subop=rename&change=windowpeer&dwid=$dwid");
			rawoutput("<textarea name='newname' cols=50 rows=5>".htmlentities($row['windowpeer'], ENT_COMPAT, getsetting("charset", "ISO-8859-1"))."</textarea><br>");
			rawoutput("<input type='submit' class='button' value='$submit'></form>");
			rawoutput("</td></tr><tr height=30px class='trlight'><td colspan=2>");
			output("`2Changing the description costs %s gold and %s gems`2. (max. 255 characters including color codes)",get_module_setting("windgoldcost"),get_module_setting("windgemcost"));
		}
		rawoutput("</td></tr>");		
		rawoutput("<tr><td colspan=2>&nbsp;</td></tr>");	
		if (get_module_setting("talkl")){
			$talkl = translate_inline("Talkline");
			rawoutput("<tr height=30px class='trlight'><td>$talkl</td><td>".appoencode($talkline)."</td></tr>");
			$changetalk = translate_inline("Change the dwelling's talkline");
			rawoutput("<tr height=50px class='trdark'><td>$changetalk</td><td align=top>");
			rawoutput("<form action='runmodule.php?module=dwellings&op=manage-names&subop=talkline&dwid=$dwid' method='POST'>");
			addnav("","runmodule.php?module=dwellings&op=manage-names&subop=talkline&dwid=$dwid");
			rawoutput("<input name='talkline' id='talkline' value='".htmlentities($talkline, ENT_COMPAT, getsetting("charset", "ISO-8859-1"))."' size='40' maxlength='255'><br>");
			rawoutput("<input type='submit' class='button' value='$submit'><br>");
			rawoutput("</form>");
			rawoutput("</td></tr><tr height=30px class='trlight'><td colspan=2>");
			output("`2Changing the talkline costs nothing. (max. 50 characters including color codes)");
			rawoutput("</td></tr>");		
		}
		rawoutput("</table>"); 
	
	addnav("Management");
	addnav("Main","runmodule.php?module=dwellings&op=manage&dwid=$dwid");
	addnav("Names and Descriptions","runmodule.php?module=dwellings&op=manage-names&dwid=$dwid");
	modulehook("dwellings-manage",array("type"=>$type,"dwid"=>$dwid));
	addnav("Leave");
	addnav("Back to the dwelling","runmodule.php?module=dwellings&op=enter&dwid=$dwid");
?>
