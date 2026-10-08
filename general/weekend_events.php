<?php

function weekend_events_getmoduleinfo(){
	$info = array(
			"name"=>"Weekend Events",
			"author"=>"Oliver Brendel",
			"version"=>"1.0",
			"category"=>"Events",
			"download"=>"",
			"settings"=>array(
				"Settings,title",
				"pvpbonus"=>"PVP bonus fights,range,1,10,1|8",
				"atkbonus"=>"atk bonus in percent,range,1,100,1|15",
				"turnbonus"=>"Turn bonus,range,1,30,1|10",
				"goldbonus"=>"Gold bonus per level,range,1,200,5|100",
				"maxhp_pc"=>"Max HP Heal from Weekend Warrior in percent,range,0,100,2|12",
				)
		     );
	return $info;
}

/**
 * Determine the event for the current weekend.
 *
 * Friday, Saturday, and Sunday belonging to the same weekend must return the
 * same event number, based on that weekend's Friday.
 *
 * @return int|false The Friday's ordinal occurrence in its month, or false on weekdays.
 */
function weekend_events_check()
{
    $currentTimestamp = time();
    $dayOfWeek = (int) date("N", $currentTimestamp);

    if ($dayOfWeek < 5) {
        return false;
    }

    // Anchor Saturday and Sunday to the preceding Friday before checking the
    // month. Thus July 31-August 2, 2026 all use Friday, July 31 as the anchor.
    $daysSinceFriday = $dayOfWeek - 5;
    $fridayTimestamp = strtotime("-{$daysSinceFriday} days", $currentTimestamp);

    // The event number is Friday's ordinal occurrence within Friday's month;
    // July 31, 2026 is the fifth Friday, so that whole weekend is event 5.
    $fridayDayOfMonth = (int) date("j", $fridayTimestamp);

    return (int) ceil($fridayDayOfMonth / 7);
}


function weekend_events_install(){
	module_addhook("newday");
	module_addhook("village-desc");
	return true;
}

function weekend_events_uninstall(){
	return true;
}

function weekend_events_dohook($hookname,$args){
	global $session;
	$event_up = weekend_events_check();
	switch($hookname){
		case "newday":
			//remove the last weekend warrior
			$remove = "weekend-warrior";
			strip_companion($remove);
			if ($event_up!==false) {
				output("`n`vWeekend Event is running!`n");
				switch ($event_up) {
					case 1: //first weekend
						output("`4It's the `!PVP Weekend!`4`nYou get `\$%s more PVP fights!`4`n",get_module_setting('pvpbonus'));
						$session['user']['playerfights']+=get_module_setting('pvpbonus');
						break;
					case 2: //second weekend
						output("`4It's the `tForest Weekend!`4`nYou get `\$%s more forest fights!`4`n",get_module_setting('turnbonus'));
						$session['user']['turns']+=get_module_setting('turnbonus');
						break;
					case 3: //third weekend
						output("`4It's the `xGolden Weekend!`4`nYou receive `\$%s gold!`4`n",$session['user']['level']*get_module_setting('goldbonus'));
						$session['user']['gold']+=$session['user']['level']*get_module_setting('goldbonus');
						break;
					case 4: //fourth weekend
						output("`4It's the `vFierce Weekend!`4`nYou get `\$%s %% more attack power!`4`n",get_module_setting('atkbonus'));
						apply_buff('weekend_buff',
								array(
									"name"=>"`vFierce Weekend!",
									"rounds"=>-1,
									"wearoff"=>"You feel a bit weaker after the weekend.",
									"atkmod"=>1+(get_module_setting('atkbonus')/100),
									"schema"=>"module-weekend_events",
								     )
							  );
						break;
					case 5: //fifth weekend
						output("`4It's the `vBroSis Weekend!`4`nYou get a helpful royal nin for a while!`4`n");
						$name = sprintf_translate("`@Weekend Warrior %s",$session['user']['sex']==SEX_FEMALE?"Princess":"Prince");
						apply_companion("weekend-warrior" , array(
									"name"=>$name,
									"hitpoints"=>max($session['user']['maxhitpoints']/2,10),
									"maxhitpoints"=>max($session['user']['maxhitpoints']/2,10),
									"attack"=>floor($session['user']['attack']*2),
									"defense"=>floor($session['user']['defense']),
									"jointext"=>"\"I hope you're having a nice weekend!\"",
									"companionactive"=>1,
									"dyingtext"=>"`@\"Urgent matters call... you have to go on alone...\"... and the royalty vanishes.",
									"cannotbehealed"=>false,
									"expireafterfight"=>0,
									"ignorelimit"=>true,
									"abilities"=>array(
										"fight"=>1,
										"heal"=>max(5,floor($session['user']['maxhitpoints']*get_module_setting('maxhp_pc')/100)),
										"defend"=>1,
										),							
									), true);			
						break;
				}
			}
			break;
		case "village-desc":
			if ($event_up!==false) {
				output("`n`c`vWeekend Event is running!`n");
				switch ($event_up) {
					case 1: //first weekend
						output("`4It's the `!PVP Weekend!`0");
						break;
					case 2: //second weekend
						output("`4It's the `tForest Weekend!`0");
						break;
					case 3: //third weekend
						output("`4It's the `xGolden Weekend!`0");
						break;
					case 4: //fourth weekend
						output("`4It's the `vFierce Weekend!`0");
						break;
					case 5: //fifth weekend
						output("`4It's the `\$BroSis Weekend!`0");
						break;
				}
				output("`c`n");
			}
			break;
	}	

	return $args;
}

function weekend_events_runevent($type){
}

function weekend_events_run(){
}

?>
