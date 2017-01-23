<?php
//the first occurrence of a module that works like this is running at lotgd.de with many more options and the like
//the general idea is very good and people with a hundred dks are rather unbalanced
// so a global reset (at some time) with a bonus would be nice
/* 
1.01 fixed a major (1 line) glitch to prevent non-megausers from accessing the cave
*/
function circulum_getmoduleinfo(){
	$info = array(
	    "name"=>"Circulum Vitae",
		"description"=>"This gives players the opportunity to start anew with benefits",
		"version"=>"1.01",
		"author"=>"`4Oliver Brendel`0",
		"category"=>"Circulum Vitae",
		"download"=>"http://lotgd-downloads.com",
		"settings"=>array(
			"Circulum Vitae - Preferences,title",
				"dks"=>"At how many DKs will the CV be available?,int|150",
				"showcirculum"=>"Show a title in the users bioinfo?,bool|1",
				"Note: These setting will be overridden once you select the field to be kept in the editor,note",
				"startgold"=>"How many gold will the resetted player have,int|500",
				"startgems"=>"How many gems will the resetted player have,int|0",
				"maxhitpoints"=>"How many maxhitpoints will the resetted player have,int|10",
			),
		"prefs"=>array(
		    "Circulum Vitae - User prefs,title",
				"circuli"=>"Number of CVs the player have,int|0",
				"Note: don't change this if you don't need to... it is changed by the module!,note",
			),
		);
    return $info;
}

function circulum_install() {
	module_addhook_priority("dk-preserve",1);
	module_addhook("superuser");
	module_addhook_priority("bioinfo",50);
	module_addhook_priority("forest",50);
	return true;
}

function circulum_uninstall() {
  output_notl ("Performing Uninstall on Circulum Vitae. Thank you for using!`n`n");
  return true;
}


function circulum_dohook($hookname, $args) {
	global $session;
		//$array=array(7,65227);
		//if (!in_array($session['user']['acctid'],$array)) return $args;
		switch($hookname) {
			case "forest":
				//location of the secret cave where you can obtain the boons
				if ($session['user']['dragonkills']>=get_module_setting('dks')) {
					addnav("Secrets");
					addnav("`1T`lhe `1S`lecret `1C`lave","runmodule.php?module=circulum&op=hiddencave");
				}
				break;
			case "superuser":
				if (($session['user']['superuser'] & SU_MEGAUSER)== SU_MEGAUSER) {
					//only superusers may have it.
					addnav("Editors");
					addnav("Circulum Vitae Editor","runmodule.php?module=circulum&op=editor");
				}
				break;
			case "bioinfo":
				if (get_module_pref('circuli','circulum',$args['acctid'])>0 && get_module_setting('showcirculum','circulum')) {
					require_once('modules/circulum/func/circulum_nochange.php');
					output("`^Honourable Title: `\$%s`n",circulum_get_maxgendertitle(get_module_pref('circuli',"circulum",$args['acctid']),($args['sex']?"female":"male")));
				}
				break;
		}
	return $args;
}

function circulum_run()	{
	global $session;
	$dks=get_module_setting("dks");
	$op=httpget('op');
	$mode=httpget('mode');
	if ($op=="editor") 	check_su_access(SU_MEGAUSER); //check again Superuser Access
	if ($op=="")  $op="default";
	require("modules/circulum/run/$op.php");
}

?>
