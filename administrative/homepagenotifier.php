<?php

function homepagenotifier_getmoduleinfo(){
$info = array(
	"name"=>"Homepagenotifier",
	"description"=>"You can enter text that is displayed on your homepage with stats",
	"version"=>"1.0",
	"author"=>"`2Oliver Brendel",
	"override_forced_nav"=>true,
	"category"=>"Administrative",
	"download"=>"http://lotgd-downloads.com",
	"settings"=>array(
		"Homepage notifier Module - Settings,title",
		"If you leave this empty there won't be a text,note",
		"Please use a <br > at the end of the line to make a line break in the text (colours and html allowed too),note",
		"hometext"=>"Text you want to have on your homepage,textarea",
		"showstats"=>"Show stats like player with most dks etc on the page?,bool|1",
		),

	);
	return $info;
}

function homepagenotifier_install(){
	module_addhook_priority("index",100);
	return true;
}

function homepagenotifier_uninstall(){
	output_notl("`n`c`b`QNotifier Module - Uninstalled`0`b`c");
	return true;
}

function homepagenotifier_dohook($hookname, $args){
	global $session;
	switch ($hookname) {
		case "index":
			$text=get_module_setting("hometext");
			$show=get_module_setting("showstats");
			if ($show) {
				$accounts=db_prefix("accounts");
				$where="(locked=0 AND (superuser & ".SU_HIDE_FROM_LEADERBOARD.") = 0)";
				$sql="SELECT name FROM $accounts where $where order by dragonkills DESC limit 1";
				$result=db_query_cached($sql,"homepage_notifier_stats_dk");
				$row=db_fetch_assoc($result);
				if (db_num_rows($result)<1) $row['name']=translate_inline("`~Z`)araki `~K`)enpachi");
				output("`QThe most dragons has slain: `&%s`0`n",$row['name']);
				//$sql="SELECT name, cast(cast(goldinbank+gold as unsigned) as signed) as data FROM $accounts where $where order by data DESC limit 1";
				$sql="SELECT name, cast(goldinbank as signed)+cast(gold as signed) as data FROM $accounts where $where order by data DESC limit 1";
				$result=db_query_cached($sql,"homepage_notifier_stats_gold");
				$row=db_fetch_assoc($result);
				if (db_num_rows($result)<1) $row['name']=translate_inline("`~Z`)araki `~K`)enpachi");
				output("`QThe warrior with most cash is: `&%s`0`n",$row['name']);
				$sql="SELECT name FROM $accounts where $where order by gems desc limit 1";
				$result=db_query_cached($sql,"homepage_notifier_stats_gems");
				$row=db_fetch_assoc($result);
				if (db_num_rows($result)<1) $row['name']=translate_inline("`~Z`)araki `~K`)enpachi");
				output("`QThe warrior with most gems at hand is: `&%s`0`n",$row['name']);
				output_notl("`n");
			}
			if ($text!='') {
				output_notl($text,true);
			}
			break;
	}
	return $args;
}

function homepagenotifier_run(){
}

?>
