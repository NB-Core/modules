<?php
/*
 * Visa Login
 * Anybody with a visa (preference) can enter the site always
 * (even when full, not when login blocked by serversuspend etc)
 * You can use this together with a lodge module to buy access to your site
 * or/and can code a maxuser setting for the visa-owner too, using the hook
 * visalogin-visaused ...which gets called if the user can enter the page
 * (i.e. you can deduct logins there or count anew)
*/

function visalogin_getmoduleinfo(){
	$info = array(
		"name"=>"Visa Login (formerly Superuserlogin)",
		"version"=>"1.1",
		"author"=>"Catscradler, `2modified by Oliver Brendel",
		"category"=>"Administrative",
		"download"=>"http://dragonprime.net",
		"allowanonymous"=>true,
		"settings"=> array(
			"superuser"=>"All superuser do not need a visa to enter via the extra entrance,bool|1",
			),
		"prefs"=> array (
			"hasvisa"=>"Has this user a visa?,bool|0",
		),
	);
	return $info;
}

function visalogin_install(){
	module_addhook("header-home");
	module_addhook("check-login");
	return true;
}

function visalogin_uninstall(){
	return true;
}

function visalogin_dohook($hookname, $args){
	switch($hookname){

		case "header-home":
			addnav("Staff Entry");
			addnav("Entry for staff members","runmodule.php?module=visalogin");
			break;

		case "check-login":
			if (httppostisset("visalogin")){
				global $session;
				$acctid = (int) ($session['user']['acctid'] ?? 0);
					$sql="SELECT value FROM ".db_prefix('module_userprefs')." WHERE modulename='visalogin' AND setting='hasvisa' AND userid=" . $acctid . ";";
					$result=db_query($sql);
				if (db_num_rows($result)>0) {
					$row=db_fetch_assoc($result);
					$hasvisa=$row['value'];
				} else $hasvisa=0;
				$sql="SELECT value FROM ".db_prefix('module_settings')." WHERE modulename='visalogin' AND setting='superuser';";
				$result=db_query($sql);
				if (db_num_rows($result)>0) {
					$row=db_fetch_assoc($result);
					$superuser=$row['value'];
				} else $superuser=0;
				if ($hasvisa==0 && !($superuser && ($session['user']['superuser']>0 && $session['user']['superuser']!=SU_GIVE_GROTTO))){  //check for proper permissions, grotto-only user can't login
					if (!isset($session['message'])) $session['message']='';
					$session['message'].=translate_inline("`4You do not have a visa. You must get one before you can sign on.`0`n"); //send naughty regular users back to their login page
					$session['user']=array();
					require_once("lib/redirect.php");
					redirect("index.php");
				}
			}
			//modulehook("visalogin-visaused",array("user"=>$session['user']['acctid'],"superuser"=>$session['user']['superuser']));
			break;
	}
	return $args;
}

function visalogin_run(){
	page_header("Visa Owner Login");

	output("`c`b`\$Visa Owner Login`b`n");
	addnav("Back to index","index.php");
	//This is just a partial copy of the core login form with two extra elements.
	// The password is posted as typed, like the core form: login.php no longer accepts "!md5!" digests.
	$uname = translate_inline("<u>U</u>sername");
	$pass = translate_inline("<u>P</u>assword");
	$butt = translate_inline("Log in");
	rawoutput("<form action='login.php' method='POST'>".templatereplace("login",array("username"=>$uname,"password"=>$pass,"button"=>$butt))."<input type=\"hidden\" name=\"visalogin\" value=\"visalogin\"/>");
	rawoutput("<input name='force' value='1' type='hidden'> </form>");	//needed if you have forgottenpasswordblocker module installed
	// Render reCAPTCHA token into the visa login form.
	// The index-login hook injects enterprise.js + hidden token field;
	// its JavaScript relocates the input into form[action*='login'].
	modulehook("index-login", array());
	output_notl("`c");
	page_footer();
}

?>
