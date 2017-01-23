<?php
function inactivemods_getmoduleinfo(){
	$info = array(
		"name"=>"Inactive Moderators",
		"version"=>"1.0",
		"author"=>"`2Oliver Brendel",
		"category"=>"Administrative",
		"download"=>"",
		"settings"=>array(
			"Inactive Moderators,title",
			"expiration"=>"Days you are inactive,int|10",
			),
		
	);
	return $info;
}

function inactivemods_install(){
	module_addhook("superuser");
	return true;
}

function inactivemods_uninstall(){
	return true;
}

function inactivemods_dohook($hookname,$args){
	global $session;
	switch ($hookname) {
	case "superuser":
		if (($session['user']['superuser'] & SU_MEGAUSER)== SU_MEGAUSER) {
			addnav("Mechanics");
			addnav("Show inactive superusers","runmodule.php?module=inactivemods");
		}

	break;
	}
	return $args;
}

function inactivemods_run(){
	global $session;
	$op=httpget('op');
	require_once("lib/superusernav.php");
	superusernav();
	page_header("Inactive Superusers");
	switch ($op) {

		default:
			addnav("Refresh");
			addnav("Refresh list","runmodule.php?module=inactivemods");
			addnav("Show those with 'never expire' flag","runmodule.php?module=inactivemods&expire=1");
			$ac=db_prefix('accounts');
			$expiration=get_module_setting('expiration');
			if (httpget('expire')==1) $where="AND (superuser & ".SU_NEVER_EXPIRE.")=".SU_NEVER_EXPIRE;
				else
				$where="AND (superuser & ".SU_NEVER_EXPIRE.")!=".SU_NEVER_EXPIRE;
			$sql="SELECT * FROM $ac WHERE laston<'".date("Y-m-d H:i:s",strtotime("-$expiration days"))."' AND superuser>0 $where ORDER BY acctid";
			$result = db_query ($sql);
			$acctid=translate_inline("AcctID");
			$name=translate_inline("Name");
			$lasthit=translate_inline("Last On");
			$dks=translate_inline("DKs");
			$days=translate_inline("Days gone");
			$stafflist=translate_inline("Staff Position");
			rawoutput("<table border=0 cellpadding=2 cellspacing=1 bgcolor='#999999' align=center width='100%'>");
			rawoutput("<tr class='trhead' height=30px><td><b>$acctid</b></td><td><b>$name</b></td><td><b>$lasthit</b></td><td><b>$days</b></td><td><b>$dks</b></td><td><b>$stafflist</b></td></tr>");
			$class="trlight";
			$col="`&";
			output("To show the stafflist position, the module stafflist must be active.`n`n");
			while ($row=db_fetch_assoc($result)) {
				$class=($class=='trlight'?'trdark':'trlight');
				rawoutput("<tr height=30px class='$class'>");
				rawoutput("<td>");
				output_notl($row['acctid']);
				rawoutput("</td><td>");
				output_notl($row['name']);
				rawoutput("</td><td>");
				output_notl($row['laston']);
				rawoutput("</td><td>");
				output_notl(round((strtotime(date("Y-m-d H:i:s",time()))-strtotime($row['laston']))/86400,3));
				rawoutput("</td><td>");
				output_notl($row['dragonkills']);
				rawoutput("</td><td>");
				output_notl(get_module_pref("desc","stafflist",$row['acctid']));
				rawoutput("</td></tr>");

			}
			rawoutput("</table>");
	}
	page_footer();
}


?>
