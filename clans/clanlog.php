<?php
use Lotgd\Forms;

//only working in 1.1.1 nighborn edition or up

function clanlog_getmoduleinfo(){
    $info = array(
        "name"=>"Clan Log (Who Did What)",
        "version"=>"1.0",
        "author"=>"`2Oliver Brendel",
        "category"=>"Clan",
        "download"=>"",
      
    );
    return $info;
}

function clanlog_install(){
	module_addhook("clanhall");
	module_addhook("footer-clan");
    return true;
}

function clanlog_uninstall(){
    return true;
}

function clanlog_dohook($hookname,$args){
    global $session;
    switch($hookname){
		case "clanhall":
			if ($session['user']['clanrank']>=CLAN_LEADER) {
				addnav("Clan Log");
				addnav("View Clan Log","runmodule.php?module=clanlog&op=view");
			}
		break;
		case "footer-clan":
			switch (httpget('op')) {
				case "membership":
					tlschema('clans');
					$ranks = array(CLAN_APPLICANT=>'`!Applicant`0',CLAN_MEMBER=>'`#Member`0',CLAN_OFFICER=>'`^Officer`0',CLAN_ADMINISTRATIVE=>"`\$Administrative`0",CLAN_LEADER=>'`&Leader`0',CLAN_FOUNDER=>'`\$Founder');
					$arg = modulehook('clanranks', array('ranks'=>$ranks, 'clanid'=>$session['user']['clanid']));
					$ranks = translate_inline($arg['ranks']);
					tlschema();
					if (method_exists(Forms::class, 'isUnverifiedRequest')) {
						// The core changes ranks and members from its posted, token-checked form only and
						// empties the POST otherwise, so these are the changes it made.
						$setrank = (int) httppost('setrank');
						$remove = (int) httppost('remove');
						$whoacctid = (int) httppost('whoacctid');
					} else {
						// Older cores took these from the links.
						$setrank = (int) httppost('setrank');
						if ($setrank===0) $setrank=(int) httpget('setrank');
						$remove=(int) httpget('remove');
						$whoacctid = (int) httpget('whoacctid');
					}
					if ($setrank > $session['user']['clanrank']) $setrank = 0;
					if ($setrank!=0 && $whoacctid>0) {
						$sql="SELECT name,login from ".db_prefix("accounts")." WHERE acctid=$whoacctid LIMIT 1";
						$result=db_query($sql);
						$row=db_fetch_assoc($result);
						$whoname=addslashes(sanitize($row['name']));
						clanlog_insert($session['user']['clanid'],"/me`3 changed rank of `%{$whoname}`3 to `^".$ranks[$setrank]."`3 (`^{$setrank}`3).",$session['user']['acctid']);
					} elseif ($remove!=0) {
						$sql="SELECT name,login from ".db_prefix("accounts")." WHERE acctid=$remove LIMIT 1";
						$result=db_query($sql);
						$row=db_fetch_assoc($result);
						$whoname=sanitize($row['name']);
						clanlog_insert($session['user']['clanid'],"/me`3 removed `%{$whoname}`3 from the clan.",$session['user']['acctid']);					
					} 
				break;

			
			}
		break;
		}
    return $args;
}

function clanlog_run () {
	global $session;
	page_header("Clan Log");
	require_once("lib/commentary.php");
	addcommentary();
	output("`c`\$~~~ The Clan Log ~~~`0`c`n`n");
	output("`3Here are the records of rank changes and removals.`n`n");
	addnav("Navigation");
	addnav("Return to the clan hall","clan.php");
	commentdisplay("`nAdd a note if necessary to a transaction:","clanlog-".$session['user']['clanid'],'',30,'notes');
	page_footer();
}

function clanlog_insert($id,$text,$author) {
	require_once("lib/commentary.php");
	injectrawcomment("clanlog-$id",$author,$text);
	return;
}
?>
