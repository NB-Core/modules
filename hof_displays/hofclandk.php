<?php

function hofclandk_getmoduleinfo(){
	$info = array(
		"name"=>"Hall of Fame: Clan Dragon Kills",
		"author"=>"Chris Vorndran, additions by `2Oliver Brendel",
		"version"=>"1.0",
		"category"=>"General",
		"download"=>"http://dragonprime.net/users/Sichae/hofclandk.zip",
		"vertxtloc"=>"http://dragonprime.net/users/Sichae/",
		"description"=>"This module will display DKs of a clan in the Hall of Fame",
		"settings"=>array(
			"Hall of Fame: Clan Dragon Kills Settings,title",
			"pp"=>"How many listings Per Page,int|50",
		),
	);
	return $info;
}
function hofclandk_install(){
	module_addhook_priority("hof-add",82);
	return true;
}
function hofclandk_uninstall(){
	return true;
}
function hofclandk_dohook($hookname,$args){
	global $session;
	switch ($hookname){
		case "hof-add":
			addnav("Clan Rankings");
			addnav("Clan Dragon Kills","runmodule.php?module=hofclandk");
			break;
		}
	return $args;
}
function hofclandk_run(){
	global $session;
	page_header("Clan Dragon Kills");
	$op = httpget('op');
	$pp = get_module_setting("pp");
	$page = (int)httpget('page');
	$pageoffset = (int)$page;
	if ($pageoffset > 0) $pageoffset--;
	$pageoffset *= $pp;
	$from = $pageoffset+1;
	$limit = "LIMIT $pageoffset,$pp";
	$sql = "SELECT COUNT(clanid) AS c FROM ".db_prefix("clans")."";
	$result = db_query($sql);
	$row = db_fetch_assoc($result);
	$total = $row['c'];
	$count = db_num_rows($result);
	if ($from + $pp < $total){
		$cond = $pageoffset + $pp;
	}else{
		$cond = $total;
	}
	$sql = "SELECT sum(".db_prefix("accounts").".dragonkills) AS dks, count(".db_prefix("accounts").".clanid) AS memcount, ".db_prefix("clans").".clanname,".db_prefix("accounts").".clanid FROM ".db_prefix("accounts")." INNER JOIN ".db_prefix("clans")." ON ".db_prefix("accounts").".clanid=".db_prefix("clans").".clanid WHERE ".db_prefix("accounts").".clanid != 0 GROUP BY ".db_prefix("accounts").".clanid ORDER BY dks DESC, memcount ASC $limit";
	$res = db_query($sql);
	rawoutput("<big>");
	output("`c`b`^Clan Dragon Kill Rankings`b`c`0`n");
	rawoutput("</big>");
	$rank = translate_inline("Rank");
	$name = translate_inline("Clan Name");
	$mem = translate_inline("# of Members");
	$dk = translate_inline("Dragonkills");
	$ratio = translate_inline("DK to Member Ratio");
	rawoutput("<table border='0' cellpadding='2' cellspacing='1' align='center' bgcolor='#999999'>");
	rawoutput("<tr class='trhead'><td>$rank</td><td align='center'>$name</td><td>$mem</td><td>$dk</td><td>$ratio</td></tr>");
	$i=0;
	if (db_num_rows($res)>0){
		while($row = db_fetch_assoc($res)) {
			$i++;;
			if ($row['clanid']==$session['user']['clanid']){
				rawoutput("<tr class='trhilight'><td align='center'>");
			} else {
				rawoutput("<tr class='".($i%2?"trdark":"trlight")."'><td align='center'>");
			}
			$j=$i+$pageoffset;
			output_notl("$j");
			rawoutput("</td><td align='center'>");
			output_notl("`^%s`0",$row['clanname']);
			rawoutput("</td><td align='center'>");
			output_notl("`@%s`0",$row['memcount']);
			rawoutput("</td><td align='center'>");
			output_notl("`@%s`0",$row['dks']);
			rawoutput("</td><td align='center'>");
			if ($row['memcount']>0) output_notl("`@%s`0",round($row['dks']/$row['memcount'],2));
			rawoutput("</td></tr>");
		}
	}
	rawoutput("</table>");
	if ($total>$pp){
		addnav("Pages");
		for ($p=0;$p<$total;$p+=$pp){
			addnav(array("Page %s (%s-%s)", ($p/$pp+1), ($p+1), min($p+$pp,$total)), "runmodule.php?module=hofclandk&page=".($p/$pp+1));
		}
	}
addnav("Leave");
addnav("Return to HoF","hof.php");
page_footer();
}
?>