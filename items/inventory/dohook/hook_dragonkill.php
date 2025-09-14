<?php
	require_once("modules/inventory/lib/itemhandler.php");
	$inventory = get_inventory();
	$count=0;
	while ($item = db_fetch_assoc($inventory)) {
		$destroyed = 0;
		for($c=0;$c<$item['quantity'];$c++) {
			if($item['dkloosechance'] >= e_rand(1,100)) $destroyed++;

		}
		if ($destroyed) {
			$destroyed_list[]=$item['name'];
			remove_item((int)$item['itemid'], $destroyed);
		}
		$count+=$destroyed;
	}
	if ($count == 1) {
		output("`n`2Shattered around you lie the remains of a once mighty item destroyed by the power of the dragon's flames. ");
		output("`n It seems that your %s`2 did not survive this fight.",$destroyed_list[0]);
	} else if ($count > 1) {
		output("`n`2Shattered around you lie the remains of once mighty items destroyed by the power of the dragon's flames. ");
		output("It seems `^%s items`2 have been destroyed:`n", $count);
		rawoutput("<ul class='itemlist'><li>");
		output_notl(implode("</li><li>",$destroyed_list),true);	
		rawoutput("</li></ul>");
	}
?>
