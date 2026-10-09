<?php
function bingobook_list() {
	global $session;
	$bingo = bingobook_massgetfull();
	output("`b`@Bingo Book:`b`n");
	rawoutput("<table style='width:100%;text-align:center;' cellpadding='3' cellspacing='0' border='0'>");
	rawoutput("<tr class='trhead'><td>".translate_inline("Name")."</td><td>".translate_inline("Logged In")."</td><td>".translate_inline("Location")."</td><td>".translate_inline("Alive")."</td><td>".translate_inline("Entrydate")."</td><td>".translate_inline("Comment")."</td><td>".translate_inline("Operations")."</td></tr>");
	$last = date("Y-m-d H:i:s", strtotime("-".getsetting("LOGINTIMEOUT", 900)." sec"));
	$x=0;
	if ($bingo!==array()) {
		foreach ($bingo as $row) {
				$ac=$row['bingoid'];
				$x++;
				rawoutput("<tr class='".($x%2?"trlight":"trdark")."'>");
				rawoutput("<td><a href='mail.php?op=write&to=".rawurlencode($row['bingologin'])."'>".appoencode("`&".$row['bingoname'],false)."</a></td>");
				addnav("","mail.php?op=write&to=".rawurlencode($row['bingologin']));
				$loggedin=$row['bingologgedin'];
				if ($row['bingolaston']<$last) {
					$loggedin=false;
				}
				$loggedin = translate_inline($loggedin?"`^Yes`0":"`%No`0");
				rawoutput("<td>".appoencode($loggedin,false)."</td>");
				rawoutput("<td><span class='colLtYellow'>".htmlentities($row['bingolocation'])."</span></td>");
				$alive = translate_inline($row['bingoalive']?"`@Yes`0":"`\$No`0");
				rawoutput("<td>".appoencode($alive,false)."</td>");
				rawoutput("<td>".appoencode($row['entrydate'],false)."</td>");
				rawoutput("<td>");
				output(sanitize_html($row['comment']));
				rawoutput("</td>");
				$ops = "[<a href='runmodule.php?module=bingobook&op=changecomment&ac=$ac' class='colDkRed'>".translate_inline("Change comment")."</a>] - [<a href='runmodule.php?module=bingobook&op=remove&ac=$ac' class='colDkRed'>".translate_inline("Remove")."</a>]";
				addnav("","runmodule.php?module=bingobook&op=changecomment&ac=$ac");
				addnav("","runmodule.php?module=bingobook&op=remove&ac=$ac");
				rawoutput("<td>$ops</td></tr>");
		}
	}
	if (count($bingo)==0) {
		rawoutput("<tr class='trlight'><td colspan='7'>");
		output("`^You have no entries.");
		rawoutput("</td></tr>");
	}
	rawoutput("</table>");

}
?>
