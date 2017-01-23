<?php
/*
v1.01 added a small array (stored in a setting) with account IDs who do NOT count as self-referers
*/
function multichecker_getmoduleinfo(){
$info = array(
	"name"=>"Multichecker",
	"version"=>"1.0",
	"author"=>"`2Oliver Brendel",
	"override_forced_nav"=>true,
	"category"=>"Multichecker",
	"download"=>"http://lotgd-downloads.com",
	"settings"=>array(
		"Multichecker Settings - more data storage,title",
		"whitelist"=>"Stored White List - Acctids serialized,viewonly",
		),
	);
	return $info;
}

function multichecker_install(){
	module_addhook("superuser");
	return true;
}

function multichecker_uninstall(){
	output_notl("`n`c`b`QMultichecker Module - Uninstalled`0`b`c");
	return true;
}

function multichecker_dohook($hookname, $args){
	global $session;
	switch ($hookname) {
		case "superuser":
			if ($session['user']['superuser'] & SU_MEGAUSER) { //only access for megausers
				addnav("Mechanics");
				addnav("Multichecker","runmodule.php?module=multichecker&op=selfID");
			}
			break;
	}
	return $args;
}

function multichecker_run(){
	global $session;
	$op = httpget('op');
	$oldop=httpget('oldop');
	require_once("lib/superusernav.php");
	superusernav();
	page_header("Multichecker");
	addnav("Check by IP","runmodule.php?module=multichecker&op=IP");
	addnav("Check for self-referral by IP","runmodule.php?module=multichecker&op=selfIP");
	addnav("Check by Cookie ID","runmodule.php?module=multichecker&op=ID");
	addnav("Check for self-referral by ID","runmodule.php?module=multichecker&op=selfID");
	addnav("Marriage");
	addnav("Self-Marriages by ID","runmodule.php?module=multichecker&op=marriageID");
	addnav("Self-Marriages by IP","runmodule.php?module=multichecker&op=marriageIP");
	addnav("Self-Fiancees by ID","runmodule.php?module=multichecker&op=fianceeID");
	$whitelist=unserialize(get_module_setting('whitelist'));
	if (is_array($whitelist)) {
		$implodedwhitelist=implode(",",$whitelist);
	} else {
		$whitelist=array();
		$implodedwhitelist='';
	}
	switch ($op) {
		case "withoutwhitelist":
			$whitelist=array();
			$implodedwhitelist='';
			$op=$oldop;
			output("`3`bThis search was done including the `#whitelisted players`b`0.`n`n");
			break;
		case "addtowhitelist":
			$who=httpget('acctid');
			$ids=explode('||',$who);
			foreach ($ids as $id) {
				$whitelist[]=$id;
			}
			set_module_setting('whitelist',serialize($whitelist));
			$implodedwhitelist=implode(",",$whitelist);
			$op=$oldop;
			break;
		case "derefer":
			$acctid=httpget('acctid');
			$victim=httpget('multi');
			$subject=array(
				"Self-referred Multichar"
				);
			$sql="SELECT acctid,name FROM ".db_prefix('accounts')." WHERE acctid IN ($acctid,$victim);";
			$result=db_query($sql);
			while ($row=db_fetch_assoc($result)) {
				if ($row['acctid']==$acctid)
					$one=$row['name'];
					else
					$two=$row['name'];
			}
			$message=array(
				"`\$Dear %s`v, `nYou have self-referred the multichar '%s`v', who is now being derefered from you.`n`n `c`b`\$This is against the rules!`b`c`n`n`vThere might other consequences follow which you will be notified via an extra mail.`nContinued self-referral will bring more severe consequences.`n`n`\$Regards`n%s",
				$one,
				$two,
				$session['user']['name'],
				);
			require_once("lib/systemmail.php");
			systemmail($acctid,$subject,$message);
			$sql="UPDATE ".db_prefix('accounts')." SET referer=0 WHERE acctid=".$victim.";";
			$result=db_query($sql); 
			if ($result) {
				output("`vThe user %s`v has been notified and the multi been derefered. If you want to ban etc, please use the normal ban editor.`0`n`n",$one);
			} else {
				output("Unknown error! I was not able to update the referer to 0!");
				debug("User:".$acctid."-".$one);
				debug("Target:".$victim."-".$two);
			}
			$op=$oldop;
		break;
		case "fianceewarn":
			$acctid=httpget('acctid');
			$victim=httpget('multi');
			$subject=array(
				"`\$Multichar Marriage"
				);
			$sql="SELECT acctid,name FROM ".db_prefix('accounts')." WHERE acctid IN ($acctid,$victim);";
			$result=db_query($sql);
			while ($row=db_fetch_assoc($result)) {
				if ($row['acctid']==$acctid)
					$one=$row['name'];
					else
					$two=$row['name'];
			}
			$message=array(
				"`\$Dear %s`v, `nYou got engaged with the multichar '%s`v', who is now being cut from you.`n`n `c`b`\$This is against the rules!`b`c`n`n`vThere might other consequences follow which you will be notified via an extra mail.`nContinued violations against the rules will bring more severe consequences.`n`n`\$Regards`n%s",
				$one,
				$two,
				$session['user']['name'],
				);
			require_once("lib/systemmail.php");
			systemmail($acctid,$subject,$message);
			$sql="UPDATE ".db_prefix('module_userprefs')." SET value=0 WHERE modulename='marriage' AND setting='fiancee' AND userid IN ($acctid,$victim);";
			$result=db_query($sql); 
			if ($result) {
				output("`vThe users %s`v and %s`v has been notified and the marriage been cancelled. If you want to ban etc, please use the normal ban editor.`0`n`n",$one,$two);
			} else {
				output("Unknown error! I was not able to update the referer to 0!");
				debug("User:".$acctid."-".$one);
				debug("Target:".$victim."-".$two);
			}
			$op=$oldop;
			break;
		case "marriagewarn":
			$acctid=httpget('acctid');
			$victim=httpget('multi');
			$subject=array(
				"`\$Multichar Marriage"
				);
			$sql="SELECT acctid,name FROM ".db_prefix('accounts')." WHERE acctid IN ($acctid,$victim);";
			$result=db_query($sql);
			while ($row=db_fetch_assoc($result)) {
				if ($row['acctid']==$acctid)
					$one=$row['name'];
					else
					$two=$row['name'];
			}
			$message=array(
				"`\$Dear %s`v, `nYou have married the multichar '%s`v', who is now being divorced from you.`n`n `c`b`\$This is against the rules!`b`c`n`n`vThere might other consequences follow which you will be notified via an extra mail.`nContinued violations against the rules will bring more severe consequences.`n`n`\$Regards`n%s",
				$one,
				$two,
				$session['user']['name'],
				);
			require_once("lib/systemmail.php");
			systemmail($acctid,$subject,$message);
			$sql="UPDATE ".db_prefix('accounts')." SET marriedto=0 WHERE acctid IN ($acctid,$victim);";
			$result=db_query($sql); 
			if ($result) {
				output("`vThe users %s`v and %s`v has been notified and the marriage been cancelled. If you want to ban etc, please use the normal ban editor.`0`n`n",$one,$two);
			} else {
				output("Unknown error! I was not able to update the referer to 0!");
				debug("User:".$acctid."-".$one);
				debug("Target:".$victim."-".$two);
			}
			$op=$oldop;
		break;
	}
	if ($previousaction!='addtowhitelist') {
		addnav("Whitelist");
		addnav("This search without whitelisted players","runmodule.php?module=multichecker&oldop=$op&op=withoutwhitelist");
	} else {
		addnav("Whitelist");
		addnav("This search without whitelisted players","");	
	}
	switch ($op) {

		case "IP":
			if ($implodedwhitelist!='') $where="AND a.acctid NOT IN ($implodedwhitelist)";
				else $where='';
			$sql="SELECT a.login as UserA, a.acctid as AcctidA,b.login as UserB,b.acctid as AcctidB,
				a.lastip as IP, b.referer as referer, a.emailaddress as emailA, b.emailaddress as emailB
				FROM  ".db_prefix("accounts")."  AS b
				LEFT JOIN  ".db_prefix("accounts")." AS a
				ON b.lastip = a.lastip WHERE b.login <> a.login $where
				ORDER BY a.acctid";
			output("Note: AOL proxys have always the same IP, AOL users are therefore treated as multis.");
			output_notl("`n`n");
			output("University and other nets who have one proxy IP will also appear here, please check them carefully.");
			break;
		case "ID":
			if ($implodedwhitelist!='') $where="AND a.acctid NOT IN ($implodedwhitelist)";
				else $where='';
			$sql="SELECT a.login as UserA, a.acctid as AcctidA,b.login as UserB,b.acctid as AcctidB,
				a.uniqueid as IP, b.referer as referer,	a.emailaddress as emailA, b.emailaddress as emailB
				FROM  ".db_prefix("accounts")."  AS b
				LEFT JOIN  ".db_prefix("accounts")." AS a
				ON b.uniqueid = a.uniqueid WHERE b.login <> a.login $where
				ORDER BY a.acctid";
			output("Note: The ID is the cookie ID stored on the users machine.");
			output(" If he uses another browser, you won't be able to track him down.");
			output(" This check should be used WITH the IP check, because here you won't have to care for proxies like AOL, University nets and the like.");
			break;
		case "selfIP":
			if ($implodedwhitelist!='') $where="AND a.acctid NOT IN ($implodedwhitelist)";
				else $where='';
			$sql="SELECT a.login as UserA, a.acctid as AcctidA,b.login as UserB,b.acctid as AcctidB,
				a.lastip as IP, b.referer as referer, a.emailaddress as emailA, b.emailaddress as emailB,
				b.level as LevelB 
				FROM  ".db_prefix("accounts")."  AS b
				LEFT JOIN  ".db_prefix("accounts")." AS a
				ON b.lastip = a.lastip WHERE b.login <> a.login $where
				AND b.referer=a.acctid
				ORDER BY a.acctid";
			output("Note: AOL proxys have always the same IP, AOL users are therefore treated as multis.");
			output_notl("`n`n");
			output("University and other nets who have one proxy IP will also appear here, please check them carefully.");
			break;
		case "selfID":
			if ($implodedwhitelist!='') $where="AND a.acctid NOT IN ($implodedwhitelist)";
				else $where='';
			$sql="SELECT a.login as UserA, a.acctid as AcctidA,b.login as UserB,b.acctid as AcctidB,
				a.uniqueid as IP, b.referer as referer,	a.emailaddress as emailA, b.emailaddress as emailB,
				b.level as LevelB 
				FROM  ".db_prefix("accounts")."  AS b
				LEFT JOIN  ".db_prefix("accounts")." AS a
				ON b.uniqueid = a.uniqueid WHERE b.login <> a.login $where
				AND b.referer=a.acctid
				ORDER BY a.acctid";
			output("Note: The ID is the cookie ID stored on the users machine.");
			output(" If he uses another browser, you won't be able to track him down.");
			output(" This check should be used WITH the IP check, because here you won't have to care for proxies like AOL, University nets and the like.");
			break;
		case "marriageIP":
			if ($implodedwhitelist!='') $where="AND a.acctid NOT IN ($implodedwhitelist)";
				else $where='';
			$sql="SELECT a.login as UserA, a.acctid as AcctidA,b.login as UserB,b.acctid as AcctidB,
				a.lastip as IP, b.referer as referer, a.emailaddress as emailA, b.emailaddress as emailB,
				b.level as LevelB 
				FROM  ".db_prefix("accounts")."  AS b
				LEFT JOIN  ".db_prefix("accounts")." AS a
				ON b.lastip = a.lastip WHERE b.login <> a.login $where
				AND b.marriedto=a.acctid
				ORDER BY a.acctid";
			output("Note: AOL proxys have always the same IP, AOL users are therefore treated as multis.");
			output_notl("`n`n");
			output("University and other nets who have one proxy IP will also appear here, please check them carefully.");
			break;
		case "marriageID":
			if ($implodedwhitelist!='') $where="AND a.acctid NOT IN ($implodedwhitelist)";
				else $where='';
			$sql="SELECT a.login as UserA, a.acctid as AcctidA,b.login as UserB,b.acctid as AcctidB,
				a.uniqueid as IP, b.referer as referer,	a.emailaddress as emailA, b.emailaddress as emailB,
				b.level as LevelB 
				FROM  ".db_prefix("accounts")."  AS b
				LEFT JOIN  ".db_prefix("accounts")." AS a
				ON b.uniqueid = a.uniqueid WHERE b.login <> a.login $where
				AND b.marriedto=a.acctid
				ORDER BY a.acctid";
			output("Note: The ID is the cookie ID stored on the users machine.");
			output(" If he uses another browser, you won't be able to track him down.");
			output(" This check should be used WITH the IP check, because here you won't have to care for proxies like AOL, University nets and the like.");
			break;
		case "fianceeID":
			if ($implodedwhitelist!='') $where="AND a.acctid NOT IN ($implodedwhitelist)";
				else $where='';
			$sql="SELECT a.login as UserA, a.acctid as AcctidA,b.login as UserB,b.acctid as AcctidB,
				a.uniqueid as IP, c.value as referer, a.emailaddress as emailA, b.emailaddress as emailB,
				b.level as LevelB
				FROM  ".db_prefix("accounts")."  AS b
				LEFT JOIN ".db_prefix("module_userprefs")." AS c
				ON b.acctid = c.userid
				LEFT JOIN  ".db_prefix("accounts")." AS a
				ON b.uniqueid = a.uniqueid 
				LEFT JOIN ".db_prefix("module_userprefs")." AS d
				ON a.acctid = d.userid".
//				"WHERE b.login <> a.login". 
				" WHERE 1 ".
				"AND c.modulename='marriage'
				AND c.setting='fiancee'
				AND c.value=a.acctid
				AND d.value=b.acctid
				$where".
//				"AND b.marriedto=a.acctid".
				"ORDER BY a.acctid";
			debug($sql);
			output("Note: The ID is the cookie ID stored on the users machine.");
			output(" If he uses another browser, you won't be able to track him down.");
			output(" This check should be used WITH the IP check, because here you won't have to care for proxies like AOL, University nets and the like.");
			break;				
		default:
			output("Please specify an action!");
			page_footer();
	}
	$result=db_query($sql);
	$i=0;
	rawoutput("<table border='0' cellpadding='2' cellspacing='0'>");
	rawoutput("<tr class='trhead'><td>". translate_inline("Name A")."</td><td>".translate_inline("Acctid")."</td><td>". translate_inline("Name B")."</td><td nowrap>".translate_inline("Actions")."</td><td>".translate_inline("Acctid")."</td><td>".translate_inline("Referer")."</td><td>".translate_inline("IP/ID")."</td><td>".translate_inline("Email A")."</td><td>".translate_inline("Email B")."</td></tr>");
	$old=array();
	if ($op=="selfIP"||$op=="selfID"||$op=="marriageID"||$op=="marriageIP") $check=TRUE;
		else $check=FALSE;
	$derefer=translate_inline("Derefer");
	$addtowhitelist=translate_inline("Add to Whitelist");
	$warn=translate_inline("Unmarry&Notify");
	$previousaction=httpget('op');
	while ($row=db_fetch_assoc($result)) {
		if (!in_array($row['AcctidB'],$old)) {
			$i++;
			rawoutput("<tr class='".($i%2?"trlight":"trdark")."'><td>");
			output_notl($row['UserA']);
			rawoutput("</td><td>");
			if ($row['AcctidA']==$row['referer']) output_notl("`\$");
			output_notl($row['AcctidA']);
			rawoutput("</td><td>");
			if ($row['AcctidA']==$row['referer']) output_notl("`\$");
			output_notl($row['UserB']);
			if ($check) output("(Lvl: %s)",$row['LevelB']);
			rawoutput("</td><td nowrap>");
			if ($row['AcctidA']==$row['referer']) {
				rawoutput("<a href='runmodule.php?module=multichecker&op=derefer&acctid={$row['AcctidA']}&multi={$row['AcctidB']}&oldop=$op'>$derefer</a>");
				addnav("","runmodule.php?module=multichecker&op=derefer&acctid={$row['AcctidA']}&oldop=$op");
				if ($previousaction!='addtowhitelist') {
					output_notl("`~ || ");
					rawoutput("<a href='runmodule.php?module=multichecker&op=addtowhitelist&acctid={$row['AcctidA']}&multi={$row['AcctidB']}&oldop=$op'>$addtowhitelist</a>");
					//multi is not used currently
					addnav("","runmodule.php?module=multichecker&op=addtowhitelist&acctid={$row['AcctidA']}&oldop=$op");
				}
			}
			if ($op=="marriageID"||$op=="marriageIP") {
				rawoutput("<a href='runmodule.php?module=multichecker&op=marriagewarn&acctid={$row['AcctidA']}&multi={$row['AcctidB']}&oldop=$op'>$warn</a>");
				addnav("","runmodule.php?module=multichecker&op=marriagewarn&acctid={$row['AcctidA']}&oldop=$op");			
			
			}
			if ($op=="fianceeID") {
				rawoutput("<a href='runmodule.php?module=multichecker&op=fianceewarn&acctid={$row['AcctidA']}&multi={$row['AcctidB']}&oldop=$op'>$warn</a>");
				addnav("","runmodule.php?module=multichecker&op=fianceewarn&acctid={$row['AcctidA']}&oldop=$op");			
			
			}
			rawoutput("</td><td>");
			if ($row['AcctidA']==$row['referer']) output_notl("`\$");
			output_notl($row['AcctidB']);
			rawoutput("</td><td>");
			if ($row['AcctidA']==$row['referer']) output_notl("`\$");
			output_notl($row['referer']);
			rawoutput("</td><td>");
			output_notl($row['IP']);
			rawoutput("</td><td>");
			output_notl($row['emailA']);
			rawoutput("</td><td>");
			output_notl($row['emailB']);
			rawoutput("</td></tr>");
			array_push($old,$row['AcctidA']);
			array_push($old,$row['AcctidB']);
		}
	}
	rawoutput("</table>");
	page_footer();

}
?>
