<?php
use Lotgd\Security\Csrf;

function perws_getmoduleinfo(){
	$info = array(
		"name"=>"Bio: Personal Website",
		"author"=>"Chris Vorndran",
		"category"=>"General",
		"version"=>"1.01",
		"download"=>"http://dragonprime.net/users/Sichae/perws.zip",
		"vertxtloc"=>"http://dragonprime.net/users/Sichae/",
		"description"=>"This module will allow users to display a Personal Website in their bio. These URLs can be moderated and banned via the Grotto. Make sure to set the pref of the user that wishes to Moderate the URLs.",
		"settings"=>array(
			"limit"=>"Maximum chars on this,int|50",
			),
		"prefs"=>array(
			"Personal Website,title",
			"user_name"=>"What is the name of your website?,text|",
			"user_link"=>"What is the URL of your address?,text|",
			"user_note"=>"Make sure to append the `^http://`0 to the front. Thank you.,note",
			"ban"=>"Has user's URL been banned?,bool|0",
			"access"=>"Does this user have access to the Banning URL page?,bool|0",
		),
	);
	return $info;
}
function perws_install(){
	module_addhook("superuser");
	module_addhook("biostat");
	module_addhook("charrestore_nosavemodules");
	return true;
}
function perws_uninstall(){
	return true;
}
function perws_dohook($hookname,$args){
	global $session,$target;
	$length=get_module_setting('limit');
	switch ($hookname){
		case "charrestore_nosavemodules":
			$args['perws']=true; //don't let charrestore save this, it's personal info
			break;
		case "superuser":
			if (get_module_pref("access")){
				addnav("Editors");
				addnav("Moderate URLs","runmodule.php?module=perws&op=list");
			}
			break;
		case "biostat":
			if (get_module_pref("user_link","perws",$target['acctid']) <> "" && !get_module_pref("ban","perws",$target['acctid'])){
				$set=get_module_pref("user_link","perws",$target['acctid']);
				if (stripos($set,"script")!==false) break;
				$name=stripslashes(get_module_pref("user_name","perws",$target['acctid']));
				$name=substr($name,0,$length);
				if (strpos($name,"script")!==false) break;
				$scheme=strtolower((string)parse_url($set, PHP_URL_SCHEME));
				if (filter_var($set, FILTER_VALIDATE_URL)===false || ($scheme!="http" && $scheme!="https")) {
					// url invalid
					$set="#";
					$name="(invalid!)";
				}
				output("`^Personal Website: `@<a href='%s' target='_blank' rel='noopener noreferrer'>",htmlspecialchars($set,ENT_QUOTES),true);
				$name=str_replace("`c","",$name);
				$name=str_replace("`b","",$name);
				$name=str_replace("`i","",$name);
				$name=str_replace("`n","",$name);
				output_notl("%s",$name);
				output_notl("</a>`0`n",$name,true);
			}
			break;
		}
	return $args;
}
/**
 * Whether the request is a posted URL ban change with a valid token.
 * Lotgd\Security\Csrf came with the core of September 2026; an older core gets the POST check alone.
 */
function perws_validpost(){
	if (!class_exists(Csrf::class)) {
		return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
	}
	return Csrf::validatePostRequest("module:perws");
}

/**
 * The hidden token field for this module's forms; empty on a core without Lotgd\Security\Csrf.
 */
function perws_tokenfield(){
	return class_exists(Csrf::class) ? Csrf::hiddenField("module:perws") : "";
}

/**
 * An inline POST button carrying the module's CSRF token.
 */
function perws_postbutton($url, $label, $confirm = ""){
	addnav("", $url);
	$onsubmit = $confirm > "" ? " onSubmit='return confirm(".htmlspecialchars(json_encode($confirm), ENT_QUOTES).");'" : "";
	return "<form action='".htmlspecialchars($url, ENT_QUOTES)."' method='post' style='display:inline'$onsubmit>"
		.perws_tokenfield()
		."<input type='submit' class='button' value='".htmlspecialchars($label, ENT_QUOTES)."'></form>";
}

function perws_run(){
	global $sesion;
	// The Grotto link is only offered with the access pref; enforce the same here.
	if (!get_module_pref("access")) redirect("superuser.php");
	$op = httpget('op');
	// Bans change only from the posted buttons with the token.
	$id = perws_validpost() ? (int)httpget('id') : 0;
	$ban = (int)httpget('ban') ? 1 : 0;
	$sub = translate_inline("Your URL has been banned.");
	$body = translate_inline("We are sorry, but due to certain reasons, your personal URL has been banned. Please take this up with your local admin. There, you may discuss why your URL was banned, and see for a means of fixing this all up. Thank you.");
	page_header("Moderate URLs");
	switch ($op){
		case "list":
			if ($id > 0){
				set_module_pref("ban",$ban,"perws",$id);
				output("`cUser's URL has been `^%s`0.`c",translate_inline($ban?"banned":"unbanned"));
				require_once("lib/systemmail.php");
				if ($ban) systemmail($id,$sub,$body);
			}
			$sql = "SELECT name, a.value AS link, b.value AS sitename, c.value AS ban, acctid FROM ".db_prefix("accounts")." INNER JOIN ".db_prefix("module_userprefs")." AS a, ".db_prefix("module_userprefs")." AS b, ".db_prefix("module_userprefs")." AS c ON acctid=a.userid ANd acctid=b.userid AND acctid=c.userid WHERE a.modulename='perws' AND b.modulename='perws' AND c.modulename='perws' AND a.setting='user_link' AND b.setting='user_name' AND c.setting='ban' AND a.value <> '' AND b.value <>''";
			$res = db_query($sql);
			$ops = translate_inline("Ops");
			$name = translate_inline("Name");
			$url = translate_inline("URL");
			$bsh = translate_inline("Banned");
			rawoutput("<br><table border='0' cellpadding='2' cellspacing='1' align='center' bgcolor='#999999'>");
			rawoutput("<tr class='trhead'><td>$ops</td><td>$name</td><td>$bsh</td><td>$url</td></tr>");
			for ($i = 0; $i < db_num_rows($res); $i++){
				$row = db_fetch_assoc($res);
				rawoutput("<tr class='".($i%2?"trdark":"trlight")."'><td>");
				if ($row['ban'] == 0){
					rawoutput(perws_postbutton("runmodule.php?module=perws&op=list&id=".(int)$row['acctid']."&ban=1", translate_inline("Ban")));
				}else{
					rawoutput(perws_postbutton("runmodule.php?module=perws&op=list&id=".(int)$row['acctid']."&ban=0", translate_inline("Un-Ban")));
				}					
				rawoutput("</td><td>");
				output_notl("`@%s",$row['name']);
				rawoutput("</td><td>");
				output("`c%s`c`0",translate_inline($row['ban']==1?"`@Yes":"`#No"));
				rawoutput("</td><td>");
				// Stored by players: escape both, and only link http/https like the bio does.
				$link=$row['link'];
				$sitename=htmlspecialchars(stripslashes($row['sitename']),ENT_QUOTES);
				$scheme=strtolower((string)parse_url($link, PHP_URL_SCHEME));
				if (filter_var($link, FILTER_VALIDATE_URL)===false || ($scheme!="http" && $scheme!="https")) {
					rawoutput($sitename." (".htmlspecialchars($link,ENT_QUOTES).") ".translate_inline("(invalid!)"));
				} else {
					rawoutput("<a href='".htmlspecialchars($link,ENT_QUOTES)."' target='_blank' rel='noopener noreferrer'>".$sitename."</a>");
				}
				rawoutput("</td></tr>");
				}
			rawoutput("</table>");
			addnav("Refresh List","runmodule.php?module=perws&op=list");
			addnav("Return to the Grotto","superuser.php");
			break;
		}
	page_footer();
}
?>	
