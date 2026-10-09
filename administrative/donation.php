<?php

function donation_getmoduleinfo(){
	$info = array(
		"name"=>"Donation Bar",
		"version"=>"1.82",
		"author"=>"Billie Kennedy, Danilo Stern-Sapad, Nicholas Moline, Excalibur",
		"category"=>"Administrative",
		"download"=>"http://www.orpgs.com/modules.php?name=Downloads&d_op=viewdownload&cid=2",
		"vertxtloc"=>"http://www.orpgs.com/downloads",
		"description"=>"Displays a Donation total with bar and Current total of donations just below the paypal links.",
		"settings"=>array(
			"General Settings,title",
			"donatetext"=>"Text to display explaining donations,text",
			"need"=>"Amount Needed,int|100",
			"show_current"=>"Show how much you already have for the month?,bool|0",
			"color_bar"=>"What color do you want for the bar?,text|blue",
			"bg_bar"=>"What color for the remainder of the goal?,text|white",
			"use_precent"=>"Use a percentage instead of dollar ammounts?,bool|1",
			"show_goal"=>"Show the Montly Goal?,bool|1",
			"Rewards,title",
			"globalheal"=>"Give a discount to all players for healing once goal is reached?,bool|1",
			"This discount applies to all players reguardless if they donated or not.,note",
			"maxhealdiscount"=>"What is the maximum Healing Discount in percent?,int|10",
			"Set this to 0 for no maximum discount,note",
			"globalturns"=>"Give extra forest fights once goal is reached?,bool|1",
			"Additional turns are given to all players reguardless if they donated or not.,note",
			"maxturns"=>"What are the maximum number of turns given?,int|2",
			"Set this to 0 for no maximum turns,note",
			"global_buff"=>"Give a buff to all players?,bool|1",
			"buffdef"=>"What is the defensive value of the buff to give players?,float|1.0",
			"buffturns"=>"How many turns should the buff apply?,int|50",
			"buffname"=>"What is the buff name?,text|Gift of the Gods",
			"buffcolor"=>"What is the buff name with color?,text|`\$Gift of the Gods",
			"buffwearoff"=>"Text for the wearoff message of the buff.,text|Your boon from the Gods wears off.",
			"buffmessage"=>"Text for the round message of the buff.,text|The Gods shield you from your foe.",
		),
	);
	return $info;
}

function donation_install(){
	module_addhook("paypal-below");
	module_addhook("healmultiply");
	module_addhook("newday");
	return true;
}

function donation_uninstall(){
	return true;
}

function donation_dohook($hookname,$args){
	global $session;
	switch($hookname){
	case "paypal-below":
		if (!isset($args['paypal_below'])) {

			$args['paypal_below'] = array();

		} elseif (!is_array($args['paypal_below'])) {

			$args['paypal_below'] = array($args['paypal_below']);
		}
		$needed = '';
		$text = '&nbsp;&nbsp;';
		$currency = '';
		if(get_module_setting('show_goal')){

			$needed = get_module_setting('need');
			$text = translate_inline("Donation Goal: ");
			$currency = translate_inline("$");
		}

		$roundpercent = get_percent();
		$nonpercent = 100 - $roundpercent;
		$have = get_have();

		$color = get_module_setting('color_bar');
		$bgcolor = get_module_setting('bg_bar');

		$display = "";

		$display .= "<div><table align='center'><tr><td align='center'><font size='-2' style='text-wrap: auto;'>".get_module_setting('donatetext')."</font><br /></td></tr></table><p><table align='center'><tr><td align='center'><b>".$text."<br />".$currency.$needed."</b></td></tr></table><table align='center' style='border: solid 1px #000000;' bgcolor='".$bgcolor."' cellpadding='0' cellspacing='0' width='70' height='5'>
			<tr><td width='$roundpercent' bgcolor='$color'></td><td width='$nonpercent'></td></tr></table></div>";
		array_push($args['paypal_below'], $display);

		if(get_module_setting('use_precent')){
			$text = translate_inline("Monthly Goal:");
			array_push($args['paypal_below'],"<div><table align='center'><tr><td align='center'><b>".$text."<br />".$roundpercent."%</b></td></tr></table></div>");
		}

		if(get_module_setting('show_current')){
			$text = translate_inline("Current Donations: ");
			$currency = translate_inline("$");
			array_push($args['paypal_below'],"<div><table align='center'><tr><td align='center'><b>".$text."<br />".$currency.$have."</b></td></tr></table></div>");
		}

		break;

	case "healmultiply":
		static $donation_did_i_run = 0;
		if (get_module_setting('globalheal')){

			$havepercent = get_percent()- 100;
			if (get_module_setting('maxhealdiscount') && $havepercent >= get_module_setting('maxhealdiscount')){
				$discount = get_module_setting('maxhealdiscount');
			}else{
				$discount = $havepercent;
			}

			if ($discount > 0) {
				if ($donation_did_i_run == 0) {
					output("`2Because of donations made, the gods grant you `^%s percent discount`2 on the healing costs.`n`n", $discount);
					$donation_did_i_run = 1;
				}
				$discount = (1 - ($discount/100));
				$args['alterpct'] *= $discount;
			}
		}
		break;

	case "newday":

		if (get_module_setting('globalturns')){

			$havepercent = get_percent();
			$bonus = floor(($havepercent-100)/10); // one for every 10%...
			$buff = get_module_setting('global_buff');

			if ($havepercent >=100 && $buff){
				$buffname = get_module_setting('buffname');
				$buffdef = get_module_setting('buffdef');
				$buffturns = get_module_setting('buffturns');
				$buffcolor = get_module_setting('buffcolor');
				$buffmessage = get_module_setting('buffmessage');
				$buffwearoff = get_module_setting('buffwearoff');


				apply_buff($buffname,
					array(
						"name"=> $buffcolor,
						"rounds"=> $buffturns,
						"wearoff"=> $buffwearoff,
						"defmod"=> $buffdef,
						"roundmsg"=> $buffmessage,
						"schema"=>"modules-donation",
					)
				);
				output("`\$Due to meeting or exceeding the donation goal, the Gods grant you a boon to protect you today.`n`n");
			}

			if (get_module_setting('maxturns') && $bonus >= get_module_setting('maxturns')){
				$bonus = get_module_setting('maxturns');
			}

			if ($bonus > 0) {
				$turnstoday = translate_inline("Donation Bonus: ");
				$turnstoday .=$bonus;
				output("`2Because of donations made, the gods grant you `^%s additional`2 forest fights.", $bonus);
				$args['turnstoday'] .= $turnstoday;
				$session['user']['turns'] += $bonus;
			}
		}
		break;

	}

	return $args;
}

function donation_run(){
}

function get_percent(){

	$sql = "SELECT substring(processdate,1,7) AS month, sum(amount)-sum(txfee) AS profit FROM ".db_prefix('paylog')." GROUP BY month ORDER BY month DESC LIMIT 1";
	$result = db_query_cached($sql,"donationgoal",60);

	$needed = get_module_setting("need");
	$month = null;
	$have = 0;
	while ($row = db_fetch_assoc($result)){
		$have = $row['profit'];
		$month = $row['month'];
	}

	if ($month == null) $have = 0;

	if ($month != date("Y-m")) $have = 0;

	if ($have == 0) {
		$percent = 0;
		$roundpercent = 0;
	} elseif ($needed <= 0) {
		// No goal set: any donation this month counts as reaching it.
		$roundpercent = 100;
	} else {
		$percent = $have / $needed * 100;
		$roundpercent = ceil($percent);
	}

	return $roundpercent;
}

function get_have(){
	$sql = "SELECT substring(processdate,1,7) AS month, sum(amount)-sum(txfee) AS profit FROM ".db_prefix('paylog')." GROUP BY month ORDER BY month DESC LIMIT 1";
	$result = db_query_cached($sql,"donationgoalcash");

	$month = null;
	$have = 0;
	while ($row = db_fetch_assoc($result)){
		$have = $row['profit'];
		$month = $row['month'];
	}
	if ($month == null) return false;

	if ($month != date("Y-m")) $have = 0;

	return $have;
}

?>
