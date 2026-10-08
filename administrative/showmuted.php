<?php
function showmuted_getmoduleinfo(){
	$info = array(
		"name"=>"Show Muted for Grotto",
		"version"=>"1.0",
		"author"=>"`2Oliver Brendel",
		"category"=>"Administrative",
		"download"=>"",
		"requires"=>array(
			"mutemod"=>"1.0|Mute Moderation (Core with Extensions by Oliver Brendel)",
			),
	);
	return $info;
}

function showmuted_install(){
	module_addhook("superuser");
	return true;
}

function showmuted_uninstall(){
	return true;
}

function showmuted_dohook($hookname,$args){
	global $session;
	switch ($hookname) {
	case "superuser":
		if (($session['user']['superuser'] & SU_EDIT_COMMENTS)== SU_EDIT_COMMENTS || ($session['user']['superuser'] & SU_EDIT_PETITIONS)== SU_EDIT_PETITIONS) {
			addnav("Mechanics");
			addnav("Muted Player Overview","runmodule.php?module=showmuted");
		}

	break;
	}
	return $args;
}

function showmuted_run(){
	global $session;
	$op=httpget('op');
	require_once("lib/superusernav.php");
	superusernav();
	page_header("Muted Users Overview");
	switch ($op) {
		default:
			addnav("Actions");
			addnav("Refresh","runmodule.php?module=showmuted");
			$sql="SELECT a.name AS name, b.value as days, c.value as muter FROM ".db_prefix('accounts')." AS a RIGHT JOIN ".db_prefix('module_userprefs')." AS b ON a.acctid=b.userid LEFT JOIN ".db_prefix('module_userprefs')." AS c ON b.userid=c.userid WHERE b.modulename='mutemod' AND b.setting='tempmute' AND b.value+0>0 AND c.modulename='mutemod' AND c.setting='whomuted' ORDER BY a.login ASC";
			$result = db_query ($sql);
			$user=translate_inline("Username");
			$days=translate_inline("Days Tempmuted");
			$who=translate_inline("Who muted");
			rawoutput("<table border=0 cellpadding=2 cellspacing=1 bgcolor='#999999' align=center>");
			rawoutput("<tr class='trhead' height=30px><td><b>$user</b></td><td><b>$days</b></td><td><b>$who</b></td></tr>");
			$class="trlight";
			$col="`&";
			output("Muted Users (only tempmutes!):`n`n");
			while ($row=db_fetch_assoc($result)) {
				$class=($class=='trlight'?'trdark':'trlight');
				rawoutput("<tr height=30px class='$class'>");
				rawoutput("<td>");
				output_notl($row['name']);
				rawoutput("</td><td>");
				output_notl($row['days']);
				rawoutput("</td><td>");
				output_notl($row['muter']);
				rawoutput("</td></tr>");

			}
			rawoutput("</table>");
			require_once("lib/commentary.php");
			addcommentary();	
			commentdisplay("`n`n`@Muter Discussions`n","MuterDiscussions","Talk or be muted =)",10,"mutes");
			break;
	}
	page_footer();
}


?>
