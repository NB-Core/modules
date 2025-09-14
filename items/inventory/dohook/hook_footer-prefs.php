<?php
	global $session, $REQUEST_URI;
	addnav("Inventory");
	if (isset($session['user']['acctid'])) 
		addnav("Show Inventory", "runmodule.php?module=inventory&user=".$session['user']['acctid']."&login=".$session['user']['login']."&return=".URLEncode($_SERVER['REQUEST_URI']));
?>
