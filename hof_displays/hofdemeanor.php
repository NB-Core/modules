<?php

function hofdemeanor_getmoduleinfo(){
	$infos = array(
		"name"=>"Demeanor in HOF",
		"version"=>"1.0",
		"author"=>"`2Oliver Brendel",
		"category"=>"Alignment",
		"download"=>"",
		"description"=>"Show the most lawful or chaotic in the HOF",
		"requires"=>array(
		"alignment"=>"1.9|WebPixie<br> `#Lonny Luberts<br>`^and Chris Vorndran",
		),
	);
	return $infos;
}
function hofdemeanor_install(){
	module_addhook_priority("hof-add",75);
	return true;
}
function hofdemeanor_uninstall(){
	return true;
}
function hofdemeanor_dohook($hookname,$args)
{
	global $session;
	switch($hookname){
	case "hof-add":
		addnav("Alignment");
		addnav("Most Lawful Guys","runmodule.php?module=hofdemeanor&op=Lawful");
		addnav("Most Chaotic Guys","runmodule.php?module=hofdemeanor&op=Chaotic");
		break;
	}
	return $args;
}

function hofdemeanor_run(){
	global $session;
	$op=httpget('op');
	$Chaoticalign = get_module_setting('chaosalign','alignment');
	$Lawfulalign = get_module_setting('lawfulalign','alignment');
	//$useralign = get_module_pref('alignment','alignment');
	switch ($op) { //+0 in the query to convert to integer, thanks to xchrisx
		case "Lawful":
			page_header("Best servants of Law");
			$title="Best servants of Law";
			$select="AND b.value>=$Lawfulalign AND (a.locked=0 AND (a.superuser & ".SU_HIDE_FROM_LEADERBOARD.") = 0)ORDER BY b.value+0 DESC;";
		break;
		case "Chaotic":
			page_header("Best servants of Chaos");
			$title="Best servants of Chaos";
			$select="AND b.value<=$Chaoticalign AND (a.locked=0 AND (a.superuser & ".SU_HIDE_FROM_LEADERBOARD.") = 0)ORDER BY b.value+0 ASC;";
			break;
		default:
			page_header("Best servants of Law");
			$title="Best servants of the Lawful";
			$select="AND b.value>=$Lawfulalign AND (a.locked=0 AND (a.superuser & ".SU_HIDE_FROM_LEADERBOARD.") = 0)ORDER BY b.value+0 DESC;";
			break;
	}
	$racesel = "IF(a.race!='0' and a.race!='',a.race,'".RACE_UNKNOWN."')";
	$sql="SELECT a.name as name, a.level as level, $racesel as data1, b.value as data2 FROM ".db_prefix("accounts")." AS a INNER JOIN ".db_prefix("module_userprefs")." AS b ON a.acctid=b.userid WHERE b.modulename='alignment' AND b.setting='demeanor' $select";
	hof_demeanor_display_table(translate_inline($title),$sql,false,false,array("Race"));
	addnav("Back to HOF","hof.php");
	addnav("Refresh","runmodule.php?module=hofdemeanor&op=$op");
	if ($op=="Chaotic")
		addnav("Most Lawful Guys","runmodule.php?module=hofdemeanor&op=Lawful");
	else
		addnav("Most Chaotic Guys","runmodule.php?module=hofdemeanor&op=Chaotic");
	page_footer();

	return true;
}

//taken from HOF.php
function hof_demeanor_display_table($title, $sql, $none=false, $foot=false,
		$data_header=false, $tag=false, $translate=false)
{
	global $session, $from, $to, $page, $playersperpage, $totalplayers;
	$from=1;
	$title = translate_inline($title);
	if ($foot !== false) $foot = translate_inline($foot);
	if ($none !== false) $none = translate_inline($none);
	else $none = translate_inline("No players found.");
	if ($data_header !== false) {
		$data_header = translate_inline($data_header);
		reset ($data_header);
	}
	if ($tag !== false) $tag = translate_inline($tag);
	$rank = translate_inline("Rank");
	$name = translate_inline("Name");
	if ($totalplayers > $playersperpage) {
		output("`c`b`^%s`0`b `7(Page %s: %s-%s of %s)`0`c`n", $title, $page, $from, $to, $totalplayers);
	} else {
		output("`c`b`^%s`0`b`c`n", $title);
	}
	rawoutput("<table cellspacing='0' cellpadding='2' align='center'>");
	rawoutput("<tr class='trhead'>");
	output_notl("<td>`b$rank`b</td><td>`b$name`b</td>", true);
	if ($data_header !== false) {
		for ($i = 0; $i < count($data_header); $i++) {
			output_notl("<td>`b{$data_header[$i]}`b</td>", true);
		}
	}
	$result = db_query($sql);
	if (db_num_rows($result)==0){
		$size = ($data_header === false) ? 2 : 2+count($data_header);
		output_notl("<tr class='trlight'><td colspan='$size' align='center'>`&$none`0</td></tr>",true);
	} else {
		for ($i=0;$i<db_num_rows($result);$i++){
			$row = db_fetch_assoc($result);
			if ($row['name']==$session['user']['name']){
				rawoutput("<tr class='hilight'>");
			} else {
				rawoutput("<tr class='".($i%2?"trlight":"trdark")."'>");
			}
			output_notl("<td>%s</td><td>`&%s`0</td>",($i+$from), $row['name'], true);
			if ($data_header !== false) {
				for ($j = 0; $j < count($data_header); $j++) {
					$id = "data" . ($j+1);
					$val = $row[$id];
					if (isset($translate[$id]) &&
							$translate[$id] == 1 && !is_numeric($val)) {
						$val = translate_inline($val);
					}
					if ($tag !== false) $val = $val . " " . $tag[$j];
					output_notl("<td align='right'>%s</td>", $val, true);
				}
			}
			rawoutput("</tr>");
		}
	}
	rawoutput("</table>");
	if ($foot !== false) output_notl("`n`c%s`c", $foot);
}
?>
