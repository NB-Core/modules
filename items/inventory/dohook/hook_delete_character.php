<?php
	$inventory = db_prefix("inventory");
	$sql = "DELETE FROM $inventory WHERE userid = " . (int) $args['acctid'];
	db_query($sql);
	require_once("modules/inventory/lib/itemhandler.php");
	inventory_legacy_invalidate_user_read_caches((int) $args['acctid']);
?>
