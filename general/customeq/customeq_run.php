<?php
global $session;
$op = httpget("op");
$subop = httpget("subop");
$keep = get_module_setting("keep");
$weapon = get_module_pref("weaponname");
$armor = get_module_pref("armorname");
$wcost = get_module_setting("extraweapon");
if ($wcost < 1 || !$weapon || $keep)
	$wcost = get_module_setting("weaponcost");
$acost = get_module_setting("extraarmor");
if ($acost < 1 || !$armor || $keep)
	$acost = get_module_setting("armorcost");
$pointsavailable =
	$session['user']['donation'] - $session['user']['donationspent'];
page_header("Hunter's Lodge");
require("modules/customeq/customeq_func.php");
if ($op == "buy"){
	addnav("L?Return to the Lodge","lodge.php");
	output("`7J. C. Petersen smiles at you, \"`&So, you're interested in purchasing custom equipment.`7\"`n");
	if (($subop == "weapon" && $pointsavailable < $wcost) ||
			($subop == "armor" && $pointsavailable < $acost)){
		output("`nHe consults his book silently for a moment and then turns to you. \"`&I'm terribly sorry, but you only have %s points available.`7\"`n", $pointsavailable);
		if ($subop == "weapon"){
			output("`n\"`&A custom weapon costs %s points.`7\"`n`n",
					$wcost);
		}else{
			output("`n\"`&A custom armor costs %s points.`7\"`n`n",
					$acost);
		}
	}else{
		if ($subop == "weapon"){
			output("`n\"`&A custom weapon costs %s points.`7\"`n`n",
					$wcost);
		}else{
			output("`n\"`&A custom armor costs %s points.`7\"`n`n",
					$acost);
		}
		// check if we are allowed to use colors in the equipment 
		if (get_module_setting("color") == 1){
			output("`7\"`&You can use colors in your equipment.`7\"`0`n`n");
		} else {
			output("\"`&Unfortunately you may not use colors in the name.`7\"`0`n`n");	
		}
		customeq_form($subop);
	}
}elseif ($op == "preview"){
	addnav("L?Return to the Lodge","lodge.php");
	$newname = rawurldecode(httppost("newname"));
	$newname = stripslashes($newname);
	$newname = str_replace("`0", "", $newname);
	$newname = preg_replace("/[+-][0-9]+/", "", $newname);
	$newname = trim($newname);
	// check if we can use color in the name and then sanitize if we are not allowed to use color
	$newname = preg_replace("/[`][bicnHw]/", "", $newname);
	if (!get_module_setting("color")){
		$newname = sanitize($newname)."`0";
	}

	// trim variable newname to a multibyte maxlength of 50 chars
	$newname = mb_substr($newname, 0, 50);

	$eq = translate_inline($subop);
	if ($newname){
		output("`7You have chosen to name your %s %s.`n", $eq, $newname);
		output(" Is this the name you want?`0`n");
		addnav("C?Confirm","runmodule.php?module=customeq&op=confirm&subop=".$subop."&newname=".rawurlencode($newname));
	}else{
		output("`7You did not choose a valid name for your %s!`0`n", $eq);
	}
	addnav("a?Choose another name","runmodule.php?module=customeq&op=buy&subop=".$subop."");
}elseif ($op == "confirm"){
	addnav("L?Return to the Lodge","lodge.php");
	$newname = rawurldecode(httpget("newname"));
	$newname = stripslashes($newname);
	$eq = translate_inline($subop);
	output("`7Your %s has been changed.`0`n", $eq);
	if ($subop == "weapon"){
		customeq_wchange($newname);
		$session['user']['donationspent'] += $wcost;
		debuglog ("spent $wcost lodge points changing weapon to $newname.");
	}else{
		customeq_achange($newname);
		$session['user']['donationspent'] += $acost;
		debuglog ("spent $wcost lodge points changing armor to $newname.");
	}
	if (is_module_active("costumeshop")) {
		output("`n`\$Please note, that if you have rented a costume, you might not see your custom %s until you return the costume!`0`n", $eq);
	}
}
page_footer();
?>
