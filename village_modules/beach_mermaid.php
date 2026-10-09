<?php
if (isset($_GET['op']) && $_GET['op']=="download"){ // this offers the module on every server for download
 $dl=join("",file("beach_mermaid.php"));
 echo $dl;
}

/* This module is partly based on the forest fairy written be Eric Stevens. Players can throw gems in a pond and have a 50% chance to get something good. Additionally a counter counts the donated gems, which will be used in e.g. the Water Dragon event.*/

function beach_mermaid_getmoduleinfo(){
	$info = array(
		"name"=>"Mermaid's Rock  (Beach)",
		"version"=>"1.0",
		"author"=>"eph",
		"category"=>"Village|Beach",
		"download"=>"modules/beach_mermaid.php?op=download",
		"settings"=>array(
			"Mermaid Pond Settings,title",
			"carrydk"=>"Do max hitpoints gained carry across DKs?,bool|1",
			"hptoaward"=>"How many HP are given by the Pond?,range,1,5,1|1",
			"fftoaward"=>"How many FFs are given by the Pond?,range,1,5,1|1",
			"perday"=>"How many gems may a player donate per day?,range,1,5,1|1",
		),
		"prefs"=>array(
			"Mermaid Pond User Preferences,title",
			"extrahps"=>"How many extra hitpoints has the user gained?,int",
			"gemstotal"=>"How many gems has the user donated overall?,int",
			"tries"=>"How many gems has the user donated today?,int",
		),
	);
	return $info;
}

function beach_mermaid_install(){
	if (!is_module_installed("beach")) {
    output("This module requires the Beach Resort module to be installed.");
    return false;
	}
	module_addhook("beach");
	module_addhook("newday");
	module_addhook("hprecalc");
	return true;
}

function beach_mermaid_uninstall(){
	return true;
}

function beach_mermaid_dohook($hookname,$args){
	switch($hookname){
	case "beach":
		addnav("At the Beach");
		addnav("Mermaid's Rock", "runmodule.php?module=beach_mermaid");
		break;
	case "newday":
		set_module_pref("tries",0);
		break;
	case "hprecalc":
		$args['total'] -= get_module_pref("extrahps");
		if (!get_module_setting("carrydk")) {
			$args['extra'] -= get_module_pref("extrahps");
			set_module_pref("extrahps", 0);
		}
		break;
	}
	return $args;
}

function beach_mermaid_run(){
	require_once("lib/increment_specialty.php");
	global $session;
	page_header("The Mermaid");
	output("`Q`c`bThe Mermaid`b`c`n");
//	rawoutput("<img src='modules/images/eph/mermaid.png' align='left'>");

	$perday=get_module_setting("perday");
	$tries=get_module_pref("tries");
	$gemstotal = get_module_pref("gemstotal");
	$op=httpget('op');
	if ($op==""){
		output("`^On a rock close to the shore sits a mermaid. Her fin casually splashes in the water as she plays a song on a harp. You stop to listen to the music, and from her song you understand that the water dragon has blessed this part of the ocean. From here she watches over the people of the land and grants them occasional favours.`n`n");
		output("Since the dawn of time humans and other races made sacrifices to the mighty dragon here. They say she is especially fond of gems.`n`n");
		output("When the song ends the mermaid looks at you in silence.`n`n");
		output("What do you do?");
		addnav("Throw a gem in the sea", "runmodule.php?module=beach_mermaid&op=give");
		addnav("Don't throw a gem", "runmodule.php?module=beach_mermaid&op=dont");
	}elseif ($op=="give"){
		addnav("Back to the beach", "runmodule.php?module=beach");
		if (($tries<$perday) && ($session['user']['gems']>0))
			{
			output("`^You take out one of your hard earned gems and hurl it into the waters.`n`n");
			output("The surface of the ocean shimmers and sparkles for a moment as your gift sinks to the ground. Then the sensation fades, yet you feel a wave of warm thoughts flowing through your head. The dragon has received your offer deep below the waves and thanks you for your kindness.`n");
			output("The mermaid just smiles and continues to play on her harp.`n`n");
			$session['user']['gems']--;
			$tries++;
			$gemstotal++;
			set_module_pref("tries",$tries);
			set_module_pref("gemstotal",$gemstotal);
			debuglog("gave 1 gem to the mermaid");
			switch(e_rand(1,25)){
			case 1: case 2: case 3:
				$extra = get_module_setting("fftoaward");
				output("`^Just as you turn to leave the water begins to glow again and you are engulfed in fine silver mist. The dragon likes your gift and sends you a favour. `n`n");
				if ($extra == 1) output("You receive an extra forest fight!");
				else output("You receive %s extra forest fights!", $extra);
				$session['user']['turns'] += $extra;
				break;
			case 4:
				output("`^Just as you turn to leave the water begins to glow again and you are engulfed in fine golden mist. The dragon likes your gift and sends you a favour. `n`n");
				$gold=$session['user']['level']*20;
				output("You gained %s gold!",$gold);
				$session['user']['gold']+=$gold;
				debuglog("gained some gold at the mermaid pond");
				break;
			case 5:
				output("`^As you turn to leave you notice a sparkle in the sand.`n`n You bend down to pick it up and find 2 gems!");
				$session['user']['gems']+=2;
				debuglog("found 2 gems at the mermaid pond");
				break;
			case 6:
				output("`^A cool breeze from the sea plays around you and your skin tickles. When you look down you see the dragon has granted you a nice tan.`n`n You gain some charm!");
				$session['user']['charm']++;
				break;
			case 7:
				$hptype = "permanently";
				if (!get_module_setting("carrydk") ||
						(is_module_active("globalhp") &&
						 !get_module_setting("carrydk", "globalhp")))
					$hptype = "temporarily";
				$hptype = translate_inline($hptype);

				$extra = get_module_setting("hptoaward");
				output("`^Just as you turn to leave the water starts to bubble. The water dragon likes your gift and sends you a favour.`n`n");
				output("Your maximum hitpoints are `b%s`b increased by %s!",
						$hptype, $extra);

				$session['user']['maxhitpoints'] += $extra;
				$session['user']['hitpoints'] += $extra;
				set_module_pref("extrahps",
						get_module_pref("extrahps")+$extra);
				break;
			case 8:
				output("`^As you turn to leave a blue mist rises from the sea and engulfes you. The water dragon likes your gift and grants you a favour.`n`n");
				increment_specialty("`^");
				break;
			case 9: case 10: case 11:
				output("`^Sparkling water drops rise from the ocean's surface and settle down on your clothes. The water dragon noticed your offer and grants you a small favour in return.`n`n");
				output("You leave with the dragon's blessing.");
				apply_buff('beach_mermaid',
				array(
					"name"=>"`#Water Dragon's Favour",
					"rounds"=>15,
					"wearoff"=>"Your clothes are dry again.",
					"defmod"=>1.02,
					"survivenewday"=>1,
					"roundmsg"=>"Water drops swirl around you and distract the enemy.",
					)
				);
				break;
			case 12: case 13: case 14:
				output("`^Sparkling water drops rise from the ocean's surface and sink into your skin. The water dragon noticed your offer and grants you a small favour in return.`n`n");
				output("You gain `#100 `^hitpoints!`n");
				$session['user']['hitpoints']+=100;
				break;
			case 15: case 16: case 17:
				output("`^Sparkling water drops rise from the ocean's surface and sink into your skin. The water dragon noticed your offer and grants you a small favour in return.`n`n");
				$exp = $session['user']['level']*40;
				output("You gain `#%s `^experience!`n",$exp);
				$session['user']['experience']+=$exp;
				break;
			case 18: case 19: case 20: case 21: case 22: case 23: case 24: case 25:
				output("`^A silent moan comes from the ground of the ocean, sending slight ripples over the water's surface. The mermaid looks at you.`n`n");
				output("\"The water dragon received your gift and sends you her profundest thanks. Keep visiting this shrine and her favour will come to you one day.\"");
				break;
			}
		}elseif ($session['user']['gems']==0)
		{
			output("`^You rummage through your purse to give a gem to the sea goddess, but you discover that you have none.`n`n");
			output("Disappointed you put away your purse and look at the mermaid.`n Obviously she acknowledges your effort, but her calm face shows no further emotion.`0`n`n");
		}else {
			output("`^When you take out a gem to throw it in the sea, the mermaid raises a hand.`n`n \"Wait!\" she says with a melodic voice. \"The water dragon appreciates your dedication to her, yet it is a bad idea to draw her attention with too many offers. Please come back tomorrow.\"`0`n`n");
		}
	}elseif ($op=="dont"){
		addnav("Back to the beach", "runmodule.php?module=beach");
		output("`^Not wanting to part with one of your precious precious gems, you decide the favour of the water dragon can't be worth the sacrifice.`0`n`n");
		$session['user']['specialinc'] = "";
	}

page_footer();
}
?>
