<?php
use Lotgd\Security\Csrf;

/**
 * Whether the request is a posted favorite change with a valid token.
 * Lotgd\Security\Csrf came with the core of September 2026; an older core gets the POST check alone.
 */
function recentsql_validpost(){
	if (!class_exists(Csrf::class)) {
		return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
	}
	return Csrf::validatePostRequest("module:recentsql");
}

/**
 * The hidden token field for this module's forms; empty on a core without Lotgd\Security\Csrf.
 */
function recentsql_tokenfield(){
	return class_exists(Csrf::class) ? Csrf::hiddenField("module:recentsql") : "";
}

function recentsql_getmoduleinfo(){
	$info = array(
		"name"=>"Recent SQL/PHP history",
		"author"=>"Eric Stevens",
		"version"=>"1.0",
		"category"=>"Administrative",
		"download"=>"core_module",
		"settings"=>array(
			"max_history"=>"Max number of items to keep in recent history,int|10",
		),
		"prefs"=>array(
			"recentSQL"=>"Recent SQL,viewonly|",
			"recentPHP"=>"Recent PHP,viewonly|",
			"favoriteSQL"=>"Favorite SQL,viewonly|",
			"favirotePHP"=>"Favorite PHP,viewonly|",
		),
	);
	return $info;
}

function recentsql_install(){
	module_addhook("rawsql-execsql");
	module_addhook("rawsql-execphp");
	module_addhook("footer-rawsql");
	module_addhook("rawsql-modphp");
	module_addhook("rawsql-modsql");
	return true;
}

function recentsql_uninstall(){
	return true;
}

function recentsql_dohook($hookname,$args){
	$op = httpget("op");
	if ($op=="") $op="sql";
	$type = "recent".strtoupper($op);

	switch($hookname){
	case "rawsql-execsql":
	case "rawsql-execphp":
		$recent = unserialize(get_module_pref($type));
		if (!is_array($recent)) $recent = array();
		array_push($recent,$args[$op]);
		$recent = array_unique($recent); //remove duplicates while keeping the most recently executed one highest on the list
		while (count($recent) > get_module_setting("max_history")){
			array_shift($recent);
		}
		set_module_pref($type,serialize($recent));
		break;
	case "footer-rawsql":
		$recent = unserialize(get_module_pref($type));
		if (!is_array($recent)) $recent = array();
		$submit = translate_inline("Load");
		rawoutput("<form action='rawsql.php?op=$op' method='post'>");
		addnav("","rawsql.php?op=$op");
		output("Recent %s:",$op);
		rawoutput("<select name='recentID'>");
		$output = "";
		foreach($recent as $key=>$val){
			$output = "<option value='$key'>".
			          htmlentities(substr($val,0,70), ENT_COMPAT, getsetting("charset", "ISO-8859-1")).
			          "</option>".
			          $output;
		}
		rawoutput("$output</select><input type='submit' class='button' value='$submit'>");
		rawoutput("</form>");

		//form to save favorites
		rawoutput("<form action='rawsql.php?op=$op&subop=addFavorite' method='POST'>".recentsql_tokenfield());
		addnav("","rawsql.php?op=$op&subop=addFavorite");
		rawoutput("<input type='radio' name='action' value='add' checked>");
		output("Add the most recently executed statement to a favorite`n");
		rawoutput("<input type='radio' name='action' value='remove'>");
		output("Remove the favorite`n");
		output("named: ");
		rawoutput("<input name='favoritename'>");
		$submit = translate_inline("Save");
		rawoutput("<input type='submit' class='button' value='$submit'>");
		rawoutput("</form>");

		//store saved favorites if found.
		// Favorites change only from the posted form with its token.
		$fav = httppost("favoritename");
		if ($fav > "" && recentsql_validpost()){
			$favorites = unserialize(get_module_pref("favorite".strtoupper($op)));
			if (!is_array($favorites)) $favorites = array();
			$recent = unserialize(get_module_pref($type));
			if (!is_array($recent)) $recent = array();
			$statement = array_pop($recent);
			$action = httppost("action");
			if ($action == "remove"){
				if (array_key_exists($fav,$favorites)){
					unset($favorites[$fav]);
				}
			}else{
				$favorites[$fav] = $statement;
			}
			set_module_pref("favorite".strtoupper($op),serialize($favorites));
		}

		//add favorites
		$types = array("sql","php");
		foreach($types as $op){
			addnav(strtoupper($op)." Favorites");
			$favorites = @unserialize(get_module_pref("favorite".strtoupper($op)));
			if (!is_array($favorites)) $favorites = array();
			foreach($favorites as $key=>$val){
				addnav($key,"rawsql.php?op=$op&favorite=".rawurlencode($key));
			}
		}

		break;
	case "rawsql-modphp":
	case "rawsql-modsql":
		$id = httppost("recentID");
		if ($id > ""){
			$recent = unserialize(get_module_pref($type));
			if (!is_array($recent)) $recent = array();
			if (array_key_exists($id,$recent)){
				$args[$op] = $recent[$id];
			}
		}

		$fav = httpget("favorite");
		if ($fav > ""){
			$favorites = unserialize(get_module_pref("favorite".strtoupper($op)));
			if (array_key_exists($fav,$favorites)){
				$args[$op] = $favorites[$fav];
			}
		}
		break;
	}
	return $args;
}
?>
