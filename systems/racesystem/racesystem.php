<?php

function racesystem_getmoduleinfo(){
	$info = array(
		"name"=>"Race System",
		"version"=>"1.0",
		"author"=>"`2Oliver Brendel",
		"category"=>"Races",
		"download"=>"",
		"settings"=>array(
			"Race System Settings,title",
			"worldname"=>"Name for the city in the outside world|Konoha Gakure",
			"minedeathchance"=>"Chance for all folks to die in the mine,range,0,100,1|15",
		),
		"prefs-drinks"=>array(
			"City Drink Preferences,title",
			"servedincity"=>"Is this drink served in the outside world?,bool|0",
		),
	);
	return $info;
}

function racesystem_install(){
	module_addhook("chooserace");
	module_addhook("setrace");
	module_addhook("creatureencounter");
	module_addhook("villagetext");
	module_addhook("travel");
	module_addhook("village");
	module_addhook("validlocation");
	module_addhook("validforestloc");
	module_addhook("moderate");
	module_addhook("drinks-text");
	module_addhook("changesetting");
	module_addhook("stabletext");
	module_addhook("stablelocs");
	module_addhook("drinks-check");
	module_addhook("raceminedeath");
	module_addhook("racenames");
	module_addhook("scrylocation");
	module_addhook("pvpadjust");
	module_addhook("adjuststats");
	module_addhook_priority("newday",INT_MAX);
	return true;
}

function racesystem_uninstall(){
	global $session; //no other races planned, so a full race reset here
	$sql = "UPDATE " . db_prefix("accounts") . " SET location='".getsetting("villagename", LOCATION_FIELDS)."'";
	db_query($sql);
	$sql = "UPDATE  " . db_prefix("accounts") . " SET race='" . RACE_UNKNOWN;
	db_query($sql);
	return true;
}

function racesystem_dohook($hookname,$args){
	global $session,$resline;
	//note: if you want to share a city with somebody, you need to set the same cityname as in the "city" string in the array
	$schemas=array(
		"text"=>"module-racesystem",
		"clock"=>"module-racesystem",
		"calendar"=>"module-racesystem",
		"title"=>"module-racesystem",
		"sayline"=>"module-racesystem",
		"talk"=>"module-racesystem",
		"newest"=>"module-racesystem",
		"gatenav"=>"module-racesystem",
		"fightnav"=>"module-racesystem",
		"tavernnav"=>"module-racesystem",
		"marketnav"=>"module-racesystem",
		"stablename"=>"module-racesystem",
		);
	$races=racesystem_getraces();
	switch($hookname){
		case "newday":
			$r=$session['user']['race'];
			$keys=array_keys($races);
			if (in_array($r,$keys)) {
				if ($races[$r]['raceevalnewday']) eval($races[$r]['raceevalnewday']);
			}
			break;
		case "pvpadjust":
			$r=$args['race'];
			$keys=array_keys($races);
			if (in_array($r,$keys)) {	
				if (isset($races[$r]['pvpadjust']) && $races[$r]['pvpadjust']) eval($races[$r]['pvpadjust']);
			}
			break;
		case"adjuststats":
			$r=$args['race'];
			$keys=array_keys($races);
			if (in_array($r,$keys)) {
				if (isset($races[$r]['adjuststats']) && $races[$r]['adjuststats']) eval($races[$r]['adjuststats']);
			}
			break;			
		case "racenames":
			foreach ($races as $name=>$race) {
				$args[$name] = $name;
			}
			break;
		case "raceminedeath":
/*			if ($session['user']['race'] == $race) {
				$args['chance'] = get_module_setting("minedeathchance");
				$args['racesave'] = "Fortunately your skill let you escape unscathed.`n";
				$args['schema'] = "module-racesystem_";
			}
			break;
*/		case "changesetting":
			// Ignore anything other than villagename setting changes for myself
			if (isset($args['setting']) && $args['setting'] == "worldname" && $args['module']=="racesystem") {
				if ($session['user']['location'] == $args['old']) $session['user']['location'] = $args['new'];
				$sql = "UPDATE ".db_prefix('accounts')." SET location='".addslashes($args['new'])."' WHERE location='".addslashes($args['old'])."'";
				db_query($sql);
				if (is_module_active("cities")) {
					$sql = "UPDATE ".db_prefix('module_userprefs')." SET value='".addslashes($args['new'])."' WHERE modulename='cities' AND setting='homecity' AND value='" . addslashes($args['old'])."'";
					db_query($sql);
				}
			}
			break;
		case "chooserace":
			if (is_module_active('alignment')) require_once('modules/alignment/func.php');
			foreach ($races as $race) {
				$failed='';	
				if ($race['racedesc']!='') {
					if (isset($race['requirements'])) {
						if (isset($race['requirements']['dks'])) {
							if (((int)$race['requirements']['dks'])>$session['user']['dragonkills']) continue;
						}
						
						if (isset($race['requirements']['gems'])) {
							if (((int)$race['requirements']['gems'])>$session['user']['gems']) continue;
						}
						if (is_module_active('alignment')) {
							if (substr($race['requirements']['alignment'],0,1)=='!') {
								$tag=substr($race['requirements']['alignment'],1,1);
								switch ($tag) {
									case "E":
										if (is_evil()) {
											$failed.=translate_inline("You may not be evil to select this.`n");
										}
										break;
									case "G":
										if (is_good()) {
											$failed.=translate_inline("You may not be good to select this.`n");
										}
										break;
									case "C":
										if (is_chaotic()) {
											$failed.=translate_inline("You may not be chaotic to select this.`n");
										}
										break;
									case "N":
										if (is_trueneutral()) {
											$failed.=translate_inline("You may not be neutral to select this.`n");
										}
										break;
									case "L":
										if (is_lawful()) {
											$failed.=translate_inline("You may not be lawful to select this.`n");
										}
										break;
								}
							} else {
								$dem=substr($race['requirements']['alignment'],0,1);
								$al=substr($race['requirements']['alignment'],1,1);
								if ($dem=='C' && !is_chaotic()) {
									$failed.=translate_inline("You need to be chaotic to select this.`n");
								}
								if ($dem=='L' && !is_lawful()) {
									$failed.=translate_inline("You need to be lawful to select this.`n");
								}
								if ($dem=='N' && !is_demneutral()) {
									$failed.=translate_inline("You need to be of neutral demeanor to select this.`n");
								}
								if ($al=='G' && !is_good()) {
									$failed.=translate_inline("You need to be good to select this.`n");
								}
								if ($al=='E' && !is_evil()) {
									$failed.=translate_inline("You need to be evil to select this.`n");
								}
								if (($al=='' || $al=='N') && !is_alneutral()) {
									$failed.=translate_inline("You need to be neutral to select this.`n");
								}
							}
						}
					}
				}
				output("`0`c`@~~~~~~~~~~~~%s%s`@~~~~~~~~~~~~`0`c`4`n",$race['colour'],sanitize($race['name']));
				if ($failed=='') {
					output_notl("<a href='newday.php?setrace=".addslashes($race['id'])."$resline'>`2%s", sprintf_translate($race['racedesc'],$race['city']), true);
					addnav("Races");
					addnav(array("%s %s`0",$race['colour'],$race['name']),"newday.php?setrace=".addslashes($race['id'])."$resline");
					addnav("","newday.php?setrace=".addslashes($race['id'])."$resline");
				} else {
					output_notl("`)%s`)", sprintf_translate($race['racedesc'],$race['city']), true);
					output_notl("`\$`c%s`c`n",$failed);
					addnav("Not Available");
					addnav(array("%s %s`0",$race['colour'],$race['name']),"");
				}
			}
			//output("`0`c`@~~~~~~~~~~~~~~~~~~~~~~~~`0`c`4");	
			break;
		case "setrace":
			foreach ($races as $name=>$race) {
				if ($session['user']['race']==$race['id']){
					$session['user']['race']=$name;
					output_notl("%s %s",$race['colour'],translate_inline($race['setracedesc']));
					if (isset($race['raceeval']) && $race['raceeval']) eval($race['raceeval']);
					if (is_module_active("cities")) {
						if ($session['user']['dragonkills']==0 &&
								$session['user']['age']==0){
							set_module_setting("newest-{$race['city']}",
									$session['user']['acctid'],"cities");
						}
						set_module_pref("homecity",$race['city'],"cities");
						debug("Set Homecity to ".$race['city']);
						if ($session['user']['age'] == 0)
							$session['user']['location']=$race['city'];
					}
					break;
				}
			}
			break;
		case "validforestloc":
		case "validlocation":
			if (is_module_active("cities")) {
				foreach ($races as $name=>$race) {
					$cities[$race['city']]=$name;
				}
				foreach ($cities as $city=>$race) {
					$args[$city] = "village-$race";
				}
				
			}
			break;
		case "scrylocation":
			if (is_module_active("cities")) {
				foreach ($races as $race) {
					if ($race['text']!='') $args[$race['city']]="vill-".$race['id']."-".substr(sanitize($race['name']),0,20-5-strlen($race['id']));
				}				
			}
			break;
		case "moderate":
			if (is_module_active("cities")) {
				tlschema("commentary");
				foreach ($races as $race) {
					if ($race['text']!='') $args["vill-".$race['id']."-".substr(sanitize($race['name']),0,20-5-strlen($race['id']))]=sprintf_translate("%s", $race['city']);
				}				
				tlschema();
			}
			break;
		case "travel":
			$capital = getsetting("villagename", LOCATION_FIELDS);
			$cities=array();
			foreach ($races as $race) {
				if (!$race['text']) continue; // no city defined
				$cities[$race['city']]=0;
			}
			$cities=array_keys($cities);
			tlschema("module-cities");
			$args = modulehook("count-travels", array('available'=>0,'used'=>0));
			$free = max(0, $args['available'] - $args['used']);
			if ($free==0) {
				output("You are too tired to travel all by yourself today!`n`n");
				break;
			}
			$cantravel=false;
			foreach ($cities as $city) {
				$hotkey = substr($city, 0, 1);
				if ($session['user']['location']==$capital){
					addnav("Safer Travel");
					addnav(array("%s?Go to %s", $hotkey, $city),"runmodule.php?module=cities&op=travel&city=$city");
					$cantravel=true;
				}elseif ($session['user']['location']!=$city){
					addnav("More Dangerous Travel");
					addnav(array("%s?Go to %s", $hotkey, $city),"runmodule.php?module=cities&op=travel&city=$city&d=1");
					$cantravel=true;
				}
				if ($session['user']['superuser'] & SU_EDIT_USERS){
					addnav("Superuser");
					addnav(array("%s?Go to %s", $hotkey, $city),"runmodule.php?module=cities&op=travel&city=$city&su=1");
				}
			}
			//if cantravel==false, then he should be able to hitchhike,which the former hitch.php did not cover, so give modules a hook as they won't see the individual travel costs without evaluating themselves
			if ($cantravel==false) modulehook("travel-notravelpossible",array());
			tlschema();
			break;	
		case "villagetext":
			foreach ($races as $race) {
				if ($session['user']['location'] == $race['city'] && $race['text']){
					$args['text']=$race['text'];
					$args['schemas']['text'] = $schemas['text'];
					$args['clock']=$race['clock'];
					$args['schemas']['clock'] = $schemas['clock'];
					if (is_module_active("calendar")) {
						$args['calendar'] = $race['calendar'];
						$args['schemas']['calendar'] = $schemas['calendar'];
					}
					$args['title']= $race['title'];
					$args['schemas']['title'] = $schemas['title'];
					$args['sayline']=$race['sayline'];
					$args['schemas']['sayline'] = $schemas['sayline'];
					$args['talk']=$race['talk'];
					$args['schemas']['talk'] = $schemas['talk'];
					$new = get_module_setting("newest-".$race['city'], "cities");
					//extracted from the core races, standard code
					if ($new != 0) {
						$sql =  "SELECT name FROM " . db_prefix("accounts") .
							" WHERE acctid='$new'";
						$result = db_query_cached($sql, "newest-".$race['city']);
						if (db_num_rows($result)>0) {
							$row = db_fetch_assoc($result);
							$args['newestplayer'] = $row['name'];
							$args['newestid']=$new;
						}
					} else {
						$args['newestplayer'] = $new;
						$args['newestid']="";
					}
					if ($new == $session['user']['acctid']) {
						$args['newest']=$race['younewest'];
					} else {
						$args['newest']=$race['newest'];
					}
					$args['schemas']['newest'] = $schemas['newest'];
					$args['gatenav']=$race['gatenav'];
					$args['schemas']['gatenav'] = $schemas['gatenav'];
					$args['fightnav']=$race['fightnav'];
					$args['schemas']['fightnav'] = $schemas['fightnav'];
					$args['marketnav']=$race['marketnav'];
					$args['schemas']['marketnav'] = $schemas['marketnav'];
					$args['tavernnav']=$race['tavernnav'];
					$args['schemas']['tavernnav'] = $schemas['tavernnav'];
					if (isset($race['stablename']) && $race['stablename']>'') {
						$args['stablename']=$race['stablename'];
						$args['schemas']['stablename'] = $schemas['stablename'];
						unblocknav("stables.php");
					}
					$args['section']="vill-".$race['id']."-".substr(sanitize($race['name']),0,20-5-strlen($race['id'])); 
				}
			}
			break;
		case "stabletext":
			foreach ($races as $race) {
				if ($session['user']['location'] == $race['city'] && $race['stablename']){
					foreach ($race['stable'] as $field=>$content) {
						$args[$field]=$content;
						$args['schemas'][$field]="module-racesystem";
					}
				}
			}
			break;
		case "stablelocs":
	case "stablelocs":
		foreach ($races as $race) {
			if (isset($race['mounts']) && $race['mounts']==1) {
				tlschema("mounts");
				$args[$race['city']]=sprintf_translate("The Village of %s", $race['city']);
				tlschema();
			}
		}
		break;		
	}
	return $args;
}

function racesystem_run(){
	$op = httpget("op");
	switch($op){
	case "ale"://For the sake of dwarvenkind, but not tested.
		require_once("lib/villagenav.php");
		page_header("Great Kegs of Ale");
		output("`3You make your way over to the great kegs of ale lined up near by, looking to score a hearty draught from their mighty reserves.");
		output("A mighty dwarven barkeep named `\$G`4argoyle`3 stands at least 4 feet tall, and is serving out the drinks to the boisterous crowd.");
		addnav("Drinks");
		modulehook("ale");
		addnav("Other");
		villagenav();
		page_footer();
		break;
	}
}

function racesystem_getraces() {
	require("modules/racesystem/races.php");//this is where your races are located in the array $races. Check the sample file for more information.
	return $races;
}

?>
