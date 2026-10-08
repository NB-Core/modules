<?php

function banknin_getmoduleinfo(){
	$info = array(
			"name"=>"Bank Ninja",
			"version"=>"1.0",
			"author"=>"Oliver Brendel",
			"category"=>"Forest",
			"download"=>"",
			"settings"=>array(
				"uses"=>"How often can somebody use this per day?|99",
				"name"=>"Name of the nin,|Berta",
				"fee"=>"Fee in percent|5",
			),
			"prefs"=>array(
				"used"=>"How often used today?",
			),
		);
	return $info;
}

function banknin_install(){
	module_addhook("forest");
	module_addhook("newday");
	return true;
}

function banknin_uninstall(){
	return true;
}

function banknin_dohook($hookname,$args){
	global $session;
	//if ($session['user']['acctid']!=7) return $args;
	$bankop = httpget("bankop");
	$uses = get_module_setting("uses");
	$used = get_module_pref("used");
	$name = get_module_setting("name");
	$fee_percent = get_module_setting("fee");
	
	$deposit = max($session['user']['gold'],0);
	$fee = floor($deposit * $fee_percent / 100);
	
	$deposit-=$fee;
	
	
	switch($hookname){
		case "newday":
			set_module_pref("used",0);
		break;
		case "forest":
			addnav("Banking");
			switch ($bankop) {
				case "banknindeposit":
					output("`x%s`\$ takes your %s gold pieces and vanishes to the bank. The fee was %s gold pieces.`n`n",$name,$deposit,$fee);
					$session['user']['goldinbank']+=$deposit;
					$session['user']['gold']=0;
					debuglog(sprintf("Autodeposit of %s gold",$deposit));
					increment_module_pref("used",1);
					break;
			default:
				if($deposit<=0){
					addnav(array("Deposit %s gold (Not enough money)",$deposit),"");
				} elseif (($uses-$used) < 1) {
					addnav(array("Deposit %s gold (No service left",$deposit),"");
				} else {
					addnav(array("Deposit %s gold (Fee %s gold, %s uses left)",$deposit,$fee,$uses-$used),"forest.php?bankop=banknindeposit");
				}
			}
		break;
	}
	return $args;
}

function banknin_run(){
	global $session;
	$op = httpget("op");
	$uses = get_module_setting("uses");
	$used = get_module_pref("used");
	$name = get_module_pref("name");
	$fee_percent = get_module_setting("fee");
	
	$deposit = max($session['user']['gold'],0);
	$fee = floor($depost * $fee_percent / 100);
	
	page_header("Bank Nin");
	addnav("Navigation");
	if ($op=="deposit"){
		addnav("L?Return to the Forest","forest.php");
		output("`7You approach someone who slightly resembles an old, dirty rock.`n`n");
		output("\"`&If you wish, I can give you a discount on healing for %s days with only %s points.\"`7, they say.", $days,$cost);
		addnav("Confirm Healing Discount");
		addnav("Yes", "runmodule.php?module=banknin&op=discountconfirm");
		addnav("No", "lodge.php");
	}
	page_footer();
}
?>	
