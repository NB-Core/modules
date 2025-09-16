<?php

function dwcityhouses_getmoduleinfo(){
    $info = array(
        "name"=>"Cityhouse Dwellings",
		"version"=>"20051221",
		"download"=>"modules/dwcityhouses.php?op=download",
        "author"=>"Nightborn based on Sixf00t4s work",
        "category"=>"Dwellings",
        "description"=>"Gives Cityhouses as a dwelling option for players",
        "requires"=>array(
	       "dwellings"=>"20051206|By Sixf00t4, available on DragonPrime",
        ), 
        "settings"=>array(
            "Cityhouses Settings,title",
                "dwname"=>"What is the display name for this type?,text|`7cityhouse",
                "dwnameplural"=>"What is the plural display name for this type?,text|`7cityhouses",
                ""=>"<i>Enter display names in lowercase</i>,note",
                "globallimit"=>"How many are allowed globally? (0 = infinite),int|0",
                "goldcost"=>"What is the cost in gold?,int|50000",
                "gemcost"=>"What is the cost in gems?,int|100",
                "turncost"=>"How many turns does it cost to build?,int|25",
                "maxkeys"=>"What is max number of keys available per cityhouse?,int|15",
                "ownersleep"=>"Enable sleeping for owner?,bool|0",
                "othersleep"=>"Enable sleeping for others?,bool|0",
                "maxsleep"=>"What is the max number of sleepers?,int|5",
                "dkreq"=>"How many DKs before they can see this type?,int|5",
				"typeid" => "What is the type number in the db?,viewonly|0",
			"Coffer Settings,title",
                "enablecof"=>"Enable coffers?,bool|1",
                "maxgold"=>"What is the max storage of gold? - 0 to disable,int|40000",
                "maxgems"=>"What is the max storage of gems? - 0 to disable,int|0",
				"goldxfer"=>"What is the amount limit for each coffer transaction of gold?(per level),int|800",
                "gemsxfer"=>"What is the amount limit for each coffer transaction of gem?,int|2",				
        ),
        "prefs-city"=>array(
            "showdwhouses"=>"Allow houses here?,bool|1",
            "loclimitdwhouses"=>"How many total houses are allowed here? (0 = infinite),int|0",
            "userloclimitdwhouses"=>"How many houses are allowed per person here? (0 = infinite),int|1",
			),		
    );        
    return $info;
}

function dwcityhouses_install(){
    module_addhook("dwellings");
    module_addhook("dwellings-list-type");
    if (!is_module_active('dwcityhouses')){
        $sql="select module from ".db_prefix("dwellingtypes")." where module='dwcityhouses'";
        $res=db_query($sql);
        if(db_num_rows($res)==0){
            $sql = "INSERT INTO ".db_prefix("dwellingtypes")." (module) VALUES ('dwcityhouses')";
            db_query($sql);
        }
    }
    $sql = "Select typeid from ".db_prefix("dwellingtypes")." where module='dwcityhouses'";
    $result = db_query($sql);
    $row = db_fetch_assoc($result);
    set_module_setting("typeid",$row['typeid'],"dwcityhouses");
    return true;
}

function dwcityhouses_uninstall() {
    $sql = "delete from ".db_prefix("dwellingtypes")." where module='dwcityhouses'";
    db_query($sql);  
	return true;
}

function dwcityhouses_dohook($hookname,$args) {
	global $session;
	switch ($hookname) {
 
        case "dwellings-list-type":
            addnav("Show Only Types");
            addnav(array("%s",ucfirst(get_module_setting("dwnameplural","dwcityhouses"))),"runmodule.php?module=dwellings&op=list&showonly=dwcityhouses&ref={$args['ref']}&sortby={$args['sortby']}&order={$args['order']}");
        break;

        case "dwellings":
            if(get_module_objpref("city",$args['cityid'],"showdwhouses")){
				output(" Several streets are filled with the %s`0 of the heroes residing here.",get_module_setting("dwnameplural"));
				if($args['allowbuy']==1 && $session['user']['dragonkills']>=get_module_setting("dkreq")){
					$cityid=$args['cityid'];
					addnav("Options");
					addnav(array("Establish a %s",ucfirst(get_module_setting("dwname","dwcityhouses"))),"runmodule.php?module=dwellings&op=buy&type=dwcityhouses&subop=presetup&cityid=$cityid");
				}
			}
        break;
	}
	return $args;
}

function dwcityhouses_run(){}

