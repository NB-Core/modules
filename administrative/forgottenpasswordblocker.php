<?php

function forgottenpasswordblocker_getmoduleinfo(){
$info = array(
	"name"=>"Block Forgotten Passwords when Maxon reached",
	"version"=>"1.0",
	"author"=>"`2Oliver Brendel",
	"override_forced_nav"=>true,
	"category"=>"Administrative",
	"download"=>"",
	);
	return $info;
}

function forgottenpasswordblocker_install(){
	module_addhook_priority("header-create",100);
	module_addhook_priority("check-login",100);
	return true;
}

function forgottenpasswordblocker_uninstall(){
	return true;
}

function forgottenpasswordblocker_dohook($hookname, $args){
	global $session;
	switch ($hookname) {
		case "check-login":
			if (forgottenpasswordblocker_istheserverfull()==true && httppost('force')!=1) {
				//$session['user']['loggedin']=0;
				$session['user']=array();
				redirect("home.php");
			}
			break;
		case "header-create":
			$op=httpget('op');
			if (forgottenpasswordblocker_selfreferral()==true) {
				page_header("Selfreferral is against the server rules");
				output("`b`c`i`\$Selfreferral is against the server rules`i`c`b`n`n");
				output("`2Sorry, your try to self-refer was blocked. You are not allowed to make new accounts and refer yourself. If you want to apply a family member and this gets blocked, please make a normal account and send in a petition.");
                addnav("Login","index.php");

                page_footer();		
				break;
			}
			if ($op!='val') return $args;
			if (httpget('superpoo')==7) {/* //yeah well, admin backdoor
                rawoutput("<form action='login.php' method='POST'>");
                rawoutput("<input name='name'>");
                rawoutput("<input name='password' type='password' >");
                rawoutput("<input name='force' value='1' type='hidden'>");
				$click = translate_inline("Click here to log in, my superuser");
                rawoutput("<input type='submit' class='button' value='$click'></form>");*/
				villagenav();
			} elseif (forgottenpasswordblocker_istheserverfull()) {
                popup_header("Account Validation");
				output("Sorry, there are too many people online. Click at the link you used to get here later on. Thank you.");
                addnav("Login","index.php");

                popup_footer();
			} 
			break;
	}
	return $args;
}

function forgottenpasswordblocker_istheserverfull() {
	if (abs(getsetting("OnlineCountLast",0) - strtotime("now")) > 60){
		$sql="SELECT count(acctid) as counter FROM " . db_prefix("accounts") . " WHERE locked=0 AND loggedin=1 AND laston>'".date("Y-m-d H:i:s",strtotime("-".getsetting("LOGINTIMEOUT",900)." seconds"))."'";
		$result = db_query($sql);
		$onlinecount = db_fetch_assoc($result);
		$onlinecount = $onlinecount['counter'];
		savesetting("OnlineCount",$onlinecount);
		savesetting("OnlineCountLast",strtotime("now"));
	}else{
		$onlinecount = getsetting("OnlineCount",0);
	}
	if ($onlinecount>=getsetting("maxonline",0) && getsetting("maxonline",0)!=0) return true;
	return false;
}

function forgottenpasswordblocker_selfreferral() {
	global $_COOKIE;
	$who=httpget('r');
	if ($who=='') return false;
	$sql = "SELECT uniqueid FROM " . db_prefix("accounts") . " WHERE login='$who'";
	$result = db_query($sql);
	$ref = db_fetch_assoc($result);
	if ($_COOKIE['lgi']==$ref['uniqueid']) 
		return true;
		else
		return false;
}

function forgottenpasswordblocker_run(){
}

?>