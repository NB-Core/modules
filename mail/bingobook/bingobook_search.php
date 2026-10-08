<?php
function bingobook_search() {
	global $session;
	$name = httppost("name");
	rawoutput("<form action='runmodule.php?module=bingobook&op=search' method='POST'>");
	addnav("","runmodule.php?module=bingobook&op=search");
	if ($name!="") {
		$string="%";
		for ($x=0;$x<strlen($name);$x++){
			$string .= substr($name,$x,1)."%";
		}
		$sql = "SELECT name,dragonkills,acctid FROM ".db_prefix("accounts")." WHERE name LIKE '%$string%' AND acctid<>".$session['user']['acctid']." AND locked=0 ORDER BY level,dragonkills";
		$result = db_query($sql);
		if (db_num_rows($result)>0) {
			$bingo = bingobook_massgetid();
			output("`@These users were found:`n");
			rawoutput("<table style='width:100%;text-align:center;' cellpadding='3' cellspacing='0' border='0'>");
			rawoutput("<tr class='trhead'><td>".translate_inline("Name")."</td><td>".translate_inline("Operations")."</td></tr>");
			$i=1;
			while ($row = db_fetch_assoc($result)) {
				$i=!$i;
				rawoutput("<tr class='".($i?"trlight":"trdark")."'><td>");
				output_notl($row['name']);
				rawoutput("</td><td>");
				if (in_array($row['acctid'],$bingo)) {
					$info = translate_inline("This user is already in your bingo book.");
				} else {
						$info = "[<a href='runmodule.php?module=bingobook&op=addentry&ac=".$row['acctid']."' class='colDkGreen'>".translate_inline("Add")."</a>]";
						addnav("","runmodule.php?module=bingobook&op=addentry&ac=".$row['acctid']);
				}
				rawoutput("$info</td></tr>");
			}
			rawoutput("</table>");
		} else {
			output("`c`@`bA user was not found with that name.`b`c");
		}
		output_notl("`n");
	}
	output("`^`b`cBingo Search...`c`b");
	output("`n`nWhom do you want to search for?");
	output("`n`nName of user: ");
	rawoutput("<input name='name' maxlength='50' value=\"".htmlentities(stripslashes(httppost('name')),ENT_COMPAT,getsetting('charset','ISO-8859-1'))."\">");
	$apply = translate_inline("Search");
	rawoutput("<input type='submit' class='button' value='$apply'></form>");
}

?>