<?php
	require_once("modules/inventory/lib/itemhandler.php");
	$inventory = get_inventory();
	$count=0;
	for ($i=0;$i<db_num_rows($inventory);$i++) {
		$item = db_fetch_assoc($inventory);
		if ($item['loosechance'] >= e_rand(1,100)) {
			remove_item((int)$item['itemid'], $item['quantity']);
			$count+=$item['quantity'];
		}
	}
	if ($count == 1) {
		output("`n`\$One of your items got damaged during the fight. ");
	} else if ($count > 1) {
		output("`n`\$Overall `^%s`\$ of your items have been damaged during the fight.", $count);
	}
?>
