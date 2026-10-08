<?php


function weaponnavs_getmoduleinfo(){
    $info = array(
        "name"=>"Weapon/Armour Navs (For Translators)",
        "version"=>"1.0",
        "author"=>"`2Oliver Brendel",
        "category"=>"General",
        "download"=>"",
      
    );
    return $info;
}

function weaponnavs_install(){
	module_addhook("footer-weapons");
	module_addhook("footer-armor");
	module_addhook_priority("modify-weapon", INT_MAX);	// Last to execute
	module_addhook_priority("modify-armor", INT_MAX);
    return true;
}

function weaponnavs_uninstall(){
    return true;
}

function weaponnavs_dohook($hookname,$args){
    global $session;
	static $get;
	static $done;
	$link='';
	if (!isset($get)) $get=httpget('op');
    switch($hookname){
		case "modify-weapon":
			if (!$done) {
				blocknav("village.php",false);
				addnav(array("Back to %s",$session['user']['location']),"village.php?back=1");
			$done=true;
			}
			addnav("Buy...");
			if (isset($args['skip']) && $args['skip']==true || $get!='') break;
			if (isset($args['unavailable']) && $args['unavailable']==true) break;
				else $link="weapons.php?op=buy&id={$args['weaponid']}";
			addnav(array(" ?`g%s `0(`^%s gold`0)",$args['weaponname'],$args['value']),$link);
			
		break;
		case "modify-armor":
			if (!$done) {
				blocknav("village.php",false);
				addnav(array("Back to %s",$session['user']['location']),"village.php?back=1");
				$done=true;
			}
			addnav("Buy...");
			if (isset($args['skip']) && $args['skip']==true || $get!='') break;
			if (isset($args['unavailable']) && $args['unavailable']==true) break;
				else $link="armor.php?op=buy&id={$args['armorid']}";
			addnav(array(" ?`g%s `0(`^%s gold`0)",$args['armorname'],$args['value']),$link);		
		break;
		}
    return $args;
}

function weaponnavs_run () {

}
?>
