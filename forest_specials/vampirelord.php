<?php

function vampirelord_getmoduleinfo(){
	$info = array(
		"name"=>"Vampire's Lair",
		"version"=>"1.1",
		"author"=>"Mike Counts, conversion by XChrisX, translated back by `2Oliver Brendel",
		"category"=>"Forest Specials",
		"download"=>"",
		"settings"=>array(
			"lifecost" => "How much maxhp does the vampire take?, int|5",
			"gemgain" => "Divide lifecost by this to receive gems, int|2",
			"goldgain" => "Multiply lifecost by this to receive gold, int|100",
			"bonus"=> "How many points to atk-def as bonus from the vampire for 1 drain, int|2",
			"vampirelord"=> "Name of the VampireLord, text|`4Vampire`!Lord",
			"anysuck"=>"Suck away a part of the 'too high' hp in any case... even when the player offers hp?,bool|1",
		),
	);
	return $info;
}

function vampirelord_chance($type){
	global $session;
	$dkhp=0;
	foreach($session['user']['dragonpoints'] as $val){
		if ($val=="hp") $dkhp++;
	}
	$maxhp=vampirelord_maxhp($dkhp);
	$minhp=10*$session['user']['level']+5*$dkhp;

	if ($session['user']['maxhitpoints'] < $minhp)
		return 0;
	else
		$type=="forest"?$chance = 100:$chance = 20;
	return $chance;

}

function vampirelord_install(){
	module_addeventhook("forest", "require_once(\"modules/vampirelord.php\"); return vampirelord_chance(\"forest\");");
	module_addeventhook("travel", "require_once(\"modules/vampirelord.php\"); return vampirelord_chance(\"travel\");");
	return true;
}

function vampirelord_uninstall(){
	return true;
}

function vampirelord_dohook($hookname,$args){
	return $args;
}

function vampirelord_runevent($type, $link){
	global $session;
	$vampirelord=get_module_setting("vampirelord");
	$dkhp=0;
	foreach($session['user']['dragonpoints'] as $val){
		if ($val=="hp") $dkhp++;
	}

	$allowedhpperdk = 6;
	$maxhp=vampirelord_maxhp($dkhp);
	$minhp=10*$session['user']['level']+5*$dkhp;
	debug($maxhp);debug($minhp);
	$lifecost = (int)get_module_setting("lifecost");
	$gemgain = round($lifecost/(int)get_module_setting("gemgain"));
	$goldgain = $lifecost*(int)get_module_setting("goldgain");
	$session['user']['specialinc'] = "module:vampirelord";

	$op = httpget('op');

	page_header("Something Special");

	switch($op) {
	case "continue":
		output("`^`c`bThe Hideout Of The %s`b`c",$vampirelord);
		output("`n`n`7An evil entity manifests before you. You cower in fear, bowed down by an ancient power... and hear its words: ");
		output("\"`\$Mortal, I feel quite some life essence in you... with the age my will to hunt grew small.`n");
		output("In exchange for a bit of your permanent life essence I will give you power beyond your imagination.`7\"");
		output(" Finally you recognize what stands before you... an old vampire waiting for your answer..");
		if($session['user']['maxhitpoints']>$lifecost){
			addnav(array("Offer %s hitpoints for attack", $lifecost),$link."op=str");
			addnav(array("Offer %s hitpoints for defense", $lifecost),$link."op=def");
			addnav(array("Offer %s hitpoints for wealth", $lifecost),$link."op=wealth");
			modulehook("vampirelord_offering",array("cost"=>$lifecost));
		} else{
			addnav("You don't have enough life essence");
		}
		addnav("Flee in terror",$link."op=leave");
		break;
	case "leave":
		$did=vampirelord_suck();
		if ($did==true) {
			if (get_module_pref('user_addimages','addimages')) {
				rawoutput("<center><img src='modules/vampirelord/vl.jpg' alt'Baluski'></center><br><br>");
			}
		}
		output("`n`7You leave this foul place as fast as you can.");
		$session['user']['specialinc'] = "";
		break;
	case "str": case "def": case "wealth":
		output("`^`c`bThe Hideout Of The %s`b`c",$vampirelord);
		if (($session['user']['maxhitpoints']-$lifecost)<$minhp) {
			output("`n`7The %s`7 takes a deep look at you and tells you that your life essence is not strong enough to feed him. He lets you go untouched and without any reward.",$vampirelord);
		} else {
			$session['user']['maxhitpoints'] -= $lifecost;
			if($session['user']['hitpoints']>$session['user']['maxhitpoints']) $session['user']['hitpoints']=$session['user']['maxhitpoints'];
			if (get_module_pref('user_addimages','addimages')) {
				rawoutput("<center><img src='modules/vampirelord/vl.jpg' alt'Baluski'></center><br><br>");
			}
			output("`n`n`7You shudder in horror as the %s`7 forces his teeth into you neck. You feel your life essence draining as the blood gets sucked out...",$vampirelord);
			output("`nAs a reward the %s`7 casts an ancient spell upon you... you would hardly call it words what he 'says'...`n`n`@",$vampirelord);
			$add=get_module_setting("bonus");
			if($op == "str") {
				$session['user']['attack']+=$add;;
				output("Your attack increases temporarily by `^%s`@ points and you lose `\$%s `@permanent hitpoints",$add, $lifecost);
			} else if($op == "def") {
				$session['user']['defense']+=$add;
				output("Your defense increases temporarily by `^%s`@ points and you lose `\$%s `@permanent hitpoints",$add , $lifecost);
			} else if($op == "wealth") {
				$session['user']['gold'] += $goldgain;
				$session['user']['gems'] += $gemgain;
				output("For your `\$%s`@ permanent hitpoints the %s`7 gives you `^%s `@gold and `#%s `@gems.", $lifecost, $vampirelord, $goldgain, $gemgain);
			}
			debuglog("lost $lifecost to the vampire and received $op!");
			if (get_module_setting("anysuck")) {
				output_notl("`n`n`q");
				vampirelord_suck(); //suck after a deal
				output_notl("`n`n`q");
			}
			$session['user']['specialinc']="";
		}
		break;
	default:
		output("`^`c`bA dark path`b`c");
		output("`n`n`7You see a dark and hidden path lying before you. Thick mist covers the plants around you and you feel horror and emptiness. Do you dare to face what comes along this way?");
		if ($session['user']['maxhitpoints']>$maxhp){
			output("`n`nYou feel that you won't leave here unharmed... it gives you the creeps...");
		}		
		addnav("Walk bravely down the path",$link."op=continue");
		addnav("Flee in terror",$link."op=leave");
	}
}

function vampirelord_run(){
	global $session;
	vampirelord_suck();
	$session['user']['specialinc'] = "";
	forest(true);
	page_footer();
}

function vampirelord_suck(){
	global $session;
	$dkhp=0;
	foreach($session['user']['dragonpoints'] as $val){
		if ($val=="hp") $dkhp++;
	}

	$maxhp=vampirelord_maxhp($dkhp);
	$minhp=10*$session['user']['level']+5*$dkhp;

	$lifecost = (int)get_module_setting("lifecost");
	$gemgain = round($lifecost/(int)get_module_setting("gemgain"));
	$goldgain = $lifecost*(int)get_module_setting("goldgain");

	$op = httpget('op');

	if ($session['user']['maxhitpoints']>$maxhp){
		$losthp=vampirelord_losehp($maxhp);
		$exp=$losthp*100;
		$session['user']['maxhitpoints']-=$losthp;
		if ($session['user']['hitpoints']>$session['user']['maxhitpoints']) $session['user']['hitpoints']=$session['user']['maxhitpoints'];
		$session['user']['experience']+=$exp;
		output("Hungry and heavily tempted by the smell of your enormous life essence the vampire cannot hold back any longer... he overcomes you and sucks out your blood.");
		output(" After a few minutes he is finally satisfied and vanishes as suddenly into the forest as he appeared.`n`nYou have `bpermanently`b lost `\$%s`7 hitpoints.", $losthp);
		output("`nBut you learned your lesson and receive `^%s`7 experience points.", $exp);
        debuglog("lost $losthp lifepoints to the vampirelord(maxhp $maxhp, minhp $minhp)!");
		if ($session['user']['turns']>0) {
			output("`nYou feel empty and lose one forest fight.");
			$session['user']['turns']--;
		} else if ($session['user']['charm'] > 0) {
			output("`nDue to the bite on your neck you feel `\$less `%charming`7.");
			$session['user']['charm']--;
		}
		require_once("lib/addnews.php");
		addnews("`%%s`7 had a grave encounter with the %s`7 in the forest.", $session['user']['name'],get_module_setting("vampirelord"));
		return true;
	}
	return false;
}

function vampirelord_losehp($maxhp) {
	global $session;
	$loss= e_rand(($session['user']['maxhitpoints']-$maxhp)/3,$session['user']['maxhitpoints']-$maxhp);
	if ($loss>60) $loss=e_rand(47,63);
	return $loss;
}

function vampirelord_maxhp($dkhp) {
	global $session;
	return (8*$session['user']['dragonkills']+12*$session['user']['level']+5*$dkhp);
}
?>
