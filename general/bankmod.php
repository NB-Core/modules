<?php
// bank mod allowing gem deposits and instant deposits within the bank.
// majority of code taken straight from bank.php and modified

function bankmod_getmoduleinfo(){
	$info = array(
		"name"=>"Bank Modification",
		"version"=>"1.1",
		"author"=>"Spider",
		"category"=>"General",
		"download"=>"http://dragonprime.net/users/Spider/bankmod.zip",
		"settings"=>array(
			"Bank Modification Settings,title",
			"maxgems"=>"How many gems can be stored in the bank? (set to 0 to turn off banking gems),int|10",
		),
		"prefs"=>array(
			"Bank Modification User Preferences,title",
			"gemsinbank"=>"Gems stored in the bank,int|0",
		)
	);
	return $info;
}

function bankmod_install(){
	module_addhook("footer-bank");
	return true;
}

function bankmod_uninstall(){
	return true;
}

function bankmod_dohook($hookname,$args){
	global $session;
	switch($hookname){
		case "footer-bank":
			if (get_module_setting("maxgems")!=0){
				addnav("Gems");
				addnav("Withdraw Gems","runmodule.php?module=bankmod&op=withdraw");
				addnav("Deposit Gems","runmodule.php?module=bankmod&op=deposit");
			}
			if ($session['user']['gold']>0 || $session['user']['gems']>0) addnav("Instant Deposit");
			if ($session['user']['gold']>0) addnav("Deposit all Gold","runmodule.php?module=bankmod&op=instantgold");
			if (get_module_setting("maxgems")!=0 && ($session['user']['gems']+get_module_pref("gemsinbank"))<=get_module_setting("maxgems")){
				if ($session['user']['gems']>0) addnav("Deposit all Gems","runmodule.php?module=bankmod&op=instantgems");
				if ($session['user']['gold']>0 && $session['user']['gems']>0) addnav("Deposit everything","runmodule.php?module=bankmod&op=instant");
			}
			if ($session['user']['goldinbank']>0 || get_module_pref("gemsinbank")>0) addnav("Instant Withdraw");
			if ($session['user']['goldinbank']>0) addnav("Withdraw all Gold","runmodule.php?module=bankmod&op=withdrawallgold");
			if (get_module_setting("maxgems")!=0){
				if (get_module_pref("gemsinbank")>0) addnav("Withdraw all Gems","runmodule.php?module=bankmod&op=withdrawallgems");
				if ($session['user']['goldinbank']>0 && get_module_pref("gemsinbank")>0) addnav("Withdraw everything","runmodule.php?module=bankmod&op=withdrawall");
			}
			if (httpget('op')=="" && get_module_setting("maxgems")!=0){
				output("`n`n`6\"`3Oh, of course, you'll be wanting to know how many gems you have too.`6\", Elessa quickly checks her ledger again then looks up. \"`3You have `^%s gems`3 in the bank.`6\"",get_module_pref("gemsinbank"));
			}
			break;
	}
	return $args;
}

function bankmod_run(){
	global $session;
	require_once("lib/http.php");
	page_header("Ye Olde Bank");
	output("`^`c`bYe Olde Bank`b`c");
	$point=getsetting('moneydecimalpoint',".");
	$sep=getsetting('moneythousandssep',",");
	$op = httpget('op');
	if($op=="deposit"){
		output("`0");
		rawoutput("<form action='runmodule.php?module=bankmod&op=depositfinish' method='POST'>");
		output("`6Elessa says, \"`3You have a total of `^%s`3 gems in the bank.`6\"`n", get_module_pref("gemsinbank"));
		output("`6Searching through all your pockets and pouches, you find that you currently have `^%s`6 gems on hand.`n`n", number_format($session['user']['gems'],0,$point,$sep));
		output("`^Deposit how much?");
		$dep = translate_inline("Deposit");
		rawoutput(" <input id='input' name='amount' width=5 > <input type='submit' class='button' value='$dep'>");
		output("`n`iEnter 0 or nothing to deposit all of your gems`i");
		rawoutput("</form>");
		rawoutput("<script language='javascript'>document.getElementById('input').focus();</script>",true);
	  addnav("","runmodule.php?module=bankmod&op=depositfinish");
	}elseif($op=="depositfinish"){
		$amount = abs((int)httppost('amount'));
		if ($amount==0){
			$amount=$session['user']['gems'];
		}
		$notenough = translate_inline("`\$ERROR: Not enough gems in hand to deposit.`n`n`^You plunk your `&%s`^ gems on the counter and declare that you would like to deposit all `&%s`^ gems of them.`n`n`6Elessa stares blandly at you for a few seconds until you become self conscious and recount your gems, realizing your mistake.");
		$depositbalance= translate_inline("`6Elessa records your deposit of `^%s `6gems in her ledger. \"`3Thank you, `&%s`3.  You now have a total of `^%s`3 gems in the bank and `^%s`3 gems in hand.`6\"");
		$overlimit = translate_inline("`6Elessa checks her ledger and looks up at you. \"`3Sorry, but you cannot deposit that many gems, our bank will only store `^%s`3 gems for you.`6\"");
		if ($amount>$session['user']['gems']){
			output_notl($notenough,number_format($session['user']['gems'],0,$point,$sep),number_format($amount,0,$point,$sep));
		}else{
			$gemsinbank = (int)get_module_pref("gemsinbank");
			if (($gemsinbank + $amount)>get_module_setting("maxgems")){
				output($overlimit,get_module_setting("maxgems"));
			}else{
				debuglog("deposited " . $amount . " gems in the bank");
				$gemsinbank+=$amount;
				set_module_pref("gemsinbank",$gemsinbank);
				$session['user']['gems']-=$amount;
				output_notl($depositbalance,number_format($amount,0,$point,$sep),$session['user']['name'], number_format(abs($gemsinbank),0,$point,$sep),number_format($session['user']['gems'],0,$point,$sep));
			}
		}
	}elseif($op=="withdraw"){
		$withdraw = translate_inline("Withdraw");
		rawoutput("<form action='runmodule.php?module=bankmod&op=withdrawfinish' method='POST'>");
		output("`6Elessa scans through her ledger, \"`3You have a total of `^%s`3 gems in the bank.`6\"`n",number_format(get_module_pref("gemsinbank"),0,$point,$sep));
		output("`6\"`3How many gems would you like to withdraw `&%s`3?\"`n`n",$session['user']['name']);
		rawoutput("<input id='input' name='amount' width=5 > <input type='submit' class='button' value='$withdraw'>");
		output("`n`iEnter 0 or nothing to withdraw all of your gems`i");
		rawoutput("</form>");
		rawoutput("<script language='javascript'>document.getElementById('input').focus();</script>");
		addnav("","runmodule.php?module=bankmod&op=withdrawfinish");
	}elseif($op=="withdrawfinish"){
		$amount=abs((int)httppost('amount'));
		if ($amount==0){
			$amount=abs(get_module_pref("gemsinbank"));
		}
		if ($amount>get_module_pref("gemsinbank")) {
			output("`\$ERROR: Not enough gems in the bank to withdraw.`^`n`n");
			output("`6Having been informed that you have `^%s`6 gems in your account, you declare that you would like to withdraw all `^%s`6 of them.`n`n", number_format(get_module_pref("gemsinbank"),0,$point,$sep), number_format($amount,0,$point,$sep));
			output("Elessa looks at you for a few moments without blinking, then advises you to take basic arithmetic.  You realize your folly and think you should try again.");
		}else{
			$gemsinbank = get_module_pref("gemsinbank");
			$gemsinbank-=$amount;
			set_module_pref("gemsinbank",$gemsinbank);
			$session['user']['gems']+=$amount;
			debuglog("withdrew $amount gems from the bank");
			output("`6Elessa records your withdrawal of `^%s `6gems in her ledger. \"`3Thank you, `&%s`3.  You now have a total of `^%s`3 gems in the bank and `^%s`3 gems in hand.`6\"", number_format($amount,0,$point,$sep),$session['user']['name'], number_format(abs(get_module_pref("gemsinbank")),0,$point,$sep),number_format($session['user']['gems'],0,$point,$sep));
		}
	}elseif($op=="instantgold"){
		$amount=$session['user']['gold'];
		$session['user']['goldinbank']+=$amount;
		$session['user']['gold']=0;
		output("`6Elessa records your deposit of `^%s `6gold in her ledger. \"`3Thank you, `&%s`3.  You now have a total of `^%s`3 gold in the bank and `^0`3 gold in hand.`6\"",number_format($amount,0,$point,$sep),$session['user']['name'], number_format(abs($session['user']['goldinbank']),0,$point,$sep));
	}elseif($op=="instantgems"){
		$amount=$session['user']['gems'];
		$gemsinbank=get_module_pref("gemsinbank");
		$gemsinbank+=$amount;
		set_module_pref("gemsinbank",$gemsinbank);
		$session['user']['gems']=0;
		output("`6Elessa records your deposit of `^%s `6gems in her ledger. \"`3Thank you, `&%s`3.  You now have a total of `^%s`3 gems in the bank and `^0`3 gems in hand.`6\"",number_format($amount,0,$point,$sep),$session['user']['name'], number_format(abs(get_module_pref("gemsinbank")),0,$point,$sep));
		if ($gemsinbank>get_module_setting("maxgems")){
			output("`n`nElessa glances at her ledger again before closing it and seems to notice something, looking up she says \"`3You know that our bank can only store `^%s`3 gems for you safely, we are not responsible for any gems that are lost or damaged if you deposit too many.\"",get_module_setting("maxgems"));
		}
	}elseif($op=="instant"){
		$amount=$session['user']['gold'];
		$session['user']['goldinbank']+=$amount;
		$session['user']['gold']=0;
		$amount2=$session['user']['gems'];
		$gemsinbank=get_module_pref("gemsinbank");
		$gemsinbank+=$amount2;
		set_module_pref("gemsinbank",$gemsinbank);
		$session['user']['gems']=0;
		output("`6Elessa records your deposit of `^%s `6gold and `^%s `6gems in her ledger. \"`3Thank you, `&%s`3.  You now have a total of `^%s`3 gold and `^%s`3gems in the bank.`6\"",number_format($amount,0,$point,$sep),number_format($amount2,0,$point,$sep),$session['user']['name'], abs($session['user']['goldinbank']), abs(get_module_pref("gemsinbank"),0,$point,$sep));
		if ($gemsinbank>get_module_setting("maxgems")){
			output("`n`nElessa glances at her ledger again before closing it and seems to notice something, looking up she says \"`3You know that our bank can only store `^%s`3 gems for you safely, we are not responsible for any gems that are lost or damaged if you deposit too many.\"",number_format(get_module_setting("maxgems"),0,$point,$sep));
		}
	}elseif($op=="withdrawallgold"){
		$amount=$session['user']['goldinbank'];
		$session['user']['goldinbank']=0;
		$session['user']['gold']+=$amount;
		debuglog("withdrew $amount gold from the bank");
		output("`6Elessa records your withdrawal of `^%s `6gold in her ledger. \"`@Thank you, `&%s`@.  You now have a balance of `^%s`@ gold in the bank and `^%s`@ gold in hand.`6\"", number_format($amount,0,$point,$sep),$session['user']['name'], number_format(abs($session['user']['goldinbank']),0,$point,$sep),number_format($session['user']['gold'],0,$point,$sep));
	}elseif($op=="withdrawallgems"){
		$amount=get_module_pref("gemsinbank");
		set_module_pref("gemsinbank",0);
		$session['user']['gems']+=$amount;
		debuglog("withdrew $amount gems from the bank");
		output("`6Elessa records your withdrawal of `^%s `6gems in her ledger. \"`@Thank you, `&%s`@.  You now have a balance of `^%s`@ gems in the bank and `^%s`@ gems in hand.`6\"", number_format($amount,0,$point,$sep),$session['user']['name'], number_format(get_module_pref("gemsinbank"),0,$point,$sep),number_format($session['user']['gems'],0,$point,$sep));
	}elseif($op=="withdrawall"){
		$amount=get_module_pref("gemsinbank");
		set_module_pref("gemsinbank",0);
		$session['user']['gems']+=$amount;
		$amount2=$session['user']['goldinbank'];
		$session['user']['goldinbank']=0;
		$session['user']['gold']+=$amount2;
		debuglog("withdrew $amount gems and $amount2 gold from the bank");
		output("`6Elessa records your withdrawal of `^%s `6gems and `^%s `6gold in her ledger. \"`@Thank you, `&%s`@.  You now have nothing in the bank and `^%s`@ gems and `^%s`@gold in hand.`6\"", number_format($amount,0,$point,$sep),number_format($amount2,0,$point,$sep),$session['user']['name'],number_format($session['user']['gems'],0,$point,$sep),number_format($session['user']['gold'],0,$point,$sep));
	}

	require_once("lib/villagenav.php");
	villagenav();
	addnav("Money");
	if ($session['user']['goldinbank']>=0){
		addnav("W?Withdraw","bank.php?op=withdraw");
		addnav("D?Deposit","bank.php?op=deposit");
		if (getsetting("borrowperlevel",20)) addnav("L?Take out a Loan","bank.php?op=borrow");
	}else{
		addnav("D?Pay off Debt","bank.php?op=deposit");
		if (getsetting("borrowperlevel",20)) addnav("L?Borrow More","bank.php?op=borrow");
	}
	if (getsetting("allowgoldtransfer",1)){
		if ($session['user']['level']>=getsetting("mintransferlev",3) || $session['user']['dragonkills']>0){
		addnav("M?Transfer Money","bank.php?op=transfer");
		}
	}
	if (get_module_setting("maxgems")!=0){
		addnav("Gems");
		addnav("Withdraw Gems","runmodule.php?module=bankmod&op=withdraw");
		addnav("Deposit Gems","runmodule.php?module=bankmod&op=deposit");
	}
	if ($session['user']['gold']>0 || $session['user']['gems']>0) addnav("Instant Deposit");
	if ($session['user']['gold']>0) addnav("Deposit all Gold","runmodule.php?module=bankmod&op=instantgold");
	if (get_module_setting("maxgems")!=0 && ($session['user']['gems']+get_module_pref("gemsinbank"))<=get_module_setting("maxgems")){
		if ($session['user']['gems']>0) addnav("Deposit all Gems","runmodule.php?module=bankmod&op=instantgems");
		if ($session['user']['gold']>0 && $session['user']['gems']>0) addnav("Deposit everything","runmodule.php?module=bankmod&op=instant");
	}

	
	if ($session['user']['goldinbank']>0 || get_module_pref("gemsinbank")>0) addnav("Instant Withdraw");
	if ($session['user']['goldinbank']>0) addnav("Withdraw all Gold","runmodule.php?module=bankmod&op=withdrawallgold");
	if (get_module_setting("maxgems")!=0){
		if (get_module_pref("gemsinbank")>0) addnav("Withdraw all Gems","runmodule.php?module=bankmod&op=withdrawallgems");
		if ($session['user']['goldinbank']>0 && get_module_pref("gemsinbank")>0) addnav("Withdraw everything","runmodule.php?module=bankmod&op=withdrawall");
	}
	page_footer(); 
}

?>
