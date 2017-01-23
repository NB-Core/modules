<?php

function sympathy_getmoduleinfo(){
	$info = array(
		"name"=>"Sympathy Buy",
		"author"=>"`2Oliver Brendel",
		"version"=>"1.0",
		"category"=>"Village",
		"download"=>"",
		"settings"=>array(
			"name"=>"Name of the female owner, text|`tK`jitty",
			),
		"prefs"=>array(
			"hadsympathy"=>"date he last had sympathy,viewonly",
			),
	);
	return $info;
}

function sympathy_install(){
	module_addhook_priority("village-Konohagakure",50);
	return true;
}
function sympathy_uninstall(){
	return true;
}

function sympathy_dohook($hookname,$args){
	global $session;
	switch($hookname){
	case "village-Konohagakure":
		$name=get_module_setting('name');
		addnav($args['marketnav']);
		addnav(array(" ?%s`l's Sympathy Shop",$name),"runmodule.php?module=sympathy");
		break;
	}
	return $args;
}

function sympathy_run(){
	global $session;
	$op = httpget("op");
	require_once("modules/addimages/addimages_func.php");
	$hadsympathy=get_module_pref('hadsympathy');
	$cost=$session['user']['level']*10;
	$pity=array(
		"Awwwww, poor puppy, did the booboo man hurt you?",
		"I am so sorry you exist... really... without that, you would have so much less pain...",
		"Cheer up... poor you... the world is mean, I feel like give her a beating!",
		"That can't be you... you should be a illustrous figure in the world...",
		"We share the same fate... I too was once ugly...",
		"Poor kitten, what did the big bad buggers do  to you?",
		"AwwwwwwwwwwwwwwwwwwwwwwwwwwwwwWWWWWWWWWWWWWWWWWWWWWwwwwwwwwwwwwwwwwwwwwwwwww",
		"You don't look so sad..."
		);
	$name=get_module_setting('name');
	$get=array_rand($pity);
	$choice=$pity[$get];
	if ($hadsympathy!=date("Y-m-d")) $canbuy=1;
		else $canbuy=0;
	page_header(array("%s`l's Sympathy Shop",$name));
	output("`b`i`c`l%s`l's Sympathy Shop`c`i`b`n",$name);
	addnav("Navigation");
	villagenav();
	switch($op) {
		case "pityme":
			addimage("sympathy/kitty_sad.gif");
			output("`xYou approach %s`x and ask her for her pity as you feel `\$veeeery miserable`x today and need a bit of pity to get over it.`n`n%s`x looks you deep in the eyes, and says with a look full of sorrow and care, \"`R%s`x\"`n`n",$name,$name,$choice);
			$session['user']['gold']-=$cost;
			set_module_pref('hadsympathy',date("Y-m-d"));
			switch (e_rand(0,49)) {
				case 7:
					output("`4You feel great! And with all your greatness, you find a `%gem`4 outside on the street!");
					$session['user']['gems']++;
					break;
				case 49:
					output("`4You feel sexy! And with that thoughts in mind you wander back, a bit more `%charming`4!");
					$session['user']['charm']++;
					break;
				default:
					output("`lYou feel only slightly better... but well... it was worth a try, you are too much into self-pity after all, don't you think?");
					break;
			}
			break;
		default:
			addimage("sympathy/kitty.gif");
			output("`xYou enter a tiny little shop that has an obscure banner outside: A trinket with a sad eye painted on it... you wonder what you can expect from it...`n`nAs you enter, a beautiful female nin with long brown hair approaches you, \"`RHi, my name is %s`R, how may I help you? If you wonder what I can offer... I can offer you sympathy... for a price.`x\"`n`nYou really wonder what that means, but it seems she means it literally...`n`n",$name);
			if ($session['user']['gold']<$cost) {
				output("You should really bring more gold!");
				addnav("Sympathy");
				addnav(array("Get some sympathy (%s gold)",$cost),"");				
			} elseif (!$canbuy) {
				output("\"`RSadly, I pitied you already today, come back tomorrow please *wink*`x\"");
			} else {
				addnav("Sympathy");
				addnav(array("Get some sympathy (%s gold)",$cost),"runmodule.php?module=sympathy&op=pityme");
			}
			break;
			
	}
	page_footer();
}
?>
