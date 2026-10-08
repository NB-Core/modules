<?php

function pktrack_getmoduleinfo(){
	$info = array(
		"name"=>"PK Tracking",
		"author"=>"Chris Vorndran",
		"version"=>"1.5",
		"category"=>"Stat Display",
		"download"=>"http://dragonprime.net/users/Sichae/pktrack.zip",
		"vertxtloc"=>"http://dragonprime.net/users/Sichae/",
		"description"=>"This module will track the amount of PKs (Player Kills) that a user has, and generate a Hall of Fame page from the information.",
		"settings"=>array(
			"PK Tracking Settings,title",
			"wo"=>"Which heading does this fall under,enum,0,Vital Info,1,Personal Info,2,Extra Info|0",
			"pp"=>"Display how many results in the HoF page,int|50",
			"shz"=>"Show people with zero PKs,bool|1",
			"dispad"=>"Show superusers in HoF listing,enum,0,Yes,1,No|1",
			"This applies to even those that have the 'Account Never Expires' Flag,note",
		),
		"user_prefs"=>array(
			"PK Tracking Prefs,title",
			"user_showpk"=>"Show Player Kills in Info area,bool|1",
		),
		"prefs"=>array(
			"PK Tracking Prefs,title",
			"user_showpk"=>"Show Player Kills in Info area,bool|1",
			"count"=>"Amount of PKs (Player Kills) ,int|0",
			"losecount"=>"Amount of PKs (Player Kills) lost ,int|0",
		),
		);
	return $info;
}
function pktrack_install(){
	module_addhook("pvpwin");
	module_addhook("pvploss");
	module_addhook("hof-add");
	module_addhook("biostat");
	return true;
	}
function pktrack_uninstall(){
	return true;
}
function pktrack_dohook($hookname,$args){
       global $session;
       switch ($hookname){
		case "pvpwin":
			increment_module_pref("count",1);
			break;
		case "pvploss":
			increment_module_pref("count",1);
			increment_module_pref("losecount",1);
			break;
               case "biostat":
                       $acctid = (int)($args['acctid'] ?? 0);
                       if (!get_module_pref('user_showpk','pktrack',$acctid)) {
                               output("`^Player Kills: %s does not want to show you.`0",$args['name']);
                               output_notl("`n");
                               break;
                       }
                       $cpk = get_module_pref("count","pktrack",$acctid);
                       $lpk = get_module_pref("losecount","pktrack",$acctid);
                       output("`^Player Kills: `@%s`v(`g%s won`0|`q%s lost`v)`0",$cpk,$cpk-$lpk,$lpk);
                       output_notl("`n");
                       break;
		case "hof-add":
			addnav("Warrior Rankings");
			addnav("Player Fights","runmodule.php?module=pktrack&op=hof");
			break;
		}
	return $args;
}
function pktrack_run(){
	global $session;
	$op = httpget('op');
	$page = httpget('page');
	if (get_module_setting("shz") == 1){
		$f = 0;
	}else{
		$f = 1;
	}
	if (get_module_setting("dispad") == 1){
		$g = "AND (superuser&".SU_HIDE_FROM_LEADERBOARD." = 0)";
	}else{
		$g = "";
	}

	switch ($op){
		case "hof":
			page_header("Hall of Fame");
			$pp = get_module_setting("pp");
			$pageoffset = (int)$page;
			if ($pageoffset > 0) $pageoffset--;
			$pageoffset *= $pp;
			$from = $pageoffset+1;
			$limit = "LIMIT $pageoffset,$pp";
			$sql = "SELECT COUNT(userid) AS c FROM " . db_prefix("module_userprefs") . " WHERE modulename = 'pktrack' AND setting = 'count' AND value >= '$f'";
			$result = db_query($sql);
			$row = db_fetch_assoc($result);
			$total = $row['c'];
			$count = db_num_rows($result);
			if ($from + $pp < $total){
				$cond = $pageoffset + $pp;
			}else{
				$cond = $total;
			}
				$sql = "SELECT ".db_prefix("module_userprefs").".value, ".db_prefix("accounts").".name, ".db_prefix("accounts").".level FROM ".db_prefix("module_userprefs")." , ".db_prefix("accounts"). " WHERE acctid = userid AND modulename = 'pktrack' AND setting = 'count' AND value >= '$f' $g ORDER BY (value+0) DESC $limit";
			$result = db_query($sql);
			$rank = translate_inline("Rank");
			$name = translate_inline("Name");
			$pk = translate_inline("Player Kills");
			$ran = translate_inline("In PvP Range?");
			rawoutput("<big>");
			output("`c`b`^Fiercest Warriors in the Land`b`c`0`n");
			rawoutput("</big>");
			rawoutput("<table border='0' cellpadding='2' cellspacing='1' align='center' bgcolor='#999999'>");
			rawoutput("<tr class='trhead'><td>$rank</td><td>$name</td><td>$pk</td><td>$ran</td></tr>");
			$total_sql=db_num_rows($result);
			if ($total_sql>0){
				for($i = $pageoffset; $i < $cond && $count; $i++) {
					$row = db_fetch_assoc($result);
					if (!isset($row['name'])) {
						//end of line
						continue;	
					}
					if ($row['name']==$session['user']['name']){
						rawoutput("<tr class='trhilight'><td>");
					} else {
						rawoutput("<tr class='".($i%2?"trdark":"trlight")."'><td>");
					}
					$j=$i+1;
					output_notl("$j.");
					rawoutput("</td><td>");
					output_notl("`&%s`0",$row['name']);
					rawoutput("</td><td>");
					output_notl("`c`@%s`c`0",$row['value']);
					rawoutput("</td><td>");
					if ($row['level'] <= ($session['user']['level']+2) && $row['level'] >= ($session['user']['level']-1)){
						$q = translate_inline("Yes");
					}else{
						$q = translate_inline("No");
					}
					if ($row['name'] == $session['user']['name']) $q = translate_inline("Always");
					output_notl("`c`@%s`c`0",$q);
					rawoutput("</td></tr>");
				}
			}
			rawoutput("</table>");
		if ($total>$pp){
			addnav("Pages");
			for ($p=0;$p<$total;$p+=$pp){
				addnav(array("Page %s (%s-%s)", ($p/$pp+1), ($p+1), min($p+$pp,$total)), "runmodule.php?module=pktrack&op=hof&page=".($p/$pp+1));
			}
		}
		break;
	}
addnav("Other");
addnav("Back to HoF", "hof.php");
if ($session['user']['alive']){
	villagenav();
}else{
	addnav("Return to the Shades", "shades.php");
}
page_footer();
}
?>
