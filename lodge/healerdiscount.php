<?php

function healerdiscount_getmoduleinfo(){
	$info = array(
			"name"=>"Healer Discount",
			"version"=>"1.0",
			"author"=>"Billie Kennedy",
			"category"=>"Lodge",
			"download"=>"http://nuketemplate.com/modules.php?name=Downloads&d_op=viewdownload&cid=33",
			"vertxtloc"=>"http://dragonprime.net/users/Dannic/",
			"settings"=>array(
				"discount"=>"What percentage of a discount should the player get for the donations?,int|5",
				"days"=>"How many days of discounts?,int|5",
				"cost"=>"How much does this cost in points?,int|50",
			),
			"prefs"=>array(
			"Healer Discount User Preferences,title",
			"daysleft"=>"How many days are left?,int|0",
			),
		);
	return $info;
}

function healerdiscount_install(){
	module_addhook("healmultiply");
	module_addhook("lodge");
	module_addhook("pointsdesc");
	module_addhook("newday");
	module_addhook("backpack");
	return true;
}

function healerdiscount_uninstall(){
	return true;
}

function healerdiscount_dohook($hookname,$args){
	global $session;
	$cost = get_module_setting("cost");
	$days = get_module_setting("days");
	$daysleft = get_module_pref("daysleft");
	$discount = get_module_setting("discount");
	$discount = (1 - ($discount/100));
	
	switch($hookname){
		case "backpack":
			if(get_module_pref("daysleft") > 0){
				output("A small wooden nickle`n");
			}
		break;
		case "healmultiply":
			if($daysleft > 0){
				output("The old one notices the special tolken you have.`n");
				output("\"You carry my special mark.  For that I will give you a discount.\", they remark.`n");
				$args['alterpct'] *= $discount;
			}
		break;
		case "lodge":
			addnav(array("Healing Discount(%s points)",$cost),"runmodule.php?module=healerdiscount&op=discount");
		break;
		case "newday":
			if(get_module_pref("daysleft") > 0){
				if(get_module_pref("daysleft")==1){
					output("You can no longer see the image of the healer on the nickle and decide that you will throw it away today.`n");
				}else{
					output("The image seems to have rubbed off the wooden nickle.`n");	
				}
				set_module_pref("daysleft", $daysleft - 1);		
			}
		break;
		case "pointsdesc":
			$discount = get_module_setting("discount");
			$args['count']++;
			$format = $args['format'];
			$str = translate("Get a discount for healing of %s percent for %s days.");
			$str = sprintf($str, $discount, $days);
			output($format, $str, true);
		break;
	}
	
	return $args;
}

function healerdiscount_run(){
	global $session;
	$op = httpget("op");
	$cost = get_module_setting("cost");
	$days = get_module_setting("days");
	$discount = get_module_setting("discount");
	$pointsavailable = $session['user']['donation'] - $session['user']['donationspent'];
	
	page_header("Hunter's Lodge");
	if ($op=="discount"){
		addnav("L?Return to the Lodge","lodge.php");
		output("`7You approach someone who slightly resembles an old, dirty rock.`n`n");
		output("\"`&If you wish, I can give you a discount on healing for %s days with only %s points.\"`7, they say.", $days,$cost);
		addnav("Confirm Healing Discount");
		addnav("Yes", "runmodule.php?module=healerdiscount&op=discountconfirm");
		addnav("No", "lodge.php");
	}elseif ($op=="discountconfirm"){
		addnav("L?Return to the Lodge","lodge.php");
		$pointsavailable = $session['user']['donation'] - $session['user']['donationspent'];
		if($pointsavailable >= $cost){
			$num = get_module_pref("daysleft");
			$num += $days;
			set_module_pref("daysleft", $num);
			$session['user']['donationspent'] += $cost;
			output("`7The old crusty character rummages around its folds of clothing and comes up with a wooden nickle.`n`n");
			output("\"`&Just keep this on you when you come visit me for healing.`7\", they remark.`n`n");
			output("`7Not really sure about the nickle, you place it in your pocket.  As you are doing so you notice it has an image of the healer on it.");
		}else{
			output("The old crusty character completely ignores you.  Maybe you don't have enough donations points availalbe.");
		}
	}
	page_footer();
}
?>	