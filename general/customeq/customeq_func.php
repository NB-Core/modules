<?php
function customeq_wchange($newweapon){
	global $session;
	set_module_pref("weaponname",$newweapon);
	$oldweapon = $session['user']['weapon'];
	$upgraded = strpos($oldweapon," +1")!==false ? true : false;
	$downgraded = strpos($oldweapon," -1")!==false ? true : false;
	if (is_module_active("costumeshop") &&
			get_module_pref("weapon","costumeshop")){
		set_module_pref("oldweapon",$newweapon,"costumeshop");
		$newweapon = get_module_pref("weapon","costumeshop");
	}
	if ($upgraded){
		$session['user']['weapon'] = $newweapon." +1";
	}elseif ($downgraded){
		$session['user']['weapon'] = $newweapon." -1";
	}else{
		$session['user']['weapon'] = $newweapon;
	}
}

function customeq_achange($newarmor){
	global $session;
	set_module_pref("armorname",$newarmor);
	$oldarmor = $session['user']['armor'];
	$upgraded = strpos($oldarmor," +1")!==false ? true : false;
	$downgraded = strpos($oldarmor," -1")!==false ? true : false;
	if (is_module_active("costumeshop") &&
			get_module_pref("armor","costumeshop")){
		set_module_pref("oldarmor",$newarmor,"costumeshop");
		$newarmor = get_module_pref("armor","costumeshop");
	}
	if ($upgraded){
		$session['user']['armor'] = $newarmor." +1";
	}elseif ($downgraded){
		$session['user']['armor'] = $newarmor." -1";
	}else{
		$session['user']['armor'] = $newarmor;
	}
}

function customeq_form($subop){
	$eq = translate_inline($subop);
	output("What would you like to name your %s?`0`n", $eq);
	$prev = translate_inline("Preview");
	rawoutput("<form action='runmodule.php?module=customeq&op=preview&subop=".$subop."' method='POST'><input name='newname' value=\"\"> <input type='submit' class='button' value='$prev'></form>");
	addnav("","runmodule.php?module=customeq&op=preview&subop=".$subop);
}
?>
