<?php

function hofclanpk_getmoduleinfo(){
	$info = array(
		"name"=>"Hall of Fame: Clan Player Kills",
		"author"=>"Chris Vorndran, modified by `2Oliver Brendel",
		"version"=>"1.0",
		"category"=>"General",
		"download"=>"http://dragonprime.net/index.php?module=Downloads;sa=dlview;id=70",
		"description"=>"This module will display PKs of a clan in the Hall of Fame",
		"settings"=>array(
			"Hall of Fame: Clan Player Kills Settings,title",
			"pp"=>"How many listings Per Page,int|50",
		),
		"requires"=>array(
			"pktrack"=>"1.0|Chris Vorndran",
		),
	);
	return $info;
}
function hofclanpk_install(){
	module_addhook_priority("hof-add",81);
	return true;
}
function hofclanpk_uninstall(){
	return true;
}
function hofclanpk_dohook($hookname,$args){
	global $session;
	switch ($hookname){
		case "hof-add":
			addnav("Clan Rankings");
			addnav("Clan Player Kills","runmodule.php?module=hofclanpk");
			break;
		}
	return $args;
}
function hofclanpk_run(){
	global $session;
	page_header("Clan Player Kills");
	$ac = db_prefix("accounts");
	$cl = db_prefix("clans");
	$mu = db_prefix("module_userprefs");
	$op = httpget('op');
	$pp = get_module_setting("pp");
	$page = (int)httpget('page');
	$pageoffset = (int)$page;
	if ($pageoffset > 0) $pageoffset--;
	$pageoffset *= $pp;
	$from = $pageoffset+1;
	$limit = "LIMIT $pageoffset,$pp";
	$sql = "SELECT COUNT(clanid) AS c FROM $cl";
	$result = db_query($sql);
	$row = db_fetch_assoc($result);
	$total = $row['c'];
	$count = db_num_rows($result);
	if ($from + $pp < $total){
		$cond = $pageoffset + $pp;
	}else{
		$cond = $total;
	}
	$sql = "SELECT sum($mu.value) AS pks, count($ac.clanid) AS memcount, $cl.clanname,$ac.clanid FROM $ac 
			INNER JOIN $cl 
			ON $ac.clanid=$cl.clanid 
			INNER JOIN $mu
			ON $ac.acctid=$mu.userid
			WHERE $ac.clanid != 0 
			AND $mu.modulename='pktrack'
			AND $mu.setting='count'
			GROUP BY $ac.clanid 
			ORDER BY pks DESC, 
			memcount ASC $limit";
	$res = db_query($sql);
	rawoutput("<big>");
	output("`c`b`^Clan Player Kill Rankings`b`c`0`n");
	rawoutput("</big>");
	$rank = translate_inline("Rank");
	$name = translate_inline("Clan Name");
	$mem = translate_inline("# of PvP Members");
	$dk = translate_inline("Playerkills");
	$ratio = translate_inline("PK to Member Ratio");
	rawoutput("<table border='0' cellpadding='2' cellspacing='1' align='center' bgcolor='#999999'>");
	rawoutput("<tr class='trhead'><td>$rank</td><td align='center'>$name</td><td>$mem</td><td>$dk</td><td>$ratio</td></tr>");
	if (db_num_rows($res)>0){
		$i = 0;
		while($row = db_fetch_assoc($res)){
			$i++;
			if ($row['clanid']==$session['user']['clanid']){
				rawoutput("<tr class='trhilight'><td align='center'>");
			} else {
				rawoutput("<tr class='".($i%2?"trdark":"trlight")."'><td align='center'>");
			}
			output_notl($i+$pageoffset);
			rawoutput("</td><td align='center'>");
			output_notl("`^%s`0",$row['clanname']);
			rawoutput("</td><td align='center'>");
			output_notl("`@%s`0",$row['memcount']);
			rawoutput("</td><td align='center'>");
			output_notl("`@%s`0",$row['pks']);
			rawoutput("</td><td align='center'>");
			output_notl("`@%s`0",round($row['pks']/$row['memcount'],2));
			rawoutput("</td></tr>");
		}
	}
	rawoutput("</table>");
	if ($total>$pp){
		addnav("Pages");
		for ($p=0;$p<$total;$p+=$pp){
			addnav(array("Page %s (%s-%s)", ($p/$pp+1), ($p+1), min($p+$pp,$total)), "runmodule.php?module=hofclanpk&page=".($p/$pp+1));
		}
	}
addnav("Leave");
addnav("Return to HoF","hof.php");
page_footer();
}
?>
