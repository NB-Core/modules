<?php
// translator ready
// addnews ready
// mail ready

require_once("lib/http.php");

function scavenge_getmoduleinfo(){
    $info = array(
        "name"=>"Scavenger",
        "version"=>"1.25",
        "author"=>"Copied and Pasted by Sneakabout",
        "category"=>"Graveyard Specials",
        "download"=>"core_module",
        "settings"=>array(
            "Scavenger - Settings,title",
            "villagechance"=>"Raw Chance of Wraith appearing in the Village?,range,0,100,5|20",
            "gravechance"=>"Raw Chance of Pit Appearing in Graveyard?,range,0,100,5|20",
        ),
        "prefs"=>array(
            "terror_wraith"=>"Wraith curse level?,int|0",
            "seen_wraith"=>"Wraith curse active(good/bad)?,int|0",
        )
    );
    return $info;
}

function scavenge_install(){
	/*$forestchance=get_module_setting("forestchance");
	$villagechance=get_module_setting("villagechance");*/
    module_addeventhook("graveyard",
			"return get_module_setting(\"gravechance\", \"scavenge\");");
    module_addeventhook("village",
			"return get_module_setting(\"villagechance\", \"scavenge\");");
	module_addhook("newday");
    return true;
}

function scavenge_uninstall(){
    return true;
}

function scavenge_dohook($hookname,$args){
    global $session;
	switch($hookname){
    case "newday":
        $terror_wraith=get_module_pref("terror_wraith");
		if ($terror_wraith == 1) {
			output("`7You are still shaking at the memory of dead eyes, staring at you accusingly!.");
			apply_buff('terror', array(
				"name"=>"`\$Terror",
				"rounds"=>15,
				"wearoff"=>"You stop shivering at shadows.",
				"atkmod"=>0.95,
				"defmod"=>0.95,
				"roundmsg"=>"You jump at shadows, and find it harder to fight!",
			));
			set_module_pref("terror_wraith",get_module_pref("terror_wraith")-1);
		} elseif ($terror_wraith == 2){
			output("`7You are still shaking at the memory of dead eyes, staring at you accusingly!.");
			apply_buff('terror', array(
				"name"=>"`\$Unnatural Terror",
				"rounds"=>20,
				"wearoff"=>"You stop shuddering.",
				"atkmod"=>0.90,
				"defmod"=>0.90,
				"roundmsg"=>"You find it hard to stop from running!",
			));
			set_module_pref("terror_wraith",get_module_pref("terror_wraith")-1);
		} elseif ($terror_wraith >= 2){
			output("`7You can barely hold your sword thinking of the vengance of the dead!");
			apply_buff('terror', array(
				"name"=>"`\$Unnatural Horror",
				"rounds"=>25,
				"wearoff"=>"You breathe a little easier.",
				"atkmod"=>0.75,
				"defmod"=>0.75,
				"roundmsg"=>"Your sword shakes in your hand, and you find it harder to fight!",
			));
			set_module_pref("terror_wraith",get_module_pref("terror_wraith")-2);
		}
        break;
	}
    return $args;
}

function scavenge_runevent($type) {
	require_once("modules/scavenge/scavenge_runevent.php");
}

function scavenge_run(){
}
?>
