<?php

function aloysius_getmoduleinfo(){
	$info = array(
		"name"=>"Aloysius' Market",
		"author"=>"Chris Vorndran",
		"version"=>"1.0",
		"category"=>"Village|Shops",
		"download"=>"http://dragonprime.net/users/Sichae/aloysius.zip",
		"vertxtloc"=>"http://dragonprime.net/users/Sichae/",
		"description"=>"Allows a user to trade in X amount of Forest Fights in order to have a chance at aquiring an extra PvP.",
		"settings"=>array(
			"Aloysius' Market Settings,title",
			"basechance"=>"Base chance that a user will get a PvP,range,1,50,1|10",
			"addchance"=>"Additional chance per FF extra,range,0,10,1|1",
			"Set to 0 to disable extra chance.,note",
			"min"=>"Minimum FFs needed to be given before additional chance is added,int|10",
			"aloc"=>"Where is Aloysius' Market located,location|".getsetting("villagename",LOCATION_FIELDS),
			"name"=>"Name of the male owner,text|Aloysius"
		),
		"prefs"=>array(
			"Aloysius' Market Prefs,title",
			"used"=>"Has Aloysius' Market been used today?,bool|0",
		),
	);
	return $info;
}
function aloysius_install(){
	module_addhook("newday");
	module_addhook("village");
	module_addhook("changesetting");
	return true;
}
function aloysius_uninstall(){
	return true;
}
function aloysius_dohook($hookname,$args){
	global $session;
	switch ($hookname){
		case "newday":
			set_module_pref("used",0);
			break;
		case "village":
			if ($session['user']['location'] == get_module_setting("aloc")){
				tlschema($args['schemas']['fightnav']);
				if (isset($args['fightnav'])) addnav($args['fightnav']);
				tlschema();
				addnav(array("%s`0's Market",get_module_setting("name")),"runmodule.php?module=aloysius&op=enter");
			}
			break;
		case "changesetting":
			if ($args['setting'] == "villagename") {
				if ($args['old'] == get_module_setting("aloc")) {
					set_module_setting("aloc", $args['new']);
				}
			}
			break;
		}
	return $args;
}
function aloysius_run(){
	global $session;
	$op = httpget('op');
	
	$basechance = get_module_setting("basechance");
	$addchance = get_module_setting("addchance");
	$amount = httppost('amount');
	$min = get_module_setting("min");
	
	$extraff = $amount-$min;
	if ($extraff <= 0) $extraff = 0;
	$totalchance = $basechance+($extraff*$addchance);
	debug("Total Chance: ".$totalchance);
	$alo=get_module_setting("name");
	page_header("%s's Market",sanitize($alo));
	switch ($op){
		case "enter":
			if (!get_module_pref("used")){
				output("`!Wandering into the market, you notice a young man standing off to the side.");
				output("He walks over to you, and extends his hand.");
				output("\"`QMy name is %s`Q, may I be of any service to you`!?\"",$alo);
				output("You notice upon his chest, many clanging medallions... most from a grand war.");
				addnav("Peruse Wares","runmodule.php?module=aloysius&op=peruse");
			}else{
				output("`!%s`! spots you and waves to you.",$alo);
				output("\"`QTry my wares again tomorrow, ye might have a better chance of getting what you wish!`!\"");
			}
			break;
		case "peruse":
			if ($amount == ""){
				output("`!\"`QSo, this is what I can do for you today...");
				output("If ye wish, I can take a minimum of `^%s `Qturns off of your hands...",$min);
				output("In exchange, you have a chance of being granted an extra PvP.");
				output("Do ye wish to take that chance? If so, please tell me how may FFs you are willing to chance.");
				output("For each extra FF you give me, your chance increase!`!\"");
				rawoutput("<form action='runmodule.php?module=aloysius&op=peruse' method='POST'>");
				rawoutput("<input name='amount' size='5'>");
				$gamble = translate_inline("Gamble");
				rawoutput("<input type='submit' class='button' value='$gamble'>");
				rawoutput("</form>");
			}else{
				$breakpoint = e_rand(0,100);
				debug("Breaking Point: ".$breakpoint);
				if ($breakpoint < $totalchance && $amount >= $min && $session['user']['turns'] >= $amount){
					output("`!%s`Q smiles and reaches into his coat pocket.",$alo);
					output("He hands you a small medallion and places it around your neck.");
					output("Your `\$%s `!begins to vibrate madly, and a searing hatred grows for your fellow warriors.",$session['user']['weapon']);
					output("`n`n`@You gain `^1 `@PvP, but lost `^%s `@Forest Fights!",$amount);
					$session['user']['playerfights']++;
					$session['user']['turns']-=$amount;
					debuglog("traded $amount forest fights for 1 PvP");
					set_module_pref("used",1);
				}elseif($session['user']['turns'] < $amount || $amount < $min){
					output("`!%s`! glares at you.",$alo);
					output("\"`QHow dare ye insult me like that... you have not the strength to bear any of my wares.");
					output("Now, get out!`!\"");
				}else{
					output("`!%s`! smiles and reaches into his coat pocket.",$alo);
					output("He takes out a small medallion and places it around your neck.");
					output("It coughs and sputters... and suddenly... you feel very sluggish.");
					output("`n`n`@You lose `^%s `@Forest Fights.",$amount);
					$session['user']['turns']-=$amount;
					debuglog("lost $amount forest fights at Aloysius' market");
					set_module_pref("used",1);
				}
			}
			addnav("","runmodule.php?module=aloysius&op=peruse");
			break;
		}
villagenav();
page_footer();
}
?>
