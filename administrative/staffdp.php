<?php
function staffdp_getmoduleinfo(){
	$info = array(
		"name"=>"Donation Points for Staff",
		"version"=>"1.0",
		"author"=>"`2Oliver Brendel",
		"category"=>"Administrative",
		"download"=>"",
	);
	return $info;
}

function staffdp_install(){
	module_addhook("superuser");
	return true;
}

function staffdp_uninstall(){
	return true;
}

function staffdp_dohook($hookname,$args){
	global $session;
	switch ($hookname) {
	case "superuser":
		if (($session['user']['superuser'] & SU_MEGAUSER)== SU_MEGAUSER) {
			addnav("Mechanics");
			addnav("Donation Points for Staff","runmodule.php?module=staffdp");
		}

	break;
	}
	return $args;
}

function staffdp_run(){
	global $session;
	// The Grotto link is only offered to megausers; enforce the same here.
	check_su_access(SU_MEGAUSER);
	$op=httpget('op');
	$subop=httpget('subop');
	require_once("lib/superusernav.php");
	superusernav();
	page_header("Give out DP to staff");
	addnav("Actions");
	addnav("Refresh","runmodule.php?module=staffdp&order=".httpget('order'));
	switch ($op) {
		default:
			$sql="SELECT login,acctid,name,b.value as pos FROM ".db_prefix('accounts')." AS a LEFT JOIN ".db_prefix('module_userprefs')." AS b ON a.acctid=b.userid WHERE b.modulename='stafflist' AND b.setting='desc' AND superuser>0 AND (".SU_NEVER_EXPIRE."!=superuser AND ".(SU_NEVER_EXPIRE&SU_GIVE_GROTTO)."!=superuser AND ".SU_GIVE_GROTTO."!=superuser);";
debug($sql);
			$result=db_query($sql);
			switch ($subop) {
				case "givedp":
					$dp=(int)httppost('dp');
					$body=str_replace("\n","`n",httppost('body'));
					$body.=sprintf("`n`n(You have received %s donation points)",$dp);
					$subject=str_replace("\n","`n",httppost('subject'));
					require_once("lib/systemmail.php");
					$acct=array();
					while ($row=db_fetch_assoc($result)) {
						$acct[]=$row['acctid'];
						systemmail($row['acctid'],$subject,$body);
					}
					
					$sql="UPDATE ".db_prefix("accounts")." SET donation=donation+$dp WHERE acctid IN (".implode(",",$acct).");";
					debug($sql);
					if ($acct!=array()) {
						$result=db_query($sql);
						output("%s rows affected...",db_affected_rows($result));
					} else output("No members selected");
					page_footer();
					break;
			}
			$result = db_query ($sql);
			$name=translate_inline("Name");
			$position=translate_inline("Position");
			rawoutput("<table border=0 cellpadding=2 cellspacing=1 bgcolor='#999999' align=center>");
			rawoutput("<tr class='trhead' height=30px><td><b>$name</b></td><td><b>$position</b></td></td></tr>");
			$class="trlight";
			output("`4Stats:`n`n");
			while ($row=db_fetch_assoc($result)) {
				$class=($class=='trlight'?'trdark':'trlight');
				rawoutput("<tr height=30px class='$class'>");
				rawoutput("<td>");
				output_notl($row['name']);
				rawoutput("</td><td>");
				output_notl($row['pos']);
				rawoutput("</td></tr>");

			}
			rawoutput("</table>");
			$subject=translate_inline("Subject:");
			$body=translate_inline("Body:");
			$points=translate_inline("How many points:");
			$submit=translate_inline("Give out points now!");
			rawoutput("<form action='runmodule.php?module=staffdp&subop=givedp' method='POST'>");
			addnav("","runmodule.php?module=staffdp&subop=givedp");
			output_notl("`n".$subject);
			rawoutput("<input type='input' class='input' name='subject'>");
			output_notl("`n".$body);
			rawoutput("<br><textarea cols='50' rows='10' name='body'></textarea><br>");
			output_notl("`n".$points);
			rawoutput("<input class='input' type='input' name='dp' value='200'>");
			rawoutput("<br><input type='submit' class='button' value=$submit></form>");
	}
	page_footer();
}


?>
