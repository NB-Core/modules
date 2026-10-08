<?php


function rp_chatcommands_getmoduleinfo(){
	$info = array(
		"name"=>"RP Chat Commands",
		"version"=>"1.0",
		"author"=>"`@Oliver Brendel",
		"category"=>"Chat",
		"download"=>"",
		"prefs"=>array(
			"Chat User Preferences,title",
			//"user_villnews"=>"Display Latest News in the Village,bool|1",
		),
		"settings"=>array(
			"Chat User Settings,title",
		//	"showhome"=>"Show news on Home Page,enum,0,No,1,Above Login,2,Below Login",
		//	"newslines"=>"Number of news lines to display in the villages,int|4",
		),
	);
	return $info;
}

function rp_chatcommands_install(){
//modulehook("gmcommentarea", array("section"=>$section,"allow_gm"=>false,"commentary"=>$commentary));
	module_addhook("gmcommentarea");
	module_addhook("darkhorse-learning");
	return true;
}

function rp_chatcommands_uninstall(){
	return true;
}

function rp_chatcommands_dohook($hookname,$args){
	switch($hookname){
		case "gmcommentarea":
			$comment = $args['commentary'];
			$section = $args['section'];
			if (strstr($comment,"\"/start_rp\"")!==false || strcmp($comment,"::start_rp")==0) {
				//replace with RP start
				$out = rp_chatcommands_starter();
				$commentary = "RP Start: ".$out;
				$args['commentary']=$commentary;
			}
		break;
		case "darkhorse-learning":
			$op = (int)httpget('explain');
				addnav("Learn about RP Chatcommands","forest.php?op=bartender&explain=1");
			if ($op==0) {
				//nothing
			} else { //explain it 
				output("`n`n`)\"`xSo you want to know about how you can make your battles and conversations more intereshting? Alrighty shen...`n`nHere is a lisht of what you can do:`)\"`n`n");
				$coms = array(
					"::start_rp" => "Give you a short startup for an RP session if you need a few sentences on where to start.",
					);
				foreach ($coms as $key=>$text) {
					output_notl(" -> %s  ---> %s `n",$key,$text);
				}
			}
			break;
	}
	return $args;
}

function rp_rand($array) {
	$index = array_rand($array,1);
	return $array[$index];
}

function rp_chatcommands_starter($chatline="") {
	global $session;
	$lang=$session['user']['prefs']['language'];
	$userid = $session['user']['acctid'];
	$vloc = modulehook("validlocation", array());
	$days = 10;
	$returnstring = "`x";

	$who = array("You","Your opponent","Your crush","You","You");
	$returnstring.= rp_rand($who)." ";
	
	$what = array("approach(es)","lure(s)","threaten(s)","converse(s) with","observe(s)","seduce(s)");
	$returnstring.= rp_rand($what)." ";
	
	$sql = "SELECT name from ".db_prefix('accounts')." WHERE laston>'".date("Y-m-d H:i:s",strtotime("-$days days"))."' and acctid!=$userid ORDER BY rand() LIMIT 1";
	$result = db_query($sql);
	$row = db_fetch_assoc($result);


	if (!$row || $row['name']=="") $name = "Obi Wan Kenobi";
		else $name = color_sanitize($row['name']);
	
	
	$whom = array("your family","your best friend","your sensei","Jiraiya","Sasuke","Naruto",$name,$name,$name);
//	$returnstring.= rp_rand($whom);
	$returnstring.= "`x".$name."`x";
	$returnstring.= ". Last seen in ".array_rand($vloc,1);
	
	$returnstring .= ". There is a rumour that ";
	
	$reac = array("Naruto","Shizune","Sakura","Neji","Ino","Inuzuki");
	$returnstring.= rp_rand($reac)." ";

	$r_what = array("reported a(n)","might order a(n)","suggests a(n)","thinks about a(n)");
	$returnstring.= rp_rand($r_what)." ";

	$r_act = array("assassination","seduction","blackmail","fight","warning");
	$returnstring.= rp_rand($r_act)." ";

	$r_time = array("soon","in a few hours","now","later","tomorrow");
	$returnstring.= rp_rand($r_time).".";

	return $returnstring;
}
