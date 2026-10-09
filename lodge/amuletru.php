<?php

// Heavily modified from base Storm Giant module code

function amuletru_getmoduleinfo(){
	$info = array(
		"name"=>"Amulet of Ru",
		"version"=>"1.02",
		"author"=>"Enhas, based on racestorm by Chris Vorndran",
		"category"=>"Lodge",
		"download"=>"http://dragonprime.net/users/Enhas/amuletru.txt",
		"settings"=>array(
			"Amulet of Ru Settings,title",
			"days"=>"Number of game days the amulet will last,range,1,10,1|3",
			"cost"=>"Amulet cost in Lodge / Donation points,int|150",
                  "trainapply"=>"Will the amulet help in training / master fights,bool|1",
                  "pvpapply"=>"Will the amulet help in PvP fights,bool|1",
		),
		"prefs"=>array(
			"Amulet of Ru Preferences,title",
			"hasamulet"=>"Does this player have an amulet,bool|0",
                  "amuletdays"=>"Number of days this player has had the amulet,int|0",
		)
	);
	return $info;
}

function amuletru_install(){
	module_addhook("newday");
	module_addhook("pointsdesc");
	module_addhook("lodge");
	return true;
}

function amuletru_uninstall(){
	return true;
}

function amuletru_dohook($hookname,$args){
	global $session;
	$cost = get_module_setting("cost");
	$days = get_module_setting("days");
      $hasamulet = get_module_pref("hasamulet");
      $amuletdays = get_module_pref("amuletdays");
	switch($hookname){
	case "pointsdesc":
		$args['count']++;
		$format = $args['format'];
		$str = translate("An Amulet of Ru, which has random effects on the wearer.  This costs %s points.");
		$str = sprintf($str, $cost);
		output($format, $str, true);
		break;
	case "lodge":
		if (!$hasamulet) {
			addnav(array("Buy an Amulet of Ru (%s points)",$cost),
					"runmodule.php?module=amuletru&op=start");
		}
		break;
	case "newday":
		$atkbuff = e_rand(5,10);
            $defbuff = (15 - $atkbuff);
            $ffmod = e_rand(1,3);        

            if ($hasamulet) {
            $amuletdays++;
            set_module_pref("amuletdays",$amuletdays);
            }

            if ($hasamulet && ($amuletdays > $days)) {
            set_module_pref("hasamulet",0);
	    debuglog("Discharged Amulet of Ru");
            set_module_pref("amuletdays",0);
            output("`n`n`2Your Amulet of Ru has vanished!  Unpredictable power indeed..`n`0");

            }elseif ($hasamulet && ($amuletdays <= $days)) {
            output("`n`n`2You feel a strange tingling coming from your neck, from where your Amulet of Ru is hanging..`n`0");
            $session['user']['turns']+=$ffmod;

            if ($ffmod==1) {
            output("`2The power flows through you.. and you gain the power to slay `^another`2 creature in the `@forest`2 today!`n`0");

            }else{
            output("`2The power flows through you.. and you gain the power to slay `^%s`2 more creatures in the `@forest`2 today!`n`0", $ffmod);
            }

			apply_buff("amuletbuff",array(
				"name"=>"`2Amulet of Ru`0",
                        "atkmod"=>(1+($atkbuff  / 100)),
				"defmod"=>(1+($defbuff  / 100)),
				"rounds"=>-1,
                        "allowinpvp"=>get_module_setting("pvpapply"),
				"allowintrain"=>get_module_setting("trainapply"),
				"schema"=>"module-amuletru",
				)
			);
		}
		break;
	}

	return $args;
}

function amuletru_run(){
	global $session;
	page_header("Hunter's Lodge");
	$cost = get_module_setting("cost");
	$days = get_module_setting("days");
      $hasamulet = get_module_pref("hasamulet");
      $amuletdays = get_module_pref("amuletdays");
	$op = httpget('op');

	switch ($op){
		case "start":
			$pointsavailable = $session['user']['donation'] -
			$session['user']['donationspent'];
			if ($pointsavailable >= $cost && $hasamulet == 0){
				output("`2J. C. Petersen opens a small, pewter box.  Inside are a few small amulets with strange runes upon them..`n`n");
				output("`\$These are very rare, and mysterious.  Known as `2Amulet of Ru`\$, they are a bit unpredictable but are known to strengthen the wearer.  You might wake up one day and find it gone though, they seem to have a mind of their own.. anyway, do you wish to purchase one?");
				addnav("Choices");
				addnav("Yes","runmodule.php?module=amuletru&op=yes");
				addnav("No","runmodule.php?module=amuletru&op=no");
			} else {
				output("`2J. C. Petersen stares at you for a moment then looks away as you realize that you don't have enough points to purchase this item.");
			}
			break;
		case "yes":
			output("`2J. C. Petersen reaches into the box, and pulls out a small green amulet.`n`n");
			output("`\$'This will serve you well in your travels.'`n`n");
			output("`2You place the amulet around your neck, admiring the runes.`n`n");
			output("J. C. Petersen tells you, `\$'The power of the amulet will only show itself on newday.  Thank you for your purchase!'");
			$session['user']['donationspent'] += $cost;
			debuglog("Bought Amulet of Ru for $cost points.");
			set_module_pref("hasamulet",1);
			break;
		case "no":
			output("`2J. C. Petersen glances up at you from the box.`n`n");
			output("`\$This is no ordinary trinket.. you'd be wise to purchase one at some point.`2");
			break;
	}
	addnav("Return");
	addnav("L?Return to the Lodge","lodge.php");
	page_footer();
}	
?>
