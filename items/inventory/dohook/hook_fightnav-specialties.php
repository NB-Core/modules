<?php
	global $session;
	require_once("modules/inventory/lib/itemhandler.php");
	$constant = HOOK_FIGHTNAV;
	$item = db_prefix("item");
	$inventory = db_prefix("inventory");
	$sql = "SELECT a.*, b.leftcharges FROM $item as a 
		LEFT JOIN (
			SELECT itemid,min(charges+0) as leftcharges FROM $inventory WHERE userid=".$session['user']['acctid']." GROUP BY itemid) AS b
			ON a.itemid=b.itemid
			WHERE (a.activationhook & $constant) != 0";// ORDER BY b.charges+0 ASC";
	$result = db_query($sql);//_cached($sql, "item-activation-$hookname");
	$args = display_item_fightnav($result, $args);
?>
