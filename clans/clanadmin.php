<?php
use Lotgd\Security\Csrf;

function clanadmin_getmoduleinfo(){
	$info = array(
		"name"=>"Clan Admin Functions",
		"version"=>"1.0",
		"author"=>"`2Oliver Brendel",
		"category"=>"Clan",
		"download"=>"",
	);
	return $info;
}

function clanadmin_install(){
	module_addhook("header-clan");
	module_addhook("clanhall");	
	return true;
}

function clanadmin_uninstall(){
	return true;
}

function clanadmin_dohook($hookname,$args){
	global $session;
	$op=httpget('op');
	switch ($hookname) {
		case "header-clan":
			if ($op!='apply') break;
			$closed=(int)get_module_objpref('clanadmin',(int)httpget('to'),'clanclosed');
			if ($closed) {
				page_header("Clans");
				output("`\$Sorry, this clan does not accept applications.");
				addnav("Back to the clan pages","clan.php");
				page_footer();
			}
			break;		
		case "clanhall":
			if ($session['user']['clanrank']>=CLAN_LEADER && $session['user']['clanid']!=0) {
				addnav("Clan Administrative");
				if (get_module_objpref('clanadmin',$session['user']['clanid'],'clanclosed')==1)
					addnav("Open Clan for Applications","runmodule.php?module=clanadmin&op=openapps");
					else
					addnav("Close Clan for Applications","runmodule.php?module=clanadmin&op=closeapps");
			}
			break;
	}
	return $args;
}

/**
 * Whether the request is a posted application toggle with a valid token.
 * Lotgd\Security\Csrf came with the core of September 2026; an older core gets the POST check alone.
 */
function clanadmin_validpost(){
	if (!class_exists(Csrf::class)) {
		return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
	}
	return Csrf::validatePostRequest("module:clanadmin");
}

/**
 * The hidden token field for this module's forms; empty on a core without Lotgd\Security\Csrf.
 */
function clanadmin_tokenfield(){
	return class_exists(Csrf::class) ? Csrf::hiddenField("module:clanadmin") : "";
}

/**
 * An inline POST button carrying the module's CSRF token.
 */
function clanadmin_postbutton($url, $label, $confirm = ""){
	addnav("", $url);
	$onsubmit = $confirm > "" ? " onSubmit='return confirm(".htmlspecialchars(json_encode($confirm), ENT_QUOTES).");'" : "";
	return "<form action='".htmlspecialchars($url, ENT_QUOTES)."' method='post' style='display:inline'$onsubmit>"
		.clanadmin_tokenfield()
		."<input type='submit' class='button' value='".htmlspecialchars($label, ENT_QUOTES)."'></form>";
}

function clanadmin_run(){
	global $session;
	// Same condition as the clan hall links.
	if ($session['user']['clanrank']<CLAN_LEADER || $session['user']['clanid']==0) {
		redirect("clan.php");
	}
	$op=httpget('op');
	page_header("Clan Administratives");
	addnav("Navigation");
	addnav("Back to the Clanhall","clan.php");
	// The clan hall navs only ask; the posted button with the token changes the setting.
	if (($op=="closeapps" || $op=="openapps") && !clanadmin_validpost()) {
		$label = translate_inline($op=="closeapps" ? "Close Clan for Applications" : "Open Clan for Applications");
		rawoutput(clanadmin_postbutton("runmodule.php?module=clanadmin&op=$op", $label));
		$op = "";
	}
	switch ($op) {
		case "closeapps":
			output("`\$The Clan is now closed for applications. Applicants will not be allowed.");
			set_module_objpref('clanadmin',$session['user']['clanid'],'clanclosed',1);
			break;
			
		case "openapps":
			output("`\$The Clan is now open for applications.");
			set_module_objpref('clanadmin',$session['user']['clanid'],'clanclosed',0);
			break;
	
	}
	//set_module_objpref("clans",$session['user']['clanid'],"filename",$url);
	//not sure why dumbfuck me has this line above in the code for this - copy+paste error I guess
	page_footer();
}

?>
