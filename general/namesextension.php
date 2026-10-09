<?php
use Lotgd\Security\Csrf;

/*
Module to be used for the prefs.
*/
function namesextension_getmoduleinfo(){
$info = array(
	"name"=>"Namesextension",
	"version"=>"1.0",
	"author"=>"`2Oliver Brendel",
	"category"=>"Names",
	"download"=>"",
	"prefs"=>array(
		"Namesextension Preferences,title",
		"name"=>"The Name of the Player,text|",
		),
	);
	return $info;
}

function namesextension_install(){
	module_addhook("superuser");
	return true;
}

function namesextension_uninstall(){
	return true;
}

function namesextension_dohook($hookname, $args){
	global $session;
	switch ($hookname) {
		default:
		if ((SU_MEGAUSER & $session['user']['superuser'])!==SU_MEGAUSER) break;
		addnav("Conversions");
		addnav("Name Conversion","runmodule.php?module=namesextension&op=convert");
		break;
	}
	return $args;
}

/**
 * Whether the request is a posted name conversion with a valid token.
 * Lotgd\Security\Csrf came with the core of September 2026; an older core gets the POST check alone.
 */
function namesextension_validpost(){
	if (!class_exists(Csrf::class)) {
		return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
	}
	return Csrf::validatePostRequest("module:namesextension");
}

/**
 * The hidden token field for this module's forms; empty on a core without Lotgd\Security\Csrf.
 */
function namesextension_tokenfield(){
	return class_exists(Csrf::class) ? Csrf::hiddenField("module:namesextension") : "";
}

/**
 * An inline POST button carrying the module's CSRF token.
 */
function namesextension_postbutton($url, $label, $confirm = ""){
	addnav("", $url);
	$onsubmit = $confirm > "" ? " onSubmit='return confirm(".htmlspecialchars(json_encode($confirm), ENT_QUOTES).");'" : "";
	return "<form action='".htmlspecialchars($url, ENT_QUOTES)."' method='post' style='display:inline'$onsubmit>"
		.namesextension_tokenfield()
		."<input type='submit' class='button' value='".htmlspecialchars($label, ENT_QUOTES)."'></form>";
}

function namesextension_run(){
global $session;
	// The Grotto link is only offered to megausers; enforce the same here.
	check_su_access(SU_MEGAUSER);
	page_header("Conversion");
	addnav("Back to the grotto","superuser.php");
	$op=httpget('op');
	// The nav only asks; the posted button with the token runs the conversion over all accounts.
	if ($op=="convert" && !namesextension_validpost()) {
		rawoutput(namesextension_postbutton("runmodule.php?module=namesextension&op=convert", translate_inline("Name Conversion")));
		$op="";
	}
	switch($op) {
		case "convert":
			mb_internal_encoding(getsetting("charset", "ISO-8859-1"));
			$sql="SELECT name,ctitle,title,acctid FROM ".db_prefix('accounts');
			$result=db_query($sql);
			while ($row=db_fetch_assoc($result)) {
				$basename=nm_get_player_basename($row['name'],$row['ctitle'],$row['title']);
				set_module_pref("name",$basename,"namesextension",$row['acctid']);
				output("%s`\$ processed to name \"%s`\$\" (%s Acctid).`n",$row['name'],$basename,$row['acctid']);
			}
		break;
	
	}
	page_footer();


}

function nm_get_player_basename($name,$ctitle,$title) {
	$title = nm_get_player_title($name,$ctitle,$title);
	if ($title) {
		$x = mb_strpos($name, $title);
		if ($x !== false)
			$name = trim(mb_substr($name,$x+mb_strlen($title)));
	}

	return str_replace("`0", "", $name);
}

function nm_get_player_title($name,$ctitle,$title) {
	$rtitle = $title;
	if ($ctitle) $title = $ctitle;
	return $title;
}
?>