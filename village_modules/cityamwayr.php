<?php
// translator ready
// addnews ready
// mail ready

/***************************************************************************/
/* Name: City                                                              */
/* ver 1.5                                                                 */
/* Billie Kennedy => dannic06@gmail.com                                    */
/*                                                                         */
/*  Find and replace all text that says cityamwayr to make a clone of this */
/*    for another city.                                                    */
/*2-19-05                                                                  */
/*  - Fixed possibility of having 2 nav's for a city when traveling        */
/*  - Added Check Module Version compatibility                             */
/*2-24-05                                                                  */
/*  - Fixed Major bug that would allow for unlimited travel between cities */
/*    in effect allowing unlimited turns.                                  */
/*2-25-05                                                                  */
/*  - Squashed a stupid bug I created.  Caused a module not found error.   */
/*  - Added Requires                                                       */
/*2-26-05                                                                  */
/*  - Added support for World Module inclusion.                            */
/*3-27-05                                                                  */
/*  - Fixed travel yet once again.  Still doesn't work quite right but it  */
/*    is much better.                                                      */
/*  - Added Free Travel Stat since the module that makes it show up is     */
/*    blocked                                                              */
/***************************************************************************/


function cityamwayr_getmoduleinfo(){
	$info = array(
		"name"=>"City - Amwayr",
		"version"=>"1.6",
		"author"=>"Billie Kennedy",
		"category"=>"Cities",
		"download"=>"http://www.nuketemplate.com/modules.php?name=Downloads&d_op=viewdownload&cid=31",
		"vertxtloc"=>"http://dragonprime.net/users/Dannic/",
		"requires"=>array(
			"cities"=>"1.0|Eric Stevens, part of the core download",
		),
		"settings"=>array(
			"Amwayr Village Settings,title",
			"villagename"=>"Name for the village|Amwayr",
			"showforest"=>"Is the forest available from here?,bool|0",
			"travelfrom"=>"Where can you travel from,location|".getsetting("villagename", LOCATION_FIELDS),
			"travelto"=>"Where can you travel to,location|".getsetting("villagename", LOCATION_FIELDS),
			//"cost"=>"How much does it cost for city access?,int|0",
			"mindk"=>"How many Dragon Kills does a player have to have for access?,int|0",
			"worldmod"=>"Use the world module for travel instead of the Cities module?,bool|0",

		),
	);
	return $info;
}

function cityamwayr_install(){
	module_addhook("villagetext");
	module_addhook("village");
	module_addhook("travel");
	module_addhook("validlocation");
	module_addhook("moderate");
	module_addhook("changesetting");
	module_addhook("pvpstart");
	module_addhook("pvpwin");
	module_addhook("pvploss");
	//module_addhook("pointsdesc");
	module_addhook("mountfeatures");
	module_addhook("scrylocation");
	//module_addhook("charstats");
	return true;
}

function cityamwayr_uninstall(){
	global $session;
	$vname = getsetting("villagename", LOCATION_FIELDS);
	$gname = get_module_setting("villagename");
	$sql = "UPDATE " . db_prefix("accounts") . " SET location='$vname' WHERE location = '$gname'";
	db_query($sql);
	if ($session['user']['location'] == $gname)
		$session['user']['location'] = $vname;
	return true;
}

function cityamwayr_dohook($hookname,$args){
	global $session,$resline;
	$city = get_module_setting("villagename");
	switch($hookname){
	case "scrylocation":
		if (array_key_exists($city,$args)) {
			$args=array_diff($args,array($city=>$args[$city]));
		}
		break;
	/*case "pointsdesc":
		$args['count']++;
		$format = $args['format'];
		$str = translate("Access to %s and beyond will cost %s points.  (These are not used up)");
		$str = sprintf($str, get_module_setting("villagename"),
				get_module_setting("cost"));
		output($format, $str, true);
		break;*/
	case "pvpwin":
		if ($session['user']['location'] == $city && $args['badguy']['location']!="Inside dwelling") {
			$args['handled']=true;
			addnews("`4%s`3 defeated `4%s`3 in fair combat near the campfire in %s.", $session['user']['name'],$args['badguy']['creaturename'], $args['badguy']['location']);
		}
		break;
	case "pvploss":
		if ($session['user']['location'] == $city && $args['badguy']['location']!="Inside dwelling") {
			$args['handled']=true;
			addnews("`%%s`5 has been slain while attacking `^%s`5 near the campfire in `&%s`5.`n%s`0", $session['user']['name'], $args['badguy']['creaturename'], $args['badguy']['location'], $args['taunt']);
		}
		break;
	case "pvpstart":
		if ($session['user']['location'] == $city ) {
			$args['atkmsg'] = "`4You wander through the village gates, to where a large group of warriors are singing around the campfire. At the edges of the scrub, in the darkness and away from the others, some foolish warriors have bedded down for the night...`n`nYou have `^%s`4 PvP fights left for today.`n`n";
			$args['schemas']['atkmsg'] = 'module-cityamwayr';
		}
		break;
	case "travel":
		$args = modulehook("count-travels", array('available'=>0,'used'=>0));
		$free = max(0, $args['available'] - $args['used']);
		$tfree=$free+$session['user']['turns'];
		$capital = getsetting("villagename", LOCATION_FIELDS);
		$hotkey = substr($city, 0, 1);
		tlschema("module-cities");
		if ($session['user']['dragonkills'] < get_module_setting("mindk") && $session['user']['acctid']!=9340) //gyu hack
			break;
		if ($session['user']['location']!=$city){
			addnav("More Dangerous Travel");
			// Actually make the travel dangerous
			$cost=6;
			if($session['user']['location'] == get_module_setting("travelfrom")){
			addnav(array("Go to %s (%s points)",  $city,$cost),
					($tfree>=$cost?"runmodule.php?op=travel&module=cities&cost=6&city=$city&d=1":""));
			}
			if($session['user']['location'] == get_module_setting("travelto") && $session['user']['location'] != get_module_setting("travelfrom")){
			addnav(array("Go to %s (%s points)", $city,$cost),
					($tfree>=$cost?"runmodule.php?op=travel&module=cities&cost=6&city=$city&d=1":""));
			}

		}
		if ($session['user']['superuser'] & SU_EDIT_USERS){
			addnav("Superuser");
			addnav(array("Go to %s (free)", $city),
					"runmodule.php?op=travel&module=cities&city=$city&su=1");
		}
		tlschema();
		break;
	case "changesetting":
		// Ignore anything other than villagename setting changes
		if ($args['setting']=="villagename" && $args['module']=="cityamwayr") {
			if ($session['user']['location'] == $args['old']) {
				$session['user']['location'] = $args['new'];
			}
			$sql = "UPDATE " . db_prefix("accounts") . " SET location='" .
				$args['new'] . "' WHERE location='" . $args['old'] . "'";
			db_query($sql);
		}
		break;
	case "validlocation":
		if (is_module_active("cities"))
			$args[$city]="village-cityamwayr";
		break;
	case "moderate":
		if (is_module_active("cities")) {
			tlschema("commentary");
			$args["village-cityamwayr"]=sprintf_translate("%s Village", $city);
			tlschema();
		}
		break;
	case "villagetext":

		if ($session['user']['location'] == $city){
			$args['text']="`\$`c`@`bA small village named $city`b`@`c`n`n`2 The residents of $city busy themselves with various tasks of everyday life.  Small farm animals scurry underfoot as you make your way through the sparce buildings.`n`nMost of the villagers of $city seem to stare at you. As the entire village is protected by a large rift with a deadly mist, only the strongest are able to come here without aid.`n`nFinally, you are here (again?) in the country where only Sannins visit and reside since a certain incident. No other outsiders are tolerated here.`n`nYou get the feeling you're surrounded by power.`n`n";
            $args['schemas']['text'] = "module-cityamwayr";
			$args['clock']="`n`7A small sundial at the center of the village reads `&%s`7.`n";
            $args['schemas']['clock'] = "module-cityamwayr";
			if (is_module_active("calendar")) {
				$args['calendar'] = "`n`2Overheard whispers suggest that it is `&%s`2, `&%s %s %s`2.`n";
				$args['schemas']['calendar'] = "module-cityamwayr";
			}
			$args['title']=array("%s Village", $city);
			$args['schemas']['title'] = "module-cityamwayr";
			$args['sayline']="brags";
			$args['schemas']['sayline'] = "module-cityamwayr";
			$args['talk']="`n`&Nearby some visitors brag:`n";
			$args['schemas']['talk'] = "module-cityamwayr";
			$args['newest'] = "";

			//block all the multicity navs and modules. configure as needed for your server

			
			blocknav("lodge.php");
			//blocknav("weapons.php");
			blocknav("armor.php");
			blocknav("clan.php");
			blocknav("pvp.php");
			//blocknav("runmodule.php?module=cities&op=travel");
//			blocknav("list.php");

			if (!get_module_setting("showforest"))
				blocknav("forest.php");



			//blocknav("bank.php");
			//blockmodule("cities");
			blockmodule("questbasics");
			blockmodule("house");
			blockmodule("klutz");
			blockmodule("abigail");
			blockmodule("crazyaudrey");
			blockmodule("zoo");
			blockmodule("battlearena");
			blockmodule("beggarslane");
			



			$args['schemas']['newest'] = "module-cityamwayr";
			$args['gatenav']="Village Gates";
			$args['schemas']['gatenav'] = "module-cityamwayr";
			$args['fightnav']="Nearby Forest";
			$args['schemas']['fightnav'] = "module-cityamwayr";
			$args['marketnav']="Village Market";
			$args['schemas']['marketnav'] = "module-cityamwayr";
			$args['tavernnav']="Drunkard's Lane";
			$args['schemas']['tavernnav'] = "module-cityamwayr";
			$args['section']="village-cityamwayr";
			$args['infonav']="Village Council";
			$args['schemas']['infonav'] = "module-cityamwayr";
		}
		break;

	/*case "charstats":
			$travelleft = cityamwayr_freetravel();
			addcharstat("Extra Info");
			addcharstat("Free Travel",$travelleft);
		break;*/
	case "village":
		$from = get_module_setting("travelfrom");
		$to = get_module_setting("travelto");
		if ($session['user']['location']==$city){
			tlschema($args['schemas']['gatenav']);
			addnav($args['gatenav']);
			tlschema();
			addnav("V?Visit the Campsite","pvp.php?campsite=1");
	
			addnav("Visit the Hospital","healer.php?return=village.php");
			if(get_module_setting("worldmod")){
				break;
			}
		/*	$travelleft =cityamwayr_freetravel();
			$travelleft =1;
			if($travelleft > 0){
				addnav(array("Travel to %s",$from),"runmodule.php?module=cities&op=travel&city=$from&d=1");
				if($to != $from){
					addnav(array("Travel to %s",$to),"runmodule.php?module=cities&op=travel&city=$to&d=1");
				}
			}*/
		}
		break;
	}
	return $args;
}

function cityamwayr_run(){
}

function cityamwayr_freetravel() {
	$args = modulehook("count-travels", array('available'=>0,'used'=>0));
	$free = max(0, $args['available'] - $args['used']);
	return max(0,$free);
}
?>
