<?php
	require_once("modules/inventory/lib/itemhandler.php");
	$constant = constant("HOOK_" . strtoupper($hookname));
	$item = db_prefix("item");
	$inventory = db_prefix("inventory");
	$sql = "SELECT $item.* FROM $item WHERE ($item.activationhook & $constant) != 0";
	$result = db_query($sql);
	run_newday_buffs($result);
?>
