<?php

function circulum_hof_getmoduleinfo(){
	$infos = array(
		"name"=>"Circulum in HOF",
		"version"=>"1.0",
		"author"=>"`2Oliver Brendel",
		"category"=>"Circulum Vitae",
		"download"=>"",
		"requires"=>array(
			"circulum"=>"1.01|Oliver Brendel",
		),
		"prefs"=>array(
			"Prefs,title",
			"total"=>"Total number of DKs lost due to resets?,int|0",
			"totalgems"=>"Total number of gems lost due to resets?,int|0",
			"listdks"=>"DK listed in order or reset,text",
			"listgems"=>"Total number of gems lost due to resets?,text",
		),
	);
	return $infos;
}
function circulum_hof_install(){
	module_addhook_priority("hof-add",75);
	module_addhook("circulum-prereset");
	return true;
}
function circulum_hof_uninstall(){
	return true;
}
function circulum_hof_dohook($hookname,$args)
{
	global $session;
	switch($hookname){
		case "hof-add":
			addnav("Kekkei Genkai");
			addnav("Most Oro Kills Lost in Kekkei Genkai","runmodule.php?module=circulum_hof");
			addnav("Most Gems Lost in Kekkei Genkai","runmodule.php?module=circulum_hof&op=gems");
			break;
		case "circulum-prereset":
			$acctid=(int)$args['acctid'];
			if ($acctid==0) break;
			$sql="SELECT gems,dragonkills FROM ".db_prefix('accounts')." WHERE acctid=$acctid;";
			$result=db_query($sql);
			$row=db_fetch_assoc($result);
			increment_module_pref('total',$row['dragonkills'],'circulum_hof',$args['acctid']);
			increment_module_pref('totalgems',$row['gems'],'circulum_hof',$args['acctid']);
			$list=get_module_pref('listdks','circulum_hof');
			if ($list=='') $list=array();
				else $list=explode(",",$list);
			$list[]=$row['dragonkills'];
			$list=implode(",",$list);
			set_module_pref('listdks',$list,'circulum_hof',$args['acctid']);
			$list=get_module_pref('listgems','circulum_hof');
			if ($list=='') $list=array();
				else $list=explode(",",$list);
			$list[]=$row['gems'];
			$list=implode(",",$list);
			set_module_pref('listgems',$list,'circulum_hof',$args['acctid']);
			
			break;
	}
	return $args;
}

function circulum_hof_run(){
	global $session;
	$op=httpget('op');
	switch ($op) {
		case "":
			$setting="total";
			$label="Total Oro Kills Lost";
			break;
		case "gems":
			$setting="totalgems";
			$label="Total Gems Lost";
			break;
	}
	page_header("Most Oro Kills Lost in Kekkei Genkai");
	$select="AND (a.locked=0 AND (a.superuser & ".SU_HIDE_FROM_LEADERBOARD.") = 0)ORDER BY b.value+0 DESC;";
	$sql="SELECT a.name as name, a.level as level, b.value as data1, b.value as data2 FROM ".db_prefix("accounts")." AS a INNER JOIN ".db_prefix("module_userprefs")." AS b ON a.acctid=b.userid WHERE b.modulename='circulum_hof' AND b.value>0 AND b.setting='$setting' $select";
	circulum_hof_display_table(translate_inline($title),$sql,false,false,array($label));
	addnav("Back to HOF","hof.php");
	addnav("Refresh","runmodule.php?module=circulum_hof&op=$op");
	page_footer();

	return true;
}

//taken from HOF.php
function circulum_hof_display_table($title, $sql, $none=false, $foot=false,
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
