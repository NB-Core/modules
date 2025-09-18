<?php

function hofbattlearena_getmoduleinfo(){
	$infos = array(
		"name"=>"BattleArenaRankings in HOF",
		"version"=>"1.0",
		"author"=>"`2Oliver Brendel",
		"category"=>"Stats",
		"download"=>"",
		"requires"=>array(
		"battlearena"=>"3.0|`#Lonny Luberts `2modified by Oliver Brendel",
		),
	);
	return $infos;
}
function hofbattlearena_install(){
	module_addhook_priority("hof-add",78);
	return true;
}
function hofbattlearena_uninstall(){
	return true;
}
function hofbattlearena_dohook($hookname,$args)
{
	global $session;
	switch($hookname){
	case "hof-add":
		addnav("Battle Arena");
		addnav("Monthly Battlearena Rankings","runmodule.php?module=hofbattlearena");
		addnav("Overall Battlearena Rankings","runmodule.php?module=hofbattlearena&op=overall");
		break;
	}
	return $args;
}

function hofbattlearena_run(){
	global $session;
	$op=httpget('op');
	$month=httpget('month');
	$year=httpget('year');
	$howmany=50; //per page
	$from=httppost('from');
	if (!$from) $from=0;
	if (httppost('previous')) $from-=$howmany;
	if (httppost('next')) $from+=$howmany;
	page_header("Battle Arena Ranking %s %s",$month,$year);
	addnav("Back to the HOF","hof.php");
	addnav("Refresh","runmodule.php?module=hofbattlearena&op=$op&month=$month&year=$year");
	addnav("Ranking");
	addnav("Overall Rankings","runmodule.php?module=hofbattlearena&op=overall");
	addnav("Months");
	$sql = "SELECT setting,objid FROM ".db_prefix("module_objprefs")." WHERE modulename='battlearena' AND objtype='highscore' ORDER BY setting,objid ASC;";
	$result=db_query_cached($sql,"hofbattlearena",600);
	while ($row=db_fetch_assoc($result)) {
		addnav_notl(array("%s/%s",$row['objid'],$row['setting']), "runmodule.php?module=hofbattlearena&op=show&month={$row['objid']}&year={$row['setting']}");
	}
	switch($op) {
		case "show":
			output("`b`i`cStatistics for %s/%s`c`i`b",$month,$year);
			output_notl("`n`n");
			hofbattlearena_show($month,$year);
			break;
		case "overall":
		    output("`b`i`cOverall Statistics for the Battlearena`c`i`b");
			output_notl("`n`n");
			$sql = "SELECT a.name as name, b.value as points FROM ".db_prefix("module_userprefs")." as b RIGHT JOIN ".db_prefix('accounts')." as a ON a.acctid=b.userid WHERE modulename='battlearena' AND  setting='battlepoints' AND b.value+0>0 ORDER BY b.value+0 DESC LIMIT $from,$howmany;";
			$result=db_query_cached($sql,"overallbattlearena-$from",600);
			rawoutput("<form action='runmodule.php?module=hofbattlearena&op=overall' method='POST'>");
			addnav("","runmodule.php?module=hofbattlearena&op=overall");
			$previous=translate_inline("Previous Page");
			$next=translate_inline("Next Page");
			if ($from>0) rawoutput("<input type='submit' class='submit' name='previous' value='$previous'>");
			if ($howmany==db_num_rows($result)) rawoutput("<input type='submit' class='submit' name='next' value='$next'>");
			rawoutput("<input type=hidden name='from' value='$from'>");
			rawoutput("</form>");
			$i=$from;
			output_notl("`c");
			rawoutput("<table border='0' cellpadding='2' cellspacing='0'>");
			rawoutput("<tr class='trhead'><td>". translate_inline("Rank") ."</td><td>". translate_inline("Name") ."</td><td>".translate_inline("Battlepoints")."</td></tr>");
			while ($row=db_fetch_assoc($result)) {
				$i++;
				rawoutput("<tr class='".($i%2?"trlight":"trdark")."'><td>");
                                output("%s", $i);
				rawoutput("</td><td>");
				output_notl("%s", $row['name']);
				rawoutput("</td><td align='center'>");
				output_notl("%s", $row['points']);
				rawoutput("</td></tr>");
			}
			rawoutput("</table>");
			output_notl("`c");
			break;
		default:
			output("`b`i`cCurrent Statistics for the Battle Arena`c`i`b");
			output_notl("`n`n");
			output("`cRefreshed every 10 minutes.`c`n`n");
			if (db_num_rows($result)==0) {
				output("`n`nIt seems there has no month passed since the statistics were introduced. Please be patient.");	break;
			}
			$day=getdate(time());
			require_once("modules/battlearena/battlearena_monthly.php");
			battlearena_monthly($day['mon'],$day['year'],false);
			hofbattlearena_show($day['mon'],$day['year']);
			break;
	}
	page_footer();

	return true;
}

function hofbattlearena_show($month,$year) {
	$sql = "SELECT value FROM ".db_prefix("module_objprefs")." WHERE modulename='battlearena' AND objtype='highscore' AND setting='$year' AND objid='$month' ";
	$result=db_query($sql);
	if (db_num_rows($result)<1) {
		output("None"); //error correction
		page_footer();
		return;
	}
	$row=db_fetch_assoc($result);
	$array=unserialize($row['value']);
	if (!is_array($array)) $array=array();
	$cities=array_keys($array);
	output_notl("`c");
	foreach($cities as $city) {
		output_notl("`b`\$%s`0`b`n",$city);
		rawoutput("<table border='0' cellpadding='2' cellspacing='0'>");
		rawoutput("<tr class='trhead'><td>". translate_inline("Rank") ."</td><td>". translate_inline("Name") ."</td><td>".translate_inline("Battlepoints")."</td></tr>");
		$i=0;
		foreach($array[$city] as $val) {
			$i++;
			rawoutput("<tr class='".($i%2?"trlight":"trdark")."'><td>");
			output_notl("%s", $i);
			rawoutput("</td><td>");
			output_notl("%s", $val['name']);
			rawoutput("</td><td align='center'>");
			output_notl("%s", $val['points']);
			rawoutput("</td></tr>");
		}
		rawoutput("</table>");
	}
	output_notl("`c");
}
?>
