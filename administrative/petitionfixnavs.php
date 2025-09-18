<?php
/*

*/

function petitionfixnavs_getmoduleinfo() {
	$info = array(
	    "name"=>"Fixnavs in petitions",
		"version"=>"1.0",
		"author"=>"`2Oliver Brendel",
		"category"=>"Administrative",
		"download"=>"http://lotgd-downloads.com",
		);
    return $info;
}

function petitionfixnavs_install() {
	module_addhook("footer-viewpetition");
	module_addhook("petition-status");
	return true;
}

function petitionfixnavs_uninstall() {
	return true;
}


function petitionfixnavs_dohook($hookname, $args){
	global $session;
	switch ($hookname) {
	case "petition-status":
		addnav("Actions");
		break;
	
	case "footer-viewpetition":
		$op=httpget('op');
		$statuses=array();
		$setstat=(int)httpget('setstat');
		if ($setstat!=0) {
			//inject a commentary about the move
			$statuses = modulehook("petition-status", array());
			// attention: do not have ANY module that modifies the petitions only in here...
			$text=sprintf_translate("/me`0 moved this petition to category '%s`0'",$statuses[$setstat]);
			pt_insert($text);
		}
		if ($op!='view') return $args;
		addnav("Actions");
		$id=httpget('id');
		addnav("Fix this users navs","runmodule.php?module=petitionfixnavs&id=$id");
		//query took from viewpetition.php
		$nextsql="SELECT p2.author, p1.petitionid, p1.status FROM ".db_prefix("petitions")." AS p1, ".db_prefix("petitions")." AS p2 WHERE p1.petitionid>'$id' AND p2.petitionid='$id' AND p1.status=p2.status ORDER BY p1.petitionid ASC LIMIT 1";
		$nextresult=db_query($nextsql);
		$nextrow=db_fetch_assoc($nextresult);
		if ($nextrow){
			$nextid=$nextrow['petitionid'];
//what does this do again? I don't get it what I wanted at that time
//			$s=$nextrow['status'];
//			$status=$statuses[$s];
			addnav("Close and next","runmodule.php?module=petitionfixnavs&op=cnext&id=$id&nextid=$nextid");
			addnav("Fix,close and next","runmodule.php?module=petitionfixnavs&op=cfnext&id=$id&nextid=$nextid");
			addnav("Notify done,close,next","runmodule.php?module=petitionfixnavs&op=ncnext&id=$id&nextid=$nextid");
		}
		addnav("Notify done,close","runmodule.php?module=petitionfixnavs&op=ncnext&id=$id&nextid=0");
		if (($session['user']['superuser'] & SU_EDIT_USERS)==SU_EDIT_USERS) {
			$sql="SELECT author,petitionid FROM ".db_prefix('petitions')." WHERE petitionid='$id';";
			$result=db_query($sql);
			$row=db_fetch_assoc($result);
			addnav("User Ops");
			addnav("Display Debug Log","user.php?op=debuglog&userid=".$row['author']."&returnpetition=".$row['petitionid']);
		}
		break;
	}
	return $args;
}

function petitionfixnavs_run(){
	global $session;
	page_header("Fixed Navs");
	$id=httpget('id');
	$op=httpget('op');
	switch ($op) {
		case "cnext":
			$nextid=httpget('nextid');
			$sql="UPDATE ".db_prefix("petitions")." SET status=2,closeuserid='{$session['user']['acctid']}',closedate='".date("Y-m-d H:i:s")."' WHERE petitionid='$id'";
			db_query($sql);
			pt_insert(translate_inline("/me has closed the petition"));
			invalidatedatacache("petition_counts");			
			redirect("viewpetition.php?op=view&id=$nextid");
			break;
		case "ncnext":
			$sql="SELECT author FROM ".db_prefix("petitions")." WHERE petitionid='$id'";
			$result=db_query($sql);
			$author=db_fetch_assoc($result);
			debug($author);
			if (!$author) {
				output_notl("Error! Please give a detailed error report to the Petition Fixnavs Module Author!");
				page_footer();
				return;
			}
			$author=$author['author'];
			$nextid=(int)httpget('nextid');
			$sql="UPDATE ".db_prefix("petitions")." SET status=2,closeuserid='{$session['user']['acctid']}',closedate='".date("Y-m-d H:i:s")."' WHERE petitionid='$id'";
			pt_insert(translate_inline("/me has done the work and closed the petition"));
			db_query($sql);
			require_once("lib/systemmail.php");
			systemmail($author,"Your petition",sprintf_translate("Your request has been processed on and is now done. If not, please petition again. (This is an automatic message).`n`nRegards %s",$session['user']['name']));			
			invalidatedatacache("petition_counts");
			if ($nextid!=0) redirect("viewpetition.php?op=view&id=$nextid");
				else redirect("viewpetition.php");
			break;
		case "cfnext":
			$sql="SELECT author FROM ".db_prefix("petitions")." WHERE petitionid='$id'";
			$result=db_query($sql);
			$author=db_fetch_assoc($result);
			debug($author);
			if (!$author) {
				output_notl("Error! Please give a detailed error report to the Petition Fixnavs Module Author!");
				page_footer();
				return;
			}
			$author=$author['author'];
			$sql="UPDATE ".db_prefix("accounts")." SET allowednavs='',specialinc='' WHERE acctid=$author";
			$result=db_query($sql);
			$sql="DELETE FROM ".db_prefix("accounts_output")." WHERE acctid=$author";
			$result=db_query($sql);		
			$nextid=httpget('nextid');
			$sql="UPDATE ".db_prefix("petitions")." SET status=2,closeuserid='{$session['user']['acctid']}',closedate='".date("Y-m-d H:i:s")."' WHERE petitionid='$id'";
			pt_insert(translate_inline("/me has fixed navs and closed the petition"));
			db_query($sql);
			require_once("lib/systemmail.php");
			systemmail($author,array("Your petition"),array("Your navs have been fixed, you should be able to navigate from the stuck page now. If not, please petition again. (This is an automatic message).`n`nRegards %s",$session['user']['name']));			
			invalidatedatacache("petition_counts");
			redirect("viewpetition.php?op=view&id=$nextid");
			break;			
		default:
			$sql="SELECT author FROM ".db_prefix("petitions")." WHERE petitionid='$id'";
			$result=db_query($sql);
			$author=db_fetch_assoc($result);
			debug($author);
			if (!$author) {
				output_notl("Error! Could not find that user!!");
				page_footer();
				return;
			}
			$author=$author['author'];
			$sql="UPDATE ".db_prefix("accounts")." SET allowednavs='',specialinc='' WHERE acctid=$author";
			$result=db_query($sql);
			$sql="DELETE FROM ".db_prefix("accounts_output")." WHERE acctid=$author";
			$result=db_query($sql);
			pt_insert(translate_inline("/me has fixed this users navs"));
			require_once("lib/systemmail.php");
			systemmail($author,array("Your petition"),array("Your navs have been fixed, you should be able to navigate from the stuck page now. If not, please petition again. (This is an automatic message).`n`nRegards %s",$session['user']['name']));
			redirect("viewpetition.php?op=view&id=$id");
			break;
			
	}
	page_footer();
}

function pt_insert($text) {
	$id=httpget('id');
	require_once("lib/commentary.php");
	injectcommentary("pet-$id","",$text);
	return;
}
?>
