<?php

function zombie_getmoduleinfo(){
	$info = array(
		"name"=>"Zombie Outbreak",
		"author"=>"`LShinobiIceSlayer `~based on work by `2Oliver Brendel",
		"version"=>"1.1",
		"category"=>"Forest Specials",
		"download"=>"",
		"settings"=>array(
			"Zombie Outbreak Settings,title",
			"buffname"=>"Name of the debuff given.,text|`TZ`eombie `TI`enfection",
			"atkmod"=>"What attack mod does the debuff cause?,int|0.9",
			"defmod"=>"What defence mod does the debuff cause?,int|0.9",
			"gold"=>"What is the cost in gold for the cure?,int|3000",
			"gems"=>"What is the cost in gems for the cure?int|3",
			"title"=>"What is the new Title the player gets when infected?,text|`TZ`eombie",
			),
		"prefs"=>array(
			"Zombie Outbreak User Preferences,title",
			"infected"=>"Is the user infected?,bool|0",
		)
	);
	return $info;
}

function zombie_install(){
	module_addeventhook("forest", "return 0;");
	module_addhook("gypsy");	
	module_addhook("pvpwin");
	module_addhook("newday");
	module_addhook("dragonkill");

	return true;
}

function zombie_uninstall(){
	return true;
}

function infect(){
	global $session;
	$buffname=get_module_setting('buffname'); 
	$atk=get_module_setting('atkmod');
	$def=get_module_setting('defmod');
	
	apply_buff('zombie',array(
		"name"=>$buffname,
		"rounds"=>-1,
		"atkmod"=>$atk,
		"defmod"=>$def,
		"allowinpvp"=>1,		
		"schema"=>"module-zombie"
	));
	
	set_module_pref("infected",1);
	
	require_once("lib/names.php");
	$newtitle = get_module_setting("title");
	$newname = change_player_title($newtitle);
	$session['user']['title'] = $newtitle;
	$session['user']['name'] = $newname;
	
}

function zombie_dohook($hookname,$args){
	global $session;
	
	switch($hookname){
		case "newday":
			if(get_module_pref("infected")){
/*				$buffname=get_module_setting('buffname'); 
				$atk=get_module_setting('atkmod');
				$def=get_module_setting('defmod');
				apply_buff('zombie',array(
					"name"=>$buffname,
					"rounds"=>-1,
					"atkmod"=>$atk,
					"defmod"=>$def,
					"allowinpvp"=>1,		
					"schema"=>"module-zombie"
				));
*/
			zombie_cure();		
			}
			break;
		case "dragonkill":
			set_module_pref("infected",0);
			break;
		case "gypsy":
			addnav("Other");
			addnav("Talk to Cloaked Figure","runmodule.php?module=zombie&op=corner");
			output("`n`nIn the corner you see a cloaked figure sitting alone.");
			break;
		case "pvpwin":
			$zombie=get_module_pref('infected','zombie');
			if(!$zombie){
				$czombie=get_module_pref('infected','zombie',$args['badguy']['acctid']);
				if($czombie){
					infect();
					output("`n`~As you deliver the final blow, you notice the skin of %s`~ is grey and somewhat rotten. You try to get away, but notice the blood is on you, and feel the burning pain, knowing they have now passed the evil infection on to you as well.",$args['badguy']['name']);
				}
			}
			break;
	}
	return $args;
}

function zombie_runevent($type){
	
	global $session;
	
	if ($type == "forest") {
		$session['user']['specialinc'] = "module:zombie";
	}
	
	$op = httpget('op');
	
	if($op==''){
		$infected=get_module_pref("infected");
		debug("Am I infected? $infected");
		if($infected==false){
			output("`~You travel from the forest, but you seem to enter a deeper, darker part then ever before.");
			output("`nYou start hearing noises around you, soft at first, but slowly growing into loud groaning all around.");
			output("`nSuddenly from the darkness, beyond the trees, humanoid looking figures come at you, arms out stretched, marching in some odd way.");
			output("`nAs they approach, you can see their skin in grey and rotten, chunks falling off as they walk, some even missing complete limbs.");
			output("`nYou try to fend them off, but there are just too many to take on all at once, and soon, they pull you down, and start feeding on your flesh.");
			output("`n`nSometime later, you awaken, to find you are now one of them, so you mindlessly seek for flesh with them.");
			infect();
		}else{
			output("`~You see more of you kind, attacking somebody, so you join the feeding freenzy.");
			$session['user']['hitpoints'] += 10;
			output("`n`nYour health increases by 10 points.");
		}
	}
	$session['user']['specialinc'] = "";
}

function zombie_run(){
	global $session;
	page_header("The Corner");
	$op = httpget('op');
	
	if ($op=="corner"){
		
		output("`~The figure looks you over briefly.");
		if(get_module_pref("infected")){
			$gems=get_module_setting("gems");
			$gold=get_module_setting("gold");
			output("`n\"So, you've become one of them I see of them, can can help you, for a price.\"");
			if($session['user']['gems']>=$gems&&$session['user']['gold']>=$gold){
				addnav(array("Ask for the Cure, `^%s gold, %s gems.",$gold,$gems),"runmodule.php?module=zombie&op=cure");
			}else{
				output("`n`n\"However, you need %s gold and %s gems before I'll even think of helping you.",$gold,$gems);
			}
		}
		
	}elseif ($op=="cure"){
		zombie_cure();		
		output("`~The stranger places a hand on your forehead, and you feel a warm fill your body once more.");
	}
	addnav("Return to the Gypsy","gypsy.php");
	require_once("lib/villagenav.php");
	villagenav();
	page_footer();
	
}
function zombie_cure() {
	 global $session;
		$gems=get_module_setting("gems");
		$gold=get_module_setting("gold");
		$session['user']['gold']-=$gold;
		$session['user']['gems']-=$gems;
		
		require_once("lib/names.php");
		require_once("lib/titles.php");
		$oldtitle = get_dk_title($session['user']['dragonkills'], $session['user']['sex']);		
		$oldname = change_player_title($oldtitle);
		$session['user']['title'] = $oldtitle;
		$session['user']['name'] = $oldname;
		set_module_pref("infected",0);
		strip_buff("zombie");
}

?>
