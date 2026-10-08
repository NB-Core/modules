<?php
	$internal=unserialize(get_module_pref('internal'));
	$times = get_module_setting("times");
	$used = (isset($internal['used'])?$internal['used']:0);
	$skittle = get_module_setting("skitcost");
	$chocbar = get_module_setting("choccost");
	$icecream = get_module_setting("icecost");
	$rootbeer = get_module_setting("rootcost");
	$soda = get_module_setting("sodacost");
	$milkshake = get_module_setting("milkcost");
	$poural = get_module_setting("poural");
	$stopped = (isset($internal['stopped'])?$internal['stopped']:0);
	$theygold = $session['user']['gold'];
	$op = httpget("op");
	page_header("Mystie's Sweets Shoppe");
	switch ($op) {
		case "enter":
			output("A bell rings on the shop door as you step in to investigate this new establishment.");
			output(" Sweet aromas assault your senses and a warm angelic seeming voice greets you.");
			output(" You turn to see a woman, of godlike beauty and an ageless face.");
			output(" She smiles brightly, \"Hey hey...I'm Mystie...welcome to my sweets shop!");
			output(" Which sweet tooth craving do you wish to sate?\"`n`n");
			output("`3Menu:`n");
			output("`%S`\$k`^i`@t`#t`!l`%e`^s: `^%s gold`n",$skittle);
			output("`qChocolate Bars: `^%s gold`n",$chocbar);
			output("`@S`2o`#d`3a `&Pop: `^%s gold`n",$soda);
			output("`qRoot `QBeer `6Floats: `^%s gold`n",$rootbeer);
			output("`qM`&i`ql`&k`qs`&h`qa`&k`qe`&s: `^%s gold`n",$milkshake);
			output("`!I`#c`3e `&Cream `qS`Qu`&n`\$d`Qa`qe: `^%s gold`n",$icecream);
			if($used<$times){
				addnav("Menu of Sweets");
				if ($theygold>=$skittle) addnav("Skittles","runmodule.php?module=sweets&op=skittle");
				if ($theygold>=$chocbar) addnav("Chocolate Bars","runmodule.php?module=sweets&op=chocbar");
				if ($theygold>=$icecream) addnav("Ice Cream Sundaes","runmodule.php?module=sweets&op=icecream");
				if ($theygold>=$rootbeer) addnav("Root Beer Floats","runmodule.php?module=sweets&op=rootbeer");
				if ($theygold>=$soda) addnav("Soda Pop","runmodule.php?module=sweets&op=soda");
				if ($theygold>=$milkshake) addnav("Milkshake","runmodule.php?module=sweets&op=milkshake");
			} else {
				output("`n`n`2You are full...fuller than full... sweeter than sweet... you can't bear the thought of eating anything more...`n");
			}
			addnav("Other Fun Stuff");
			addnav("Talk Amongst Others","runmodule.php?module=sweets&op=talk");
			if ($internal['pour']<$poural) addnav("Cause Some Mischief","runmodule.php?module=sweets&op=pour");
			addnav("Leave");
			villagenav();
			break;
		case "skittle":
		case "chocbar":
		case "icecream":
		case "rootbeer":
		case "soda":
		case "milkshake":
			output("Your order is passed to you and Mystie motions to the tables telling you to enjoy your selection and come again.`n`n");
			require_once("modules/sweets/sweets_generator.php");
			$internal=sweets_generator($internal);
			$session['user']['gold']-=$$op;
			$internal['used']++;
			if ($internal['used']<$times) addnav("Return to the Shoppe","runmodule.php?module=sweets&op=enter");
			villagenav();
			break;
		case "talk":
			output("You enter a adjoining room off the sweet shop, and others seem to be sitting and enjoying their treats.`n`n");
			require_once("lib/commentary.php");
			addcommentary();
			viewcommentary("sweettalk","Talking Amongst Others",25,"says sweetly");
			addnav("Return to the Shoppe","runmodule.php?module=sweets&op=enter");
			villagenav();
			break;
		case "pour":
			$water="";
			output("`7You clamber into a small chamber and see several bottles of `qChocolate Syrup`7.");
			output(" A small grin peeks across your lips, as you hoist one of the bottles up.");
			if ($internal['pour']<$poural) addnav("Return to the Shoppe","runmodule.php?module=sweets&op=enter");
			villagenav();
			$sql = "SELECT acctid,name FROM ".db_prefix("accounts")." WHERE location='{$session['user']['location']}' AND acctid!={$session['user']['acctid']} AND loggedin=1 AND laston>'".date("Y-m-d H:i:s",strtotime("-".getsetting("LOGINTIMEOUT",300)." seconds"))."';";
			$result = db_query($sql);
			$here=array();
			while ($row = db_fetch_assoc($result)) {
				array_push($here,$row['name']);
			}
			output("`7You also see standing below:`n");
			output_notl(implode(",",$here));
			addnav("Pour Chocolate Syrup on people","runmodule.php?module=sweets&op=poured");
			break;
		case "poured":
			$internal['pour']++;
			if ($used<$times) addnav("Return to the Shoppe","runmodule.php?module=sweets&op=enter");
			villagenav();
			$sql = "SELECT acctid,name FROM ".db_prefix("accounts")." WHERE location='{$session['user']['location']}' AND acctid!={$session['user']['acctid']} AND loggedin=1 AND laston>'".date("Y-m-d H:i:s",strtotime("-".getsetting("LOGINTIMEOUT",300)." seconds"))."' ORDER BY RAND(".e_rand().")";
			output("You hit....");
			$name=$session['user']['name'];
			$result = db_query($sql);
			$water=0;
			require_once("lib/commentary.php");
			while (($row = db_fetch_assoc($result)) && $water==0) {
				$inter=unserialize(get_module_pref('internal','sweets',$row['acctid']));
				if (isset($inter['event']) && $inter['event']==1) continue;
				//target found
				$targetname=$row['name'];
				if (get_module_setting('displaynews')) addnews("%s `3dumped `qChocolate Syrup `3from Mystie's Sweets Shoppe on %s!",$name,$targetname);
				$comment=sprintf_translate("/me`2 `3dumped `qChocolate Syrup `3 on %s!",$targetname);
				injectrawcomment("sweettalk", $session['user']['acctid'], $comment);
				$inter['event']=1;
				$inter['eventculprit']=addslashes($name);
				set_module_pref('internal',serialize($inter),'sweets',$row['acctid']);
				$water=1;
			}
			if ($water==1) {
				output("%s`7!`6",$targetname);
			} else {
				output("No one! You missed!");
			}
			break;
	}
	set_module_pref('internal',serialize($internal));
?>
