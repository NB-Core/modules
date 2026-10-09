<?php
/*
Mystie's Sweets Shoppe
File:	sweets.php
Author:	Chris Vorndran (Sichae)
Date:	11/20/2004
Version: 1.2 (11/22/2004)

Mystie's Sweets Shoppe is for my good friend Mystie.
She loves... loves anything sweet ^.^
So, dontcha think a sweets shoppe would be just fitting?

v1.1
Snippet of Lonny's Castle code for the Chocolate Guns ^.^
Added in a random generator of outcomes

v1.2
Added in more checking, as well as a gem outcome. ^.^
Yes, this does block a person from getting over 6 gems perday ^.^ as well as over 6 Turns

v1.3
?

v1.4
cleaned up code, made 1 pref
*/
require_once("lib/villagenav.php");
require_once("lib/http.php");

function sweets_getmoduleinfo(){
	$info = array(
		"name"=>"Mystie's Sweet Shoppe",
		"author"=>"Chris Vorndran, modified by `2Oliver Brendel",
		"version"=>"1.4",
		"category"=>"Village",
		"download"=>"http://dragonprime.net/users/Sichae/sweets.zip",
		"vertxtloc"=>"http://dragonprime.net/users/Sichae/",
		"description"=>"User can purchase sweets, in order to gain boons and such. Allows for users to pour chocolate on unsuspecitng users.",
		"settings"=>array(
			"Mystie's Sweets Shoppe Settings,title",
			"times"=>"How many times per day,int|3",
			"poural"=>"How many times to pour,int|5",
			"stopq"=>"Does the stopper exist,bool|0",
			"Mystie's Sweets Shoppe Menu Pricing,title",
			"skitcost"=>"How much do skittles cost in gold,int|100",
			"choccost"=>"How much do chocolate bars cost in gold,int|100",
			"sodacost"=>"How much does a soda pop cost in gold,int|200",
			"milkcost"=>"How much does a milkshake cost in gold,int|300",
			"rootcost"=>"How much does a root beer float cost in gold,int|300",
			"icecost"=>"How much does an ice cream sundae cost in gold,int|400",
			"Mystie's Sweets Shoppe Location,title",
			"sweetloc"=>"Where does Mystie appear,location|".getsetting("villagename", LOCATION_FIELDS),
			"displaynews"=>"Display News for Dumping on somebody?,bool|0",
			),
		"prefs"=>array(
			"Mystie's Sweets Shoppe Preferences,title",
			"internal"=>"Internal Serialized String,viewonly",
			/*"pour"=>"How many times have poured,int|0",
			"used"=>"How many times been used,int|0",
			"stopped"=>"Has stopper been activated,bool|0",
			"stop"=>"How many days left for stopper,range,0,3,1|0",
			"event"=>"Event Message,text|",
			)*/
			),
		);
	return $info;
	}
function sweets_install(){
	module_addhook("moderate");
	module_addhook("changesetting");
	module_addhook("village");
	module_addhook("newday");
	module_addhook_priority("bioinfo",1);
	return true;
}
function sweets_uninstall(){
	return true;
}
function sweets_dohook($hookname,$args){
	global $session;
	$menu = array(1=>"`%S`\$k`^i`@t`#t`!l`%e",2=>"`qChocolate Bar",3=>"`@S`2o`#d`3a `&Pop",4=>"`qRoot `QBeer `6Float",5=>"`qM`&i`ql`&k`qs`&h`qa`&k`qe`&s",6=>"`!I`#c`3e `&Cream `qS`Qu`&n`\$d`Qa`qe");
	$internal=unserialize(get_module_pref('internal'));
	if (isset($internal['stopped'])) $stopped = $internal['stopped'];
		else $stopped = 0;
	if (isset($internal['stopq'])) $stopp = $internal['stopq'];
		else $stopq = 0;
	if (isset($internal['stop'])) $stop = $internal['stop'];
		else $stop = 0;
	$rand = e_rand(1,6);
	$menusel = $menu[$rand];
	switch ($hookname){
		case "bioinfo":
			$sql="SELECT * from ".db_prefix('commentary')." WHERE author=".$args['acctid']." AND section='sweettalk' ORDER BY commentid DESC LIMIT 5";
			$result=db_query($sql);
			if (db_num_rows($result)>0) {
				output("`n`^Recent Nasty Syrup Dumps:`n");
			}
			while ($row=db_fetch_assoc($result)) {
				$show=str_replace('/me',$args['name'],$row['comment'])."`n";
				output_notl($show);
			}
			output_notl("`n");
			break;
		case "changesetting":
			if ($args['setting'] == "villagename") {
			    if ($args['old'] == get_module_setting("sweetloc")) {
					set_module_setting("sweetloc", $args['new']);
				}
			}
			break;
		case "moderate":
			$args['sweettalk'] = "Sweet Shoppe";
			break;
		case "village":
			if ($session['user']['location'] == get_module_setting("sweetloc")) {
				tlschema($args['schemas']['marketnav']);
			    addnav($args['marketnav']);
				tlschema();
				addnav("Mystie's Sweets Shoppe","runmodule.php?module=sweets&op=enter");
				if (isset($internal['event']) && $internal['event'] <> ""){
					$message=sprintf_translate("`7\"You have been covered in `qChoclate Syrup`7! %s`7 dumped `qChocolate syrup `7on you from Mystie's Sweets Shoppe.\"",str_replace("\"","'",stripslashes($internal['eventculprit'])));
					output("`4`c`bSpecial Event: %s`b`c`7`n`n",$message);
					$internal['event']='';
					$internal['eventculprit']='';
				}
			}
			break;
		case "newday":
			if ($stopq==1){
				if ($stopped==1){
					$stop--;
					$internal['stop']=0;
					if ($stop>0){
						output("`nYou still feel the glint of the gems in your eyes, and do not wish to choke on one...");
						output(" A druid tells you, that you can go back in %s %s.`n",$stop,translate_inline($stop==1?"day":"days"));
					}
					if ($stop==0){
						output("`nThe sugar finally passes and you feel like going off to Mystie's for a nice %s.`n",$menusel);
						$internal['stopped']=0;
					}
				}
			}
			$internal['used']=0;
			$internal['pour']=0;
		break;
	}
	if (isset($session['user']['acctid'])) //only if logged in
		set_module_pref('internal',serialize($internal));
	return $args;
}
function sweets_run(){
	global $session;
	require("modules/sweets/sweets_run.php");
	page_footer();
}

?>
