<?php

use Doctrine\DBAL\ParameterType;
use Lotgd\MySQL\Database;

function bingobook_addon_getmoduleinfo(){
	$info = array(
		"name"=>"Bingo Book Addon",
		"version"=>"1.0",
		"author"=>"`2Oliver Brendel",
		"category"=>"Mail", 
		"settings"=>array(
			"Bingo Book Addon - Settings,title",
		),
		"requires"=>array(
			"bingobook"=>"1.0|`2Oliver Brendel",
		),
		
	);
	return $info;
}

function bingobook_addon_install(){
	module_addhook("bartenderbribe");
	return true;
}

function bingobook_addon_uninstall(){
	return true;
}

function bingobook_addon_dohook($hookname,$args){
	global $session;
	switch($hookname){
		case "bartenderbribe":
			addnav("Ask about Bingo Book","runmodule.php?module=bingobook_addon&op=ask");
		break;
	}
	return $args;
}

function bingobook_addon_run(){
	global $session;
	$op = httpget('op');
	page_header("Bingo Book");
	$barkeep=getsetting('barkeep','`tCedrik');
	addnav("Navigation");
	addnav("Back to the inn","inn.php");
	addnav("Actions");
	switch ($op) {
		case "checkcomment":
			$go=(int)httpget('go');
			$link="runmodule.php?module=bingobook_addon&op=checkcomment";
			$user=httppost('user');
			if ($go==1) {
				$ac=(int)httpget('ac');
				require_once("modules/bingobook/func.php");
				$data=bingobook_get($session['user']['acctid'],$ac);
				$conn=Database::getDoctrineConnection();
				$accounts=Database::prefix('accounts');
				$row=$conn->executeQuery(
					"SELECT name FROM {$accounts} WHERE acctid = :acctid LIMIT 1",
					['acctid'=>$ac],
					['acctid'=>ParameterType::INTEGER]
				)->fetchAssociative();
				if ($data==array()) {
					output("\"`%Erm, you are not that nin's bingo book, sufficient answer?`0\" he remarks.");
					addnav("Ask about Bingo Book","runmodule.php?module=bingobook_addon&op=ask");
				} else {
					output("%s`0 put about you in the bingo book:`n`n%s`n`n",$row['name'],$data['comment']);
				}
			} else {
				output("\"`%Who shall I look for?`0\" he asks gruffly.");			
				if ($user!='') bingobook_showform($user,$link,"Check Comment");
				bingobook_searchform($link);
			}
			break;	
		case "checkdate":
			$go=(int)httpget('go');
			$link="runmodule.php?module=bingobook_addon&op=checkdate";
			$user=httppost('user');
			if ($go==1) {
				$ac=(int)httpget('ac');
				require_once("modules/bingobook/func.php");
				$data=bingobook_get($session['user']['acctid'],$ac);
				$conn=Database::getDoctrineConnection();
				$accounts=Database::prefix('accounts');
				$row=$conn->executeQuery(
					"SELECT name FROM {$accounts} WHERE acctid = :acctid LIMIT 1",
					['acctid'=>$ac],
					['acctid'=>ParameterType::INTEGER]
				)->fetchAssociative();
				$date=strtotime($data['entrydate']);
				$year=date("Y",$date);
				$month=translate_inline(date("F",$date),"datetime");
				$day=date("d",$date);
				if ($data==array()) {
					output("\"`%Erm, you are not that nin's bingo book, sufficient answer?`0\" he remarks.");
					addnav("Ask about Bingo Book","runmodule.php?module=bingobook_addon&op=ask");
				} else {
					output("%s`0 put you in the bingo book on %s %s in %s... whatever you did to this nin on that day...`n`n",$row['name'],$month,$day,$year);
				}
			} else {
				output("\"`%Who shall I look for?`0\" he asks gruffly.");			
				if ($user!='') bingobook_showform($user,$link,"Check Entrydate");
				bingobook_searchform($link);
			}
			break;
		case "whohasme":
			output("%s`0 looks at you sort-of sideways like.",$barkeep);
			output("\"`%Here ya go...`0\"...`n`n");
			bingobook_showbingo();
			break;
		case "ask":
			output("%s`0 looks at you sort-of sideways like.",$barkeep);
			output("`n`n\"`%I can tell you who has you in his bingo book... or even what he wrote about you in his book... or I can tell you when you were added...`0\"`n`n");
			output("\"`%What d'ya want?`0\" he asks gruffly.");
			addnav("Who has me in his bingo book?","runmodule.php?module=bingobook_addon&op=whohasme");
			addnav("Check comment","runmodule.php?module=bingobook_addon&op=checkcomment");
			addnav("Check entrydate","runmodule.php?module=bingobook_addon&op=checkdate");
		default:
	
	}
	page_footer();
}

function bingobook_showform($name,$link,$op="Check") {
	global $session;
	// httppost() adds slashes; the pattern is bound, so it needs the raw name.
	$plain=stripslashes((string)$name);
	$string="%";
	for ($x=0;$x<strlen($plain);$x++){
		$string .= substr($plain,$x,1)."%";
	}
	$conn = Database::getDoctrineConnection();
	$accounts = Database::prefix("accounts");
	rawoutput("<table style='text-align:center;' cellpadding='3' cellspacing='0' border='0'>");
	rawoutput("<tr class='trhead'><td>".translate_inline("Name")."</td><td>".translate_inline("Born As")."</td><td>".translate_inline("Operations")."</td></tr>");
	$rows=$conn->executeQuery(
		"SELECT name,dragonkills,acctid,race FROM {$accounts} WHERE name LIKE :name AND acctid<>:acctid AND locked=0 ORDER BY level,name ASC",
		['name'=>$string,'acctid'=>(int)$session['user']['acctid']],
		['name'=>ParameterType::STRING,'acctid'=>ParameterType::INTEGER]
	)->fetchAllAssociative();
	$x=0;
	foreach ($rows as $row) {
		$x=!$x;
		rawoutput("<tr class='".($x?"trlight":"trdark")."'>");
		rawoutput("<td>".appoencode($row['name'],false)."</td>");
		rawoutput("<td><span class='colLtYellow'>".htmlentities(translate_inline($row['race'],"race"))."</span></td>");
		$ops = "[<a href='$link&ac=".$row['acctid']."&go=1' class='colDkRed'>".translate_inline($op)."</a>]";
		addnav("",$link."&ac=".$row['acctid']."&go=1");
		rawoutput("<td>$ops</td></tr>");
	}
	if (count($rows)==0) {
		rawoutput("<tr class='trlight'><td colspan='5'>");
		output("`^No users found");
		rawoutput("</td></tr>");
	}
	rawoutput("</table>");
}

function bingobook_searchform($link) {
	rawoutput("<form action='$link' method='POST'>");
	addnav("",$link);
	rawoutput("<input type='input' name='user'>");
	$submit=translate_inline("Submit");
	rawoutput("<input type='submit' class='button' name='go' value='$submit'>");
	rawoutput("</form>");
}

function bingobook_showbingo($user=false) {
	global $session;
	if ($user===false) $user=$session['user']['acctid'];
	require_once("modules/bingobook/func.php");
	$data=bingobook_getbingo($user);
	output_notl("`c");
	rawoutput("<table style='text-align:center;' cellpadding='3' cellspacing='0' border='0'>");
	rawoutput("<tr class='trhead'><td>".translate_inline("Name")."</td></tr>");
	$x=1;
	foreach ($data as $row) {
		$x=!$x;
		rawoutput("<tr class='".($x?"trlight":"trdark")."'>");
		rawoutput("<td>".appoencode($row['username'],false)."</td></tr>");
	}
	if (count($data)==0) {
		rawoutput("<tr class='trlight'><td colspan='5'>");
		output("`^No users found");
		rawoutput("</td></tr>");
	}
	rawoutput("</table>");
	output_notl("`c");
}
?>
