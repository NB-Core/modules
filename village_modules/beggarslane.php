<?php

function beggarslane_getmoduleinfo(){
	$info = array(
		"name"=>"Beggars Lane",
		"author"=>"`2Oliver Brendel",
		"version"=>"1.0",
		"category"=>"Village",
		"download"=>"http://lotgd-downloads.com",
		"settings"=>array(
			"The Beggars Lane,title",
			"(alignment ready),note",
			"daily"=>"How often can someone be generous a day (users receive max up to twice)?,range,1,10|1",
		),
		"preferences"=>array(
			"receive"=>"Player received today how often funds from the lane?,viewonly",
			"gave"=>"Player gave today how often funds at the lane?,viewonly",
		),
	);
	return $info;
}

function beggarslane_install(){
	module_addhook_priority("village",50);
	module_addhook_priority("newday",50);
	return true;
}
function beggarslane_uninstall(){
	return true;
}

function beggarslane_dohook($hookname,$args){
	global $session;
	$cost = get_module_setting("cost");
	switch($hookname){
	case "village":
		tlschema($args['schemas']['tavernnav']);
		addnav($args['tavernnav']);
		tlschema();
		addnav("Beggars Lane","runmodule.php?module=beggarslane");
		break;
	case "newday":
		set_module_pref("receive",0);
		set_module_pref("gave",0);
	}
	return $args;
}

function beggarslane_run(){
	global $session;
	$op = httpget("op");
	$gold = httpget("gold");
	require_once("lib/commentary.php");
	page_header("The Beggars Lane");
	output("`b`i`c`\$The `1Beggars `vLane`c`i`b`n");
	addcommentary();
	villagenav();
	switch ($op) {
		case "alms":
			$sql="SELECT a.author FROM ".db_prefix("commentary")." as a LEFT JOIN ".db_prefix("module_userprefs")." as b ON a.author=b.userid WHERE b.modulename='beggarslane' AND b.setting='receive' AND (b.value is null OR b.value<2*".get_module_setting("daily").") AND a.section='beggarslane' ORDER BY a.commentid DESC LIMIT 1;";
			$result=db_query($sql);
			if (db_num_rows($result)<=0) {
				output("`2Your intention was good, but no one is actually here to receive it...`n`n");
			} else {
				$row=db_fetch_assoc($result);
				if ($row['author']==$session['user']['acctid']) {
					output("`2You pour some money out... and put it into your hand... do you feel like a better person?`n");
					output("Don't try to give alms to yourself, you twit!`n`n");
					break;
				}
				addnav("Return to the beggars lane","runmodule.php?module=beggarslane");
				output("`2You hand one pitiful beggar %s gold pieces... you feel like a good person.`n`n",$gold);
				increment_module_pref("gave");
				if (is_module_active("alignment")) {
					require_once("modules/alignment/func.php");
					if (e_rand(1,20)==1) align(1); //get better
				}
				if (e_rand(1,10)==1) {
					output("`\$You receive a `%charm point`\$ for being such a kind guy!`n");
					$session['user']['charm']++;
				}
				$session['user']['gold']-=$gold;
				$msg=array("`2Someone very generous gave you `^%s gold pieces`2 at the beggars lane. Obviously his heart was as open as his purse.",$gold);
				require_once("lib/systemmail.php");
				if (e_rand(1,5)==1) {
					systemmail($row['author'],array("`RYou have received gold from the beggars lane!"),$msg);
					increment_module_pref("receive",1,"beggarslane",$row['author']);
					$sql="UPDATE ".db_prefix("accounts")." SET gold=gold+$gold WHERE acctid={$row['author']};";
					db_query($sql);
				}
			}
			break;
		default:
			output("`2You enter the beggars lane, a place where people can beg for gold... as it is clearly obvious as the first ones try to go for you with dog eyes...`n`n");
			if ($session['user']['gold']>0 && get_module_pref("gave")<get_module_setting("daily")) {
				$gold=beggarslane_get();
				addnav(array("Give alms (%s gold)",$gold),"runmodule.php?module=beggarslane&op=alms&gold=$gold");
			} elseif ($session['user']['gold']==0) {
				output("`\$Having no gold, you start to fight for a place among the beggars...`n`n");
			} else {
				output("`\$Sadly you don't feel like giving away any money anymore... you already comforted your need for charity today enough.`2`n`n");
			}
			commentdisplay("`n`n`@Talk.`n","beggarslane","Beg",20,"begs");
		break;
	}

	page_footer();
}

function beggarslane_get() {
	global $session;
	$gold=$session['user']['gold'];
	if ($gold>10000) $gold=10000;
	$level=$session['user']['level'];
	$first=($level/3)*log($gold)+20;
	return round(($first>=$gold?$gold/2:$first),0);
}
?>
