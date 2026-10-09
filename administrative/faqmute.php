<?php
use Lotgd\Security\Csrf;
// translator ready
// addnews ready
// mail ready

function faqmute_getmoduleinfo(){
	$info = array(
		"name"=>"Newbie Mute",
		"version"=>"1.1",
		"author"=>"Booger",
		"category"=>"Administrative",
		"download"=>"core_module",
		"prefs"=>array(
			"Newbie Mute User Prefs, title",
			"seenfaq"=>"Has the player seen the FAQ,bool|0",
		)
	);
	return $info;
}

function faqmute_install(){
	module_addhook("insertcomment");
	module_addhook("faq-posttoc");
	module_addhook("bioinfo");
	return true;
}

function faqmute_uninstall(){
	return true;
}

/**
 * Whether the request is a posted FAQ mute reset with a valid token.
 * Lotgd\Security\Csrf came with the core of September 2026; an older core gets the POST check alone.
 */
function faqmute_validpost(){
	if (!class_exists(Csrf::class)) {
		return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
	}
	return Csrf::validatePostRequest("module:faqmute");
}

/**
 * An inline POST button carrying the module's CSRF token.
 */
function faqmute_postbutton($url, $label){
	addnav("", $url);
	return "<form action='".htmlspecialchars($url, ENT_QUOTES)."' method='post' style='display:inline'>"
		.(class_exists(Csrf::class) ? Csrf::hiddenField("module:faqmute") : "")
		."<input type='submit' class='button' value='".htmlspecialchars($label, ENT_QUOTES)."'></form>";
}

function faqmute_dohook($hookname,$args){
	global $session;
	$seen=get_module_pref("seenfaq");
	switch ($hookname) {
	case "insertcomment":
		if (!$seen && !$session['user']['dragonkills']) {
			$args['mute']=1;
			$mutemsg="`n`\$You have to read the FAQ before you can post comments. You can find it in any town.`0`n`n";
			$mutemsg=translate_inline($mutemsg);
			$args['mutemsg']=$mutemsg;
		}
		break;
	case "faq-posttoc":
		if (!$seen) set_module_pref("seenfaq",true);
		break;
	case "bioinfo":
		$id = $args['acctid'];
		$seen=get_module_pref("seenfaq", "faqmute",$id);
		if (httpget("op")=="faqmute" && ($session['user']['superuser'] & SU_EDIT_COMMENTS)){
			// The nav only asks; the posted button with the token resets the status.
			if (faqmute_validpost()) {
				set_module_pref("seenfaq",false, "faqmute",$id);
				output("`nPlayer's FAQ seen status reset.`n");
			} else {
				output_notl("`n");
				rawoutput(faqmute_postbutton("bio.php?char=".rawurlencode($args['login'])."&ret=".rawurlencode(httpget("ret"))."&op=faqmute", translate_inline("FAQmute player")));
				output_notl("`n");
			}
		} elseif (($session['user']['superuser'] & SU_EDIT_COMMENTS) &&
				$seen && !$args['dragonkills']) {
			addnav("Mute Player Options");
			addnav("FAQmute player","bio.php?char=".rawurlencode($args['login'])."&ret=".rawurlencode(httpget("ret"))."&op=faqmute");
		}
		break;
	}
	return $args;
}

function faqmute_run(){
}
?>
