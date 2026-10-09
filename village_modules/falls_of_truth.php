<?php

function falls_of_truth_getmoduleinfo(){
	$info = array(
		"name"=>"The Falls of Truth",
		"author"=>"`LKurt Mills",
		"version"=>"1.0",
		"category"=>"Village",
		"download"=>"",
		"settings"=>array(
            "The Falls of Truth - Settings,title",
			"falls_of_truthloc"=>"Where does the game appear,location|".getsetting("villagename", LOCATION_FIELDS) //Kumogakure
        ),
		"prefs"=>array(
			"seentoday"=>"Has the player been to the falls today?,bool|0",
			),
	);
	return $info;
}

function falls_of_truth_install(){
	module_addhook("village");
	module_addhook("newday");
	module_addhook("changesetting");
	return true;
}
function falls_of_truth_uninstall(){
	return true;
}

function falls_of_truth_dohook($hookname,$args){
	global $session;
	switch($hookname){
	case "changesetting":
		if ($args['setting'] == "villagename") {
			if ($args['old'] == get_module_setting("falls_of_truthloc")) {
				set_module_setting("falls_of_truthloc", $args['new']);
			}
		}
		break;
	case "newday":
		set_module_pref("seentoday",0);
		break;
	case "village":
		if ($session['user']['location'] == get_module_setting("falls_of_truthloc")) {
            tlschema($args['schemas']['tavernnav']);
			addnav($args['tavernnav']);
            tlschema();
			addnav("Falls of Truth","runmodule.php?module=falls_of_truth&op=");
		}
		break;
	}
	return $args;
}

function falls_of_truth_run(){
	global $session;
	$op = httpget("op");	
	page_header("The Falls of Truth");
	addnav("Navigation");
	villagenav();
	addnav("Actions");
	if($op==''){
		$seentoday=get_module_pref('seentoday');
		if($seentoday==0){
			output("`~You stand before the famed Falls of Truth.`n");
			output("It is said that those who approach will be able to face their true selves, and gain victory over them.`n");
			output("As you step closer, you see a near mirror image of you through the waters.");
			addnav("Face your true self","runmodule.php?module=falls_of_truth&op=face");
		}else output("`~You can't bring yourself to again today.");
		
	}elseif($op=="face"){
		set_module_pref("seentoday",1);
		output("`~You lunge at your true self, the same moment it goes for you.`n");
		$align=falls_of_truth_get();
		switch($align){
		case 0:
			output("The evil in you heart is embodied in this being you fight. Blow after blow you engage in what seems tobe an endless battle.`n`n");
		break;
		case 1:
			output("The mirror of you is in perfect balance, much as you are also. Locked in an impossible battle, you fight on.`n`n");
		break;
		case 2:
			output("The good inside you shines through this strange mirror of you. You engage in an eternal struggle against one another.`n`n");
		break;
		}
		$change=e_rand(0,1);
		if($change==0){
			output("The darkness wins. You feel more `\$evil`~.");
			increment_module_pref('alignment',-1,'alignment');
		}else{
			output("The good overcomes. You feel more `@good`~.");
			increment_module_pref('alignment',1,'alignment');
		}
	}	
	page_footer();
}

//Borrowed this from you. =D
function falls_of_truth_get() {
	$evilalign = get_module_setting('evilalign','alignment');
	$goodalign = get_module_setting('goodalign','alignment');
	$useralign = get_module_pref('alignment','alignment');
	//0 equals evil, 1 equals neutral, 2 equals good alignment
	if ($useralign <= $evilalign) return 0;
	if ($useralign >= $goodalign) return 2;
	return 1;
}
?>
