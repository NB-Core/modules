<?php
function ninjamerchant_chances() {
	global $session;
	$hp=($session['user']['maxhitpoints']-$session['user']['level']*10)-3*$session['user']['dragonkills']; //leftover
	return (100-min(70,$hp)); //if return>100 then the module eventhandler takes care of it
}
?>