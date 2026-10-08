<?php

function availability_getmoduleinfo(){
	$info = array(
		"name"=>"EquipmentAvailability",
		"author"=>"Christian Rutsch",
		"version"=>"1.0",
		"category"=>"Equipment Modifications",
		"download"=>"",
		"settings"=>array(
			"Availability - Weapons,title",
				"weapon1"=>"How many level 1 weapons are currently available?,int|190",
				"weapon2"=>"How many level 2 weapons are currently available?,int|180",
				"weapon3"=>"How many level 3 weapons are currently available?,int|170",
				"weapon4"=>"How many level 4 weapons are currently available?,int|160",
				"weapon5"=>"How many level 5 weapons are currently available?,int|150",
				"weapon6"=>"How many level 6 weapons are currently available?,int|140",
				"weapon7"=>"How many level 7 weapons are currently available?,int|130",
				"weapon8"=>"How many level 8 weapons are currently available?,int|120",
				"weapon9"=>"How many level 9 weapons are currently available?,int|110",
				"weapon10"=>"How many level 10 weapons are currently available?,int|100",
				"weapon11"=>"How many level 11 weapons are currently available?,int|90",
				"weapon12"=>"How many level 12 weapons are currently available?,int|80",
				"weapon13"=>"How many level 13 weapons are currently available?,int|70",
				"weapon14"=>"How many level 14 weapons are currently available?,int|60",
				"weapon15"=>"How many level 15 weapons are currently available?,int|50",
			"Availability - Armors,title",
				"armor1"=>"How many level 1 armors are currently available?,int|190",
				"armor2"=>"How many level 2 armors are currently available?,int|180",
				"armor3"=>"How many level 3 armors are currently available?,int|170",
				"armor4"=>"How many level 4 armors are currently available?,int|160",
				"armor5"=>"How many level 5 armors are currently available?,int|150",
				"armor6"=>"How many level 6 armors are currently available?,int|140",
				"armor7"=>"How many level 7 armors are currently available?,int|130",
				"armor8"=>"How many level 8 armors are currently available?,int|120",
				"armor9"=>"How many level 9 armors are currently available?,int|110",
				"armor10"=>"How many level 10 armors are currently available?,int|100",
				"armor11"=>"How many level 11 armors are currently available?,int|90",
				"armor12"=>"How many level 12 armors are currently available?,int|80",
				"armor13"=>"How many level 13 armors are currently available?,int|70",
				"armor14"=>"How many level 14 armors are currently available?,int|60",
				"armor15"=>"How many level 15 armors are currently available?,int|50",
			"Availability - Other settings,title",
				"purchasesame"=>"Are players allowed to purchase equipment they already own?,bool|1",
		),
		"prefs"=>array(
			"prices"=>"Last valid prices,viewonly|",
		)
	);
	return $info;
}

function availability_install(){
	module_addhook("newday-runonce");
	module_addhook("footer-weapons");
	module_addhook("footer-armor");
	module_addhook_priority("modify-weapon", INT_MAX-1);	// Need to have them as last module, as other modules might
	module_addhook_priority("modify-armor", INT_MAX-1);	// change the price of weapons and we need to check the
	return true;										// final price.
}

function availability_uninstall(){
	return true;
}

function availability_dohook($hookname,$args){
	global $session;
	static $notavail = false;
	static $price_array = false;
	switch ($hookname) {
		case "modify-weapon":
			$damage = $args['damage'];
			$setting = "weapon$damage";
			$amount = get_module_setting($setting);
			if ($amount<=0) {
				$args['unavailable'] = true;
				$args['alternatetext'] = translate_inline("This weapon is currently not available.");
				$notavail = true;
			} elseif ($amount<50 && httpget('op') == '') {
				//make it more expensive
				//but not for small fries
				if ($session['user']['dragonkills']>2) {
					if (httpget('op') != 'buy'){
						$args['weaponname'].=sprintf_translate(" (only %s left!)",$amount);
					}
					$price=round($args['value']*(1+($amount*$amount*0.004-$amount*0.4+10)),0);
					$args['value']=$price;
				}
			} else if (httpget('op') == 'buy') { // we are buying. revert prices.
				if ($price_array === false) {
					$price_array = unserialize(get_module_pref('prices'));
				}
				if (is_array($price_array) && isset($price_array[$damage])) {
					$args['value'] = $price_array[$damage];
					$price = $price_array[$damage];
				}
				$tradeinvalue = round(($session['user']['weaponvalue']*.75),0);
				if ($args['value']<=($session['user']['gold']+$tradeinvalue)){
					increment_module_setting($setting, -1);
				}
			} else {
				$price = $args['value'];
			}
			if (get_module_setting("purchasesame") == false) {
				if ($damage == $session['user']['weapondmg']) {
					$args['unavailable'] = true;
					$args['alternatetext'] = translate_inline("You already have a weapon of this strength.");
				}
			}

			if (httpget('op') == '') {
				if ($price_array === false) {
					$price_array = array($damage => $price);
				} else {
					// this does the same as the replaced expression, where $price was not defined but set to price_array
					if (!isset($price)) {
						$price_array[$damage] = 0;
					} else {
						$price_array[$damage] = $price;
					}
				}
				if ($damage >= 15) {
					set_module_pref("prices", serialize($price_array));
				}
			}
			break;
		case "modify-armor":
			$damage = $args['defense'];
			$setting = "armor$damage";
			$amount = get_module_setting($setting);
			if ($amount<=0) {
				$args['unavailable'] = true;
				$args['alternatetext'] = translate_inline("This armor is currently not available.");
				$notavail = true;
			} elseif ($amount<50 && httpget('op') == '') {
				//make it more expensive
				//but not for small fries
				if ($session['user']['dragonkills']>2) {
					if (httpget('op') != 'buy'){
						$args['armorname'].=sprintf_translate(" (only %s left!)",$amount);
					}
					$price=round($args['value']*(1+($amount*$amount*0.004-$amount*0.4+10)),0);
					$args['value']=$price;
				}
			} else if (httpget('op') == 'buy') { // we are buying. revert prices.
				if ($price_array === false) {
					$price_array = unserialize(get_module_pref('prices'));
				}
				if (is_array($price_array) && isset($price_array[$damage])) {
					$args['value'] = $price_array[$damage];
					$price = $price_array[$damage];
				}
				$tradeinvalue = round(($session['user']['armorvalue']*.75),0);
				if ($args['value']<=($session['user']['gold']+$tradeinvalue)){
					increment_module_setting($setting, -1);
				}
			} else {
				$price = $args['value'];
			}

			if (get_module_setting("purchasesame") == false) {
				if ($damage == $session['user']['armordef']) {
					$args['unavailable'] = true;
					$args['alternatetext'] = translate_inline("You already have an armor of this strength.");
				}
			}

			if (httpget('op') == '') {
				if ($price_array === false) {
					$price_array = array($damage => $price);
				} else {
					// this does the same as the replaced expression, where $price was not defined but set to price_array
					if (!isset($price)) {
						$price_array[$damage] = 0;
					} else {
						$price_array[$damage] = $price;
					}
				}
				if ($damage >= 15) {
					set_module_pref("prices", serialize($price_array));
				}
			}
			break;
		case "footer-weapons":
			if ($notavail === true) {
				output("Some of the weapons are currently not stocked.");
				output("Please check again tomorrow.");
			}
			break;
		case "footer-armor":
			if ($notavail === true) {
				output("Some of the armors are currently not stocked.");
				output("Please check again tomorrow.");
			}
			break;
		case "newday-runonce":
			$weapons=array();
			$armor=array();
			for($i=1;$i<=15;++$i) {
				// A fifth of the initial stock + the current stock but not more than the initial stock.
				$currentstock = get_module_setting("weapon$i");
				$newstock = min(ceil((220 - ($i-1)*10)/2) + $currentstock,(220 - ($i-1)*10));
				set_module_setting("weapon$i", $newstock);
				$weapons[]=$newstock;
				$currentstock = get_module_setting("armor$i");
				$newstock = min(ceil((220 - ($i-1)*10)/2) + $currentstock,(220 - ($i-1)*10));
				set_module_setting("armor$i", $newstock);
				$armor[]=$newstock;
			}
			require_once("lib/gamelog.php");
			gamelog("Restocked Weapons to: ".implode(",",$weapons));
			gamelog("Restocked Armours to: ".implode(",",$armor));
			break;
	}
	return $args;
}
?>
