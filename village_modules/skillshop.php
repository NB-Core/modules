<?php
//skillshop.php
//2.0 updated nav, preference and cost - Thanks to Kendaer
function skillshop_getmoduleinfo(){
	$info = array(
			"name"=>"Minzer's Skill Shop",
			"version"=>"2.3",
			"author"=>"Reznarth<br>Updated by: Chris Vorndran",
			"category"=>"Village|Shops",
			"download"=>"http://dragonprime.net/users/Sichae/skillshop.zip",
			"vertxtloc"=>"http://dragonprime.net/users/Sichae/",
			"description"=>"User can pay to have their specialty usage points refreshed.",
			"settings"=>array(
				"Skillshop Settings,title",
				"cost"=>"Gold needed to refresh skills `ibased on level`i,int|1000",
				"max"=>"Max refreshes per day,int|1",
				"name"=>"Minzers Name (male),text|Minzer",
				"Minzer Specifications,title",
				"race"=>"What Race is Minzer,text|Elf",
				"descrip"=>"What does Minzer look like,textarea|Minzer is a tall male, about six feet. His ears are quite rigid and perk at the slightest noise. He has a long flowing mane of blonde hair, with a blue glint to his eyes.",
				"Minzer's Location,title",
				"shoploc"=>"Where does Minzer appear,location|".getsetting("villagename", LOCATION_FIELDS)
				),
			"prefs"=>array(
				"Skillshop User Preferences,title",
				"refresh"=>"How many times did they refresh skills today,int|0",
				),
			);
	return $info;
}
function skillshop_install(){
	module_addhook("village");
	module_addhook("newday");
	module_addhook("changesetting");
	return true;
}

function skillshop_uninstall(){
	return true;
}

function skillshop_dohook($hookname,$args){
	global $session;
	switch($hookname){
		case "newday":
			set_module_pref("refresh",0);
			break;
		case "changesetting":
			if ($args['setting'] == "villagename") {
				if ($args['old'] == get_module_setting("shoploc")) {
					set_module_setting("shoploc", $args['new']);
				}
			}
			break;
		case "village":
			if ($session['user']['location'] == get_module_setting("shoploc")) {
				tlschema($args['schemas']['marketnav']);
				addnav($args['marketnav']);
				tlschema();
				addnav(array("%s`0's Skill Shop",get_module_setting("name")),"runmodule.php?module=skillshop&op=enter");
			}
			break;
	}
	return $args;
}
function skillshop_run(){
	global $session;
	$specialties = modulehook("specialtymodules");
	$spec = $specialties[$session['user']['specialty']];
	$uses =  get_module_pref("uses", $spec);
	$amt = get_module_pref("skill", $spec) / 3;
	if ($spec=='specialtysystem' &&is_module_active("specialtysystem")) {
		require_once("modules/specialtysystem/datafunctions.php");
		require_once("modules/specialtysystem/functions.php");
		$amt=$uses+1;
		$uses=specialtysystem_getskillpoints();
	}debug($uses);debug($amt);

	$op = httpget('op');
	$refresh = get_module_pref("refresh");
	$max = get_module_setting("max");
	$cost = (get_module_setting("cost")*$session['user']['level']);
	$gold = $session['user']['gold'];
	tlschema("race");
	$race = translate_inline(get_module_setting("race"));
	tlschema();
	$descrip = get_module_setting("descrip");
	$name=get_module_setting("name");
	page_header("%s's Skill Shop",sanitize($name));

	if ($op == "enter"){
		if ($refresh < $max){
			output("`)You wander into a small alley, noticing the flickering lights from the old abandoned shoppes.");
			output(" On the ground, you find a small puzzle box.");
			output(" You poke at it with your `&%s`), hoping to jar some kind of spirit.",$session['user']['weapon']);
			output(" A blinding light lifts you off of your feet and tosses you through a window.");
			output(" You hit the ground with a thud and see a tall %s.",$race);
			if ($descrip <> ""){
				output_notl("`n`n%s.",$descrip);
			}else{
			}
			output(" He takes your hand and lifts you effortlessly.`n`n");
			output("\"`2Hello, my name is %s`2... how may I be of service to you?`)\" he asks.",$name);
			output(" You see a small pamphlet and see that %s`2 will refresh your magic.",$name);
			output(" Cost is only `^%s `)gold.`n", $cost);

			if ($session['user']['gold']>=$cost) {
				addnav(array("Refresh Skills - %s Gold",$cost),"runmodule.php?module=skillshop&op=refresh");
			}else{
				output("`n`@You need `^%s `@more gold to do this.",$cost - $gold);
			}
			if ($uses >= $amt){
				output("`n`)%s`) stares at the magical aura about you.",$name);
				output("\"`2You do not need to refresh today...`)\"");
				blocknav("runmodule.php?module=skillshop&op=refresh");
			}
		}else{
			output("`)%s`) stares at you, \"`2You need to come back at the newday... my powers are weak now from helping you earlier...`)\"",$name);
		}
	} elseif ($op == "refresh" && ($refresh >= $max || $session['user']['gold'] < $cost)) {
		// The same checks as the link: refreshes left today and the gold to pay.
		output("`)%s`) stares at you, \"`2You need to come back at the newday... my powers are weak now from helping you earlier...`)\"",$name);
	} elseif ($op == "refresh"){
		$specialties = modulehook("specialtymodules");
		if ($session['user']['specialty']=='SS') {
			$bonus = getsetting("specialtybonus", 1);
			set_module_pref("uses",0-$bonus,"specialtysystem");
			set_module_pref("cache",'',"specialtysystem");
		} else {
			foreach ($specialties as $key=>$specmodule) {
				$amt = (int)(get_module_pref("skill", $specmodule) / 3);
				if ($session['user']['specialty'] == $key) $amt++;
				set_module_pref("uses", $amt, $specmodule);
			}
		}
		$session['user']['gold']-=$cost;
		set_module_pref("refresh",$refresh+1);
		output("`)%s`) smiles and takes your `^%s `)gold.",$name,$cost);
		output("\"`2Your skills have been refreshed.`)\"");
	}

	require_once("lib/villagenav.php");
	villagenav();

	page_footer();
}
?>
