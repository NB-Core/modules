<?php
//version information in readme.txt

function loveshack_getmoduleinfo(){
	$info = array(
		"name"=>"Loveshack",
		"version"=>"1.1",
		"author"=>"`@`bCortalUX`b`n`2rewrite by Oliver Brendel",
		"category"=>"Gardens",
		"download"=>"",
		"settings"=>array(
			"Loveshack - General,title",
			"loveDrinksAdd"=>"Status of Loveshack Drinks?,hidden|0",
			"lall"=>"Show the Love Shack in all villages?,bool|1",
			"loveloc"=>"If no- Where does the Love Shack appear?,location|".getsetting("villagename", LOCATION_FIELDS),
			"bartendername"=>"Name of the Bartender?,text|Jatti",
			"genderbartender"=>"Gender of the Bartender?,enum,0,Male,1,Female|0",
			"sg"=>"Same Gender Flirts allowed?,bool|1",
			"maxDayFlirt"=>"Maximum flirts per day?,range,1,100,1|10",
			"podrink"=>"Flirt points for buying someone a drink?,range,1,100,1|2",
			"prdrink"=>"Cost of buying someone a drink?,int|25",
			"poroses"=>"Flirt points for buying someone some roses?,range,1,100,1|10",
			"prroses"=>"Cost of buying someone some roses?,int|40",
			"poslap"=>"Flirt points lost for slapping someone?,range,1,100,1|5",
			"pokiss"=>"Flirt points for kissing someone?,range,1,100,1|12",
			"chancefail"=>"Chance for someone's flirt to fail?,range,0,100,1|10",
			),
		"prefs"=>array(
			"Loveshack - Preferences,title",
			"inShack"=>"Is this user in the Loveshack?,viewonly|0",
			"flirtsfaith"=>"`%Amount of times been unfaithful?,int|0",
			"Loveshack - Other,title",
			"`b`\$(only edit beyond this point if you know what you are doing!)`b,note",
			"`@(comma separated for each user id),note",
		),
		"prefs-drinks"=>array(
			"Loveshack - Drink Preferences,title",
			"drinkLove"=>"Is this drink served in the Loveshack?,bool|1",
			"loveOnly"=>"Is this drink Loveshack only?,enum,1,No,0,Yes|1",
		),
	);
	return $info;
}

function loveshack_install(){
	if (!is_module_active('Loveshack')){
		output_notl("`n`c`b`QLoveshack Module - Installed`0`b`c");
	}else{
		output_notl("`n`c`b`QLoveshack Module - Updated`0`b`c");
	}
	module_addhook("drinks-text");
	module_addhook("drinks-check");
	module_addhook("moderate");
	module_addhook("newday");
//	module_addhook_priority("footer-inn",1);
	module_addhook("gardens");
	if ($SCRIPT_NAME == "modules.php"){
		$module=httpget("module");
		if ($module == "Loveshack"){
			require_once("modules/loveshack/lovedrinks.php");
			loveshack_lovedrinks();
		}
	}
	return true;
}

function loveshack_uninstall(){
	require_once("modules/loveshack/lovedrinks.php");
	loveshack_lovedrinksrem();
	output_notl("`n`c`b`QLoveshack Module - Uninstalled`0`b`c");
	return true;
}

function loveshack_dohook($hookname, $args){
	global $session;
	require("modules/loveshack/dohook/$hookname.php");
	return $args;
}

function loveshack_run(){
	global $session;
	require_once("modules/loveshack/loveshack_func.php");
	$op = httpget('op');
	modulehook("header-loveshack",array());
	output("`c`xThe `\$Love`xshack`0`c`n`n");
	// Only known pages may be included; every link uses op=loveshack.
	require("./modules/loveshack/loveshack.php");
}
?>
