<?php
//you can use this function if you want to search for a flirtpartner
function searchplayer($link) {
	global $session;
	$whom = httppost("whom");
	rawoutput("<form action='$link&stage=0' method='POST'>");
	addnav("","$link&stage=0");
	if ($whom!="") {
		$string="%";
		for ($x=0;$x<strlen($whom);$x++){
			$string .= substr($whom,$x,1)."%";
		}
		$sql = "SELECT name,acctid FROM ".db_prefix("accounts")." WHERE name LIKE '%$string%' AND acctid<>".$session['user']['acctid']." ORDER BY name";
		$result = db_query($sql);
		if (db_num_rows($result)!=0) {
			output("`@These users were found `^(click on a name`@):`n");
			rawoutput("<table cellpadding='3' cellspacing='0' border='0'>");
			rawoutput("<tr class='trhead'><td>".translate_inline("Name")."</td></tr>");
			for ($i=0;$i<db_num_rows($result);$i++){
				$row = db_fetch_assoc($result);
				rawoutput("<tr class='".($i%2?"trlight":"trdark")."'><td><a href='$link&stage=1&target=".$row['acctid']."'>");
				addnav("","$link&stage=1&target=".$row['acctid']);
				output_notl($row['name']);
				rawoutput("</a></td></tr>");
			}
			rawoutput("</table>");
		} else {
			output("`c`@`bA user was not found with that name.`b`c");
		}
		output_notl("`n");
	}
	output("`^`b`cSelecting..`c`b");
	output("`nWhom do you want to have in your game?");
	output("`nName of user: ");
	rawoutput("<input name='whom' maxlength='50' value=\"".htmlentities(stripslashes($whom))."\">");
	$apply = translate_inline("Invite");
	rawoutput("<input type='submit' class='button' value='$apply'></form>");
}
?>
