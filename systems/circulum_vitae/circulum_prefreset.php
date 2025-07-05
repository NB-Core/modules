<?php


function circulum_prefreset_getmoduleinfo(){
	$info = array(
	    "name"=>"Circulum Pref Reset",
		"version"=>"1.0",
		"author"=>"`4Oliver Brendel`0",
		"category"=>"Circulum Vitae",
		"download"=>"",
		"settings"=>array(
			"Circulum Vitae - Preferences,title",
				"Note: Separate via comma and use the filename of the module - like lover or ella or dag,note",
				"reset"=>"Which module preferences should be deleted when somebody does a CV?,text|",
                                "inventoryreset"=>"Reset (if installed!) the inventory of a user who walks the fame?,bool|1",
			),		
		"requires"=>array(
			"circulum"=>"1.0|Circulum Vitae by `2Oliver Brendel",
			),
		);
    return $info;
}

function circulum_prefreset_install() {
	module_addhook("circulum-moduleprefs");
	return true;
}

function circulum_prefreset_uninstall() {
  return true;
}


function circulum_prefreset_dohook($hookname, $args) {
	global $session;
	switch($hookname) {
		case "circulum-moduleprefs":
			$set=get_module_setting('reset');
			if ($set=='') break;
			$settings=explode(",",$set);
			foreach ($settings as $modulename) {
				$args[]=$modulename;
			}
			if (get_module_setting('inventoryreset')) {
				$sql="DELETE FROM ".db_prefix("inventory")." WHERE userid=".$session['user']['acctid'].";";
				db_query($sql);			
			}
			if (is_module_active("prizemount")) {
				$sql = "DELETE FROM " . db_prefix("module_userprefs") . " WHERE modulename='prizemount' AND setting='oldmount' AND userid='{$session['user']['acctid']}'";
				db_query($sql);
				//clear the old mount
			}
			break;
	}
	return $args;
}

function circulum_prefreset_run()	{

}

?>
